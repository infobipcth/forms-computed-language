<?php

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\Exceptions\UnknownTokenException;
use FormsComputedLanguage\Lifecycle\Stack;
use FormsComputedLanguage\Lifecycle\VariableStore;
use PhpParser\Node;
use PhpParser\Node\Expr\AssignOp\Concat as AssignOpConcat;
use PhpParser\Node\Expr\AssignOp\Div as AssignOpDiv;
use PhpParser\Node\Expr\AssignOp\Minus as AssignOpMinus;
use PhpParser\Node\Expr\AssignOp\Mul as AssignOpMul;
use PhpParser\Node\Expr\AssignOp\Plus as AssignOpPlus;

/**
 * Class handling all assignment operators.
 */
class AssignOpVisitor implements VisitorInterface
{
	public static function leaveNode(Node &$node)
	{
		$nodeType = get_class($node);

		if ($node instanceof AssignOpPlus) {
			VariableStore::setVariable($node->var->name, VariableStore::getVariable($node->var->name) + Stack::pop());
		} elseif ($node instanceof AssignOpMinus) {
			VariableStore::setVariable($node->var->name, VariableStore::getVariable($node->var->name) - Stack::pop());
		} elseif ($node instanceof AssignOpMul) {
			VariableStore::setVariable($node->var->name, VariableStore::getVariable($node->var->name) * Stack::pop());
		} elseif ($node instanceof AssignOpDiv) {
			VariableStore::setVariable($node->var->name, VariableStore::getVariable($node->var->name) / Stack::pop());
		} elseif ($node instanceof AssignOpConcat) {
			VariableStore::setVariable($node->var->name, VariableStore::getVariable($node->var->name) . Stack::pop());
		} else {
			throw new UnknownTokenException("Unknown assignment operator {$nodeType} used");
		}
	}

	public static function enterNode(Node &$node)
	{
		// intentionally left empty: no actions needed when leaving the node.
	}
}
