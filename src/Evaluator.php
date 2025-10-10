<?php

namespace FormsComputedLanguage;

// Imports for...
// PHP errors
// FCL errors
use FormsComputedLanguage\Exceptions\UndeclaredVariableUsageException;
use FormsComputedLanguage\Exceptions\UnknownFunctionException;
use FormsComputedLanguage\Exceptions\UnknownTokenException;
// FCL functions
use FormsComputedLanguage\Lifecycle\Stack;
use FormsComputedLanguage\Lifecycle\VariableStore;
// FCL node visitors
use FormsComputedLanguage\Visitors\ArrayDimFetchVisitor;
use FormsComputedLanguage\Visitors\ArrayItemVisitor;
use FormsComputedLanguage\Visitors\ArrayVisitor;
use FormsComputedLanguage\Visitors\AssignOpVisitor;
use FormsComputedLanguage\Visitors\AssignVisitor;
use FormsComputedLanguage\Visitors\BinaryOpVisitor;
use FormsComputedLanguage\Visitors\BreakVisitor;
use FormsComputedLanguage\Visitors\CastVisitor;
use FormsComputedLanguage\Visitors\ConstFetchVisitor;
use FormsComputedLanguage\Visitors\ContinueVisitor;
use FormsComputedLanguage\Visitors\ElseIfVisitor;
use FormsComputedLanguage\Visitors\ElseVisitor;
use FormsComputedLanguage\Visitors\ExecutionChangeExceptions\ExecutionChangeException;
use FormsComputedLanguage\Visitors\ForeachVisitor;
use FormsComputedLanguage\Visitors\FuncCallVisitor;
use FormsComputedLanguage\Visitors\IfVisitor;
use FormsComputedLanguage\Visitors\ScalarVisitor;
use FormsComputedLanguage\Visitors\TernaryVisitor;
use FormsComputedLanguage\Visitors\VariableVisitor;
// Node types from php-parser
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\UnaryPlus;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

/**
 * A stack-based "virtual machine" to evaluate and execute programs in a safe manner.
 * Recieves a PHP abstract syntax tree from @nikic/php-parser and tries to evaluate a subset
 * of it.
 */
class Evaluator extends NodeVisitorAbstract
{
	/**
	 * Boot up the evaluator VM.
	 */
	public function __construct()
	{
	}

	/**
	 * Enter a node from the AST and do everything we need when entering that particular type of node.
	 * We can only do things that don't depend on node children evaluations when entering a block, so this
	 * is mostly used to set up if/elseif/else relationships and to push variables to the stack.
	 *
	 * @param Node $node A node to enter.
	 * @return void|int Returns void to continue, or a signal to the NodeTraverser to skip traversing a part of the AST.
	 * @noinspection PhpRedundantCatchClauseInspection
	 * @noinspection PhpRedundantCatchClauseInspection
	 * @noinspection PhpRedundantCatchClauseInspection
	 */
	public function enterNode(Node $node)
	{
		// @codeCoverageIgnoreStart
		// Debugging helpers are ignored for coverage.
		if (getenv("FCL_DEBUG") === "debug") {
			echo "Entering node\n";
			var_dump(get_class($node));
			echo "Variable store:\n";
			var_dump(VariableStore::getVariables());
			echo "Stack: \n";
			Stack::debug();
		}
		// @codeCoverageIgnoreEnd

		/**
		 * If this node is part of an if/elseif/else block, we need to be careful:
		 * 'if' condition should be evaluated always;
		 * 'elseif' conditions should be evaluated only if every previous condition in the block was false.
		 *
		 * We calculate the condition value, and then push the value up to the parent
		 * so that we can know whether to execute the inner statements of the if/elseif/else block.
		 * This check needs to happen when entering a node that's a condition or a statement
		 * as we traverse the AST recursively and don't return to the If block until all statements and conditions
		 * have been traversed.
		 *
		 * To support this, we need to set up node relationship references when entering the If block,
		 * so that we know whether a particular statement is part of an if condition,
		 * a statement that should be executed if the 'if' is true; and so on for 'elseifs' and 'else'.
		 * Similar tricks are used for the ternary operator.
		 *
		 * We set a 'parentIf', 'parentElseif', 'parentTernary' attribute on the applicable child nodes
		 * with a reference to the parent node, and a 'parentIfRelationship' etc. attribute
		 * describing the relationship between the child and the parent.
		 * On every if and elseif node that's not skipped
		 * (there haven't been any true conditions in the block previously) we set a 'condTruthy' attribute
		 * to indicate what does the condition evaluate to.
		 */

		$parentIf = $node->getAttribute('parentIf');
		$parentElseif = $node->getAttribute('parentElseif');
		$parentTernary = $node->getAttribute('parentTernary');

		// Check whether we should ignore this node / its children.
		if ($parentElseif) { // Is this node a direct descendant (statement or condition) of an elseif block?
			// If yes, then it's also a descentant of an If statement.

			if ($parentIf->getAttribute('condTruthy')) { // Is the condition of the If block true?
				$node->setAttribute('shouldEvaluate', false);
				return NodeVisitor::DONT_TRAVERSE_CHILDREN; // Don't evaluate this node nor its children.
			}

			// Have we already evaluated an elseif statement?
			// I.e., has there been a true elseif prior to this one in the block?
			if ($parentIf->getAttribute('hasEvaluatedElifs') === true) {
				$node->setAttribute('shouldEvaluate', false);
				return NodeVisitor::DONT_TRAVERSE_CHILDREN; // Don't evaluate this node nor its children.
			}

			// Otherwise, what's the relationship between this node and its parent elseif?
			$parentElseifRelationship = $node->getAttribute('parentElseifRelationship');
			if ($parentElseifRelationship === 'stmt' && !$parentElseif->getAttribute('condTruthy')) {
				// If it's a statement of the elseif and the condition is false,
				// don't evaluate this node nor its children.
				// In other words, skip the inner code in the elseif if the condition is false.
				$node->setAttribute('shouldEvaluate', false);
				return NodeVisitor::DONT_TRAVERSE_CHILDREN;
			}

			if ($parentElseif->getAttribute('condTruthy')) {
				// If the condition of this elseif is true, set context so other elseifs aren't evaluated.
				$parentIf->setAttribute('hasEvaluatedElifs', true);
			}
		}

		if ($parentTernary) { // Is this node part of a ternary?
			$parentRelationship = $node->getAttribute('parentTernaryRelationship');
			// What's the relationship to the ternary?
			if ($parentTernary->getAttribute('condTruthy')) { // Is the parent ternary true?
				if ($parentRelationship === 'else') { // If so, if this is the 'false' part of the ternary, skip it.
					$node->setAttribute('shouldEvaluate', false);
					return NodeVisitor::DONT_TRAVERSE_CHILDREN;
				}
			} else {
				if ($parentRelationship === 'if') {
					// If the parent ternary is false, and this is the 'true' part of the ternary, skip it.
					$node->setAttribute('shouldEvaluate', false);
					return NodeVisitor::DONT_TRAVERSE_CHILDREN;
				}
			}
		}

		if ($parentIf) { // Is this node part of an If?
			if (
				!$parentIf->getAttribute('condTruthy')
				&& $node->getAttribute('parentRelationship') === 'stmt'
			) { // If the if condition is false and this node is a statement of the if, skip it.
				return NodeVisitor::DONT_TRAVERSE_CHILDREN;
			}
		}

		// Start evaluating nodes.
		if ($node instanceof Scalar) {
			ScalarVisitor::enterNode($node);
		}

		if ($node instanceof Variable) {
			VariableVisitor::enterNode($node);
		}

		if ($node instanceof If_) {
			IfVisitor::enterNode($node);
		}

		if ($node instanceof ElseIf_) {
			try {
				ElseIfVisitor::enterNode($node);
			} catch (ExecutionChangeException $e) {
				return $e->getChange();
			}
		}

		if ($node instanceof Else_) {
			try {
				ElseVisitor::enterNode($node);
			} catch (ExecutionChangeException $e) {
				return $e->getChange();
			}
		}

		if ($node instanceof Ternary) {
			TernaryVisitor::enterNode($node);
		}

		if ($node instanceof Assign) {
			AssignVisitor::enterNode($node);
		}

		if ($node instanceof ArrayDimFetch) {
			ArrayDimFetchVisitor::enterNode($node);
		}

		if ($node instanceof Foreach_) {
			try {
				ForeachVisitor::enterNode($node);
			} catch (ExecutionChangeException $e) {
				return $e->getChange();
			}
		}

		if ($node instanceof Break_) {
			BreakVisitor::enterNode($node);
		}

		if ($node instanceof Continue_) {
			ContinueVisitor::enterNode($node);
		}

		/**
		 * Coalesce is a binary operator, but also changes execution flow!
		 * Coalesced variable lookups don't throw an error, meaningfully changing the execution flow if a variable is undefined.
		 * We need to mark the LHS of a coalesce operation so that when we look up a variable, we know not to throw an error if it's undefined.
		 * This is done in BinaryOpVisitor::enterNode for clarity, so that all Coalesce-related logic is in one place.
		 * Mostly - as this attribute is checked in VariableVisitor when looking up a variable.
		 */
		if ($node instanceof BinaryOp) {
			BinaryOpVisitor::enterNode($node);
		}
	}

	/**
	 * Leave a node from the AST and do everything we need when leaving that particular type of node.
	 * Most evaluation logic happens here, as the results of child block evaluations are now known and can be used
	 * from the stack.
	 *
	 * @param Node $node A node to leave.
	 * @return void Returns void.
	 * @throws UnknownTokenException If the token for the node is unknown to the evaluator.
	 * @throws UndeclaredVariableUsageException If an undefined constant is used.
	 * @throws UnknownFunctionException If an undefined function is called.
	 * @throws TypeException If a function is called with a wrong argument type.
	 * @throws ArgumentCountException If a function doesn't accept the given number of arguments.
	 */
	public function leaveNode(Node $node)
	{
		if ($node->getAttribute('shouldEvaluate', true) === false) {
			// If we shouldn't evaluate this node, skip it.
			return;
		}

		$nodeType = get_class($node);

		if ($node instanceof Assign) {
			AssignVisitor::leaveNode($node);
		} elseif ($node instanceof AssignOp) {
			AssignOpVisitor::leaveNode($node);
		} elseif ($node instanceof BinaryOp) {
			BinaryOpVisitor::leaveNode($node);
		} elseif ($node instanceof UnaryMinus) {
			$t = Stack::pop();
			Stack::push(-$t);
		} elseif ($node instanceof UnaryPlus) {
			$t = Stack::pop();
			Stack::push(+$t);
		} elseif ($node instanceof ConstFetch) {
			ConstFetchVisitor::leaveNode($node);
		} elseif ($node instanceof FuncCall) {
			FuncCallVisitor::leaveNode($node);
		} elseif ($node instanceof BooleanNot) {
			$temp = Stack::pop();
			Stack::push(!$temp);
		} elseif ($node instanceof ArrayItem) {
			ArrayItemVisitor::leaveNode($node);
		} elseif ($node instanceof Array_) {
			ArrayVisitor::leaveNode($node);
		} elseif ($node instanceof ArrayDimFetch) {
			ArrayDimFetchVisitor::leaveNode($node);
		} elseif ($node instanceof Break_) {
			BreakVisitor::leaveNode($node);
		} elseif ($node instanceof Continue_) {
			ContinueVisitor::leaveNode($node);
		} elseif ($node instanceof Cast) {
			CastVisitor::leaveNode($node);
		} elseif (
			$node instanceof Variable
			|| $node instanceof Scalar
			|| $node instanceof If_
			|| $node instanceof ElseIf_
			|| $node instanceof Else_
			|| $node instanceof Ternary
			|| $node instanceof Name
			|| $node instanceof Arg
			|| $node instanceof Expression
			|| $node instanceof Foreach_
		) {
			// Don't throw an UnknownTokenException for nodes we consider in enterNode or that are 'wrapper' nodes,
			// such as Expression, Arg, Name etc.
		} else {
			throw new UnknownTokenException("Unknown token {$nodeType} used");
		}

		// If this node is part of an if/elseif/else block, we need to be careful:
		// see comment in enterNode method.

		if ($parentElseif = $node->getAttribute('parentElseif')) { // Is this node a direct descentant of an elseif?
			if ($node->getAttribute('parentElseifRelationship') === 'cond') {
				// Is this node the condition of an elseif?
				// If yes, its evaluation is on the top of the stack. We can push it up to the parent elseif,
				// so statements inside it know whether to execute or not.
				// Note that we are not popping the stack, simply looking at its top.
				$parentElseif->setAttribute('condTruthy', Stack::pop());
			}
		}

		if ($parent = $node->getAttribute('parentIf')) { // Is this node a direct descentant of an If?
			if ($node->getAttribute('parentRelationship') === 'cond') { // Is this node the condition of an If?
				// Push the evaluation to the parent if.
				$parent->setAttribute('condTruthy', Stack::pop());
			}
		}

		if ($parentTernary = $node->getAttribute('parentTernary')) {
			// Is this node a direct descendant of a ternary operator?
			if ($node->getAttribute('parentTernaryRelationship') === 'cond') {
				// Is this node a condition of the ternary?
				// Push the evaluation to the parent ternary node.
				$parentTernary->setAttribute('condTruthy', Stack::pop());
			}
		}

		// @codeCoverageIgnoreStart
		// Debugging helpers are ignored for coverage.
		if (getenv("FCL_DEBUG") === "debug") {
			echo "Leaving node\n";
			var_dump(get_class($node));
			echo "Variable store:\n";
			var_dump(VariableStore::getVariables());
			echo "Stack: \n";
			Stack::debug();
		}
		// @codeCoverageIgnoreEnd
	}
}
