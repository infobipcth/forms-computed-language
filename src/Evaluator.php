<?php

namespace FormsComputedLanguage;

// Imports for...
// PHP errors
use Error;
// FCL errors
use FormsComputedLanguage\Exceptions\UndeclaredVariableUsageException;
use FormsComputedLanguage\Exceptions\UnknownFunctionException;
use FormsComputedLanguage\Exceptions\UnknownTokenException;
// FCL functions
use FormsComputedLanguage\Functions\CountSelectedItems;
use FormsComputedLanguage\Functions\Round;
use FormsComputedLanguage\Functions\IsSelected;
use FormsComputedLanguage\StackObjects\ArrayItem as StackObjectsArrayItem;
// Node types from php-parser
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\AssignOp\Concat as AssignOpConcat;
use PhpParser\Node\Expr\AssignOp\Div as AssignOpDiv;
use PhpParser\Node\Expr\AssignOp\Minus as AssignOpMinus;
use PhpParser\Node\Expr\AssignOp\Mul as AssignOpMul;
use PhpParser\Node\Expr\AssignOp\Plus as AssignOpPlus;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\BinaryOp\Div;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\BinaryOp\Mul;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\Plus;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\UnaryPlus;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * A stack-based "virtual machine" to evaluate and execute programs in a safe manner.
 * Recieves a PHP abstract syntax tree from @nikic/php-parser and tries to evaluate a subset
 * of it.
 */
class Evaluator extends NodeVisitorAbstract
{
    /**
     * Contains all declared variables and their values in any given point during the execution lifecycle.
     * Array keys are variable names, and values are variable values.
     *
     * @var array
     */
    private array $vars = [];

    /**
     * The stack of the evaluator, used to memorize intermediate values during AST traversal.
     *
     * @var array
     */
    private array $stack = [];

    /**
     * The language runner that called the evaluator.
     */
    private LanguageRunner $languageRunner;

    /**
     * Callbacks to run for available functions.
     */
    private const FUNCTION_CALLBACKS = [
        'round' => [Round::class, 'run'],
        'countSelectedItems' => [CountSelectedItems::class, 'run'],
        'isSelected' => [IsSelected::class, 'run'],
    ];

    /**
     * Boot up the evaluator VM. Sets initial variables and initializes an empty stack.
     *
     * @param array $_vars Variables to initialize. Array keys are variable names, values are values.
     */
    public function __construct(array $_vars, LanguageRunner $_languageRunner)
    {
        $this->vars = $_vars; // Initialize the variables to passed variables.
        $this->stack = []; // Initialize the empty stack.
        $this->languageRunner = $_languageRunner; // Remember the language runner.
    }

    /**
     * Enter a node from the AST and do everything we need when entering that particular type of node.
     * We can only do things that don't depend on node children evaluations when entering a block, so this
     * is mostly used to set up if/elseif/else relationships and to push variables to the stack.
     *
     * @param Node $node A node to enter.
     * @return void|int Returns void to continue, or a signal to the NodeTraverser class to skip traversing a part of the AST.
     */
    public function enterNode(Node $node)
    {
        if (getenv("FCL_DEBUG") === "debug") {
            echo "Entering node\n";
            var_dump(get_class($node));
            echo "Variable store:\n";
            var_dump($this->vars);
            echo "Stack: \n";
            var_dump($this->stack);
        }

        // If this node is part of an if/elseif/else block, we need to be careful:
        // the 'if' condition should be evaluated always; 'elseif' conditions should be
        // evaluated only if every previous condition in the block was false.
        // We calculate the condition value, and then push the value up to the parent
        // so that we can know whether or not to execute the inner statements of the if/elseif/else block.
        // This check needs to happen when entering a node that's a condition or a statement as we traverse
        // the AST recursively and don't return to the If block until all statements and conditions have been traversed.
        // To support this, we need to set up node relationship references when entering the If block,
        // so that we know whether a particular statement is part of an if condition, a statement that should be
        // executed if the if is true; and so on for elseifs and else. Similar tricks are used for the ternary operator.
        // We set a 'parentIf', 'parentElseif', 'parentTernary' attribute on the applicable child nodes with a reference
        // to the parent node, and a 'parentIfRelationship' etc. attribute describing the relationship between the
        // child and the parent.
        // On every if and elseif node that's not skipped (there haven't been any true conditions in the block previously)
        // we set a 'condTruthy' attribute to indicate what does the condition evaluate to.

        $parentIf = $node->getAttribute('parentIf');
        $parentElseif = $node->getAttribute('parentElseif');
        $parentTernary = $node->getAttribute('parentTernary');

        // Check whether we should ignore this node / its children.
        if ($parentElseif) { // Is this node a direct descendant (statement or condition) of an elseif block?
            // If yes, then it's also a descentant of an If statement.

            if ($parentIf->getAttribute('condTruthy')) { // Is the condition of the If block true?
                return NodeTraverser::DONT_TRAVERSE_CHILDREN; // Don't evaluate this node nor its children.
            }

            // Have we already evaluated an elseif statement? I.e., has there been a true elseif prior to this one in the block?
            if ($parentIf->getAttribute('hasEvaluatedElifs') === true) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN; // Don't evaluate this node nor its children.
            }

            // Otherwise, what's the relationship between this node and its parent elseif?
            $parentElseifRelationship = $node->getAttribute('parentElseifRelationship');
            if ($parentElseifRelationship === 'stmt' && $parentElseif->getAttribute('condTruthy') == false) {
                // If it's a statement of the elseif and the condition is false, don't evaluate this node nor its children.
                // In other words, skip the inner code in the elseif if the condition is false.
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }

            if ($parentElseif->getAttribute('condTruthy')) {
                // If the condition of this elseif is true, set context so other elseifs aren't evaluated.
                $parentIf->setAttribute('hasEvaluatedElifs', true);
            }
        }

        if ($parentTernary) { // Is this node part of a ternary?
            $parentRelationship = $node->getAttribute('parentTernaryRelationship'); // What's the relationship to the ternary?
            if ($parentTernary->getAttribute('condTruthy')) { // Is the parent ternary true?
                if ($parentRelationship === 'else') { // If so, if this is the 'false' part of the ternary, skip it.
                    return NodeTraverser::DONT_TRAVERSE_CHILDREN;
                }
            } else {
                if ($parentRelationship === 'if') { // If the parent ternary is false, and this is the 'true' part of the ternary, skip it.
                    return NodeTraverser::DONT_TRAVERSE_CHILDREN;
                }
            }
        }

        if ($parentIf) { // Is this node part of an If?
            if (
                $parentIf->getAttribute('condTruthy') == false
                && $node->getAttribute('parentRelationship') === 'stmt'
            ) { // If the if condition is false and this node is a statement of the if, skip it.
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
        }



        // Start evaluating nodes.

        if ($node instanceof Scalar) { // If this node is a scalar, push its value to the stack.
            $this->stack[] = $node->value ?? null;
        }

        if ($node instanceof Variable) { // If this node references a variable e.g. $x, push the variable value to the stack.
            $this->stack[] = $this->vars[$node->name] ?? null;
        }

        // Set up relationships and references for children of If, Elseif, Else blocks and ternary operators.
        if ($node instanceof If_) {
            if ($node->cond) {
                $node->cond->setAttribute('parentIf', $node);
                $node->cond->setAttribute('parentRelationship', 'cond');
            }

            foreach ($node->stmts ?? [] as $statement) {
                $statement->setAttribute('parentIf', $node);
                $statement->setAttribute('parentRelationship', 'stmt');
            }

            foreach ($node->elseifs ?? [] as $elseif) {
                $elseif->setAttribute('parentIf', $node);
                $elseif->setAttribute('parentRelationship', 'elif');
            }

            if ($node->else) {
                $node->else->setAttribute('parentIf', $node);
                $node->else->setAttribute('parentRelationship', 'else');
            }
        }

        if ($node instanceof ElseIf_) {
            if ($parentIf->getAttribute('hasEvaluatedElifs')) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }

            $node->cond->setAttribute('parentIf', $parentIf);
            $node->cond->setAttribute('parentElseif', $node);
            $node->cond->setAttribute('parentElseifRelationship', 'cond');

            foreach ($node->stmts as $stmt) {
                $stmt->setAttribute('parentIf', $parentIf);
                $stmt->setAttribute('parentElseif', $node);
                $stmt->setAttribute('parentElseifRelationship', 'stmt');
            }
        }

        if ($node instanceof Else_) {
            if ($parentIf->getAttribute('condTruthy') == true) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }

            if ($parentIf->getAttribute('hasEvaluatedElifs') == true) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
        }

        if ($node instanceof Ternary) {
            $node->cond->setAttribute('parentTernary', $node);
            $node->cond->setAttribute('parentTernaryRelationship', 'cond');
            $node->if->setAttribute('parentTernary', $node);
            $node->if->setAttribute('parentTernaryRelationship', 'if');
            $node->else->setAttribute('parentTernary', $node);
            $node->else->setAttribute('parentTernaryRelationship', 'else');
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
        $nodeType = get_class($node);

        if ($node instanceof Assign) {
            $this->vars[$node->var->name] = array_pop($this->stack);
        } elseif ($node instanceof AssignOp) {
            if ($node instanceof AssignOpPlus) {
                $this->vars[$node->var->name] += array_pop($this->stack);
            } elseif ($node instanceof AssignOpMinus) {
                $this->vars[$node->var->name] -= array_pop($this->stack);
            } elseif ($node instanceof AssignOpMul) {
                $this->vars[$node->var->name] *= array_pop($this->stack);
            } elseif ($node instanceof AssignOpDiv) {
                $this->vars[$node->var->name] /= array_pop($this->stack);
            } elseif ($node instanceof AssignOpConcat) {
                $this->vars[$node->var->name] .= array_pop($this->stack);
            } else {
                throw new UnknownTokenException("Unknown assignment operator {$nodeType} used");
            }
        } elseif ($node instanceof BinaryOp) {
            $rhs = array_pop($this->stack);
            $lhs = array_pop($this->stack);

            if ($node instanceof Concat) {
                $this->stack[] = $lhs . $rhs;
            } elseif ($node instanceof Plus) {
                $this->stack[] = $lhs + $rhs;
            } elseif ($node instanceof Minus) {
                $this->stack[] = $lhs - $rhs;
            } elseif ($node instanceof Mul) {
                $this->stack[] = $lhs * $rhs;
            } elseif ($node instanceof Div) {
                $this->stack[] = $lhs / $rhs;
            } elseif ($node instanceof Equal) {
                $this->stack[] = $lhs == $rhs;
            } elseif ($node instanceof NotEqual) {
                $this->stack[] = $lhs != $rhs;
            } elseif ($node instanceof Smaller) {
                $this->stack[] = $lhs < $rhs;
            } elseif ($node instanceof SmallerOrEqual) {
                $this->stack[] = $lhs <= $rhs;
            } elseif ($node instanceof Greater) {
                $this->stack[] = $lhs > $rhs;
            } elseif ($node instanceof GreaterOrEqual) {
                $this->stack[] = $lhs >= $rhs;
            } elseif ($node instanceof BooleanAnd) {
                $this->stack[] = ($lhs && $rhs);
            } elseif ($node instanceof BooleanOr) {
                $this->stack[] = ($lhs || $rhs);
            } else {
                throw new UnknownTokenException("Unknown boolean operator {$nodeType} used");
            }
        } elseif ($node instanceof UnaryMinus) {
            $t = array_pop($this->stack);
            $this->stack[] = -$t;
        } elseif ($node instanceof UnaryPlus) {
            $t = array_pop($this->stack);
            $this->stack[] = +$t;
        } elseif ($node instanceof ConstFetch) {
            $constfqn = Helpers::getFqnFromParts($node->name->parts);
            if (!$this->languageRunner->canAccessConstant($constfqn)) { // Ask the language runner whether we can pass the constant.
                throw new UndeclaredVariableUsageException("Tried to get the value of disallowed constant {$constfqn}");
            }
            try {
                $this->stack[] = constant($constfqn);
            } catch (Error $e) {
                throw new UndeclaredVariableUsageException("Tried to get the value of undefined constant {$constfqn}");
            }
        } elseif ($node instanceof FuncCall) {
            if (!empty((string) $node->name)) {
                $functionName = (string)($node->name);
            }
            else {
                $functionName = $node->name->getParts()[0];
            }
            $argv = [];
            foreach ($node->args as $arg) {
                $argv[] = array_pop($this->stack);
            }

            $argv = array_reverse($argv);

            if (!isset(self::FUNCTION_CALLBACKS[$functionName])) {
                throw new UnknownFunctionException("Undefined function {$functionName} called");
            }

            $this->stack[] = call_user_func_array(self::FUNCTION_CALLBACKS[$functionName], [$argv]);
        } elseif ($node instanceof BooleanNot) {
            $temp = array_pop($this->stack);
            $this->stack[] = !$temp;
        } elseif ($node instanceof ArrayItem) {
            $arrayItemValue = array_pop($this->stack);
            if ($node->key ?? false) {
                $arrayItemKey = array_pop($this->stack);
            }
            $arrayItem = new StackObjectsArrayItem($arrayItemKey, $arrayItemValue);
            $this->stack[] = $arrayItem;
        } elseif ($node instanceof Array_) {
            $arraySize = count($node->items);
            $array = [];
            for ($i = $arraySize - 1; $i >= 0; $i--) {
                $arrayItem = array_pop($this->stack);
                if ($arrayItem->key ?? false) {
                    var_dump($arrayItem);
                    $array[$arrayItem->key] = $arrayItem->value;
                }
                else {
                    $array[$i] = $arrayItem->value;
                }
            }
            $this->stack[] = ($array);
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
        ) {
            // Don't throw an UnknownTokenException for nodes we consider in enterNode or that are 'wrapper' nodes,
            // such as Expression, Arg, Name etc.
        } else {
            throw new UnknownTokenException("Unknown token {$nodeType} used");
        }

        // If this node is part of an if/elseif/else block, we need to be careful:
        // see comment in enterNode method.

        if ($parentElseif = $node->getAttribute('parentElseif')) { // Is this node a direct descentant of an elseif?
            if ($node->getAttribute('parentElseifRelationship') === 'cond') { // Is this node the condition of an elseif?
                // If yes, its evaluation is on the top of the stack. We can push it up to the parent elseif,
                // so statements inside it know whether to execute or not.
                // Note that we are not popping the stack, simply looking at its top.
                $parentElseif->setAttribute('condTruthy', Helpers::arrayEnd($this->stack));
            }
        }

        if ($parent = $node->getAttribute('parentIf')) { // Is this node a direct descentant of an If?
            if ($node->getAttribute('parentRelationship') === 'cond') { // Is this node the condition of an If?
                // Push the evaluation to the parent if.
                $parent->setAttribute('condTruthy', Helpers::arrayEnd($this->stack));
            }
        }

        if ($parentTernary = $node->getAttribute('parentTernary')) { // Is this node a direct descendant of a ternary operator?
            if ($node->getAttribute('parentTernaryRelationship') === 'cond') { // Is this node a condition of the ternary?
                // Push the evaluation to the parent ternary node.
                $parentTernary->setAttribute('condTruthy', Helpers::arrayEnd($this->stack));
            }
        }

        if (getenv("FCL_DEBUG") === "debug") {
            echo "Leaving node\n";
            var_dump(get_class($node));
            echo "Variable store:\n";
            var_dump($this->vars);
            echo "Stack: \n";
            var_dump($this->stack);
        }
    }

    public function afterTraverse(array $nodes)
    {
        $this->languageRunner->setVars($this->vars);
    }
}
