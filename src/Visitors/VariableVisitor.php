<?php

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\Lifecycle\Stack;
use FormsComputedLanguage\Lifecycle\VariableStore;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;

class VariableVisitor implements VisitorInterface
{
	public static function enterNode(Node &$node)
	{
		if ($node instanceof Variable) {
			// If this node references a variable e.g. $x, push the variable value to the stack.
			if (!($node->getAttribute('parentIsAssignment', false))) {
				Stack::push(VariableStore::getVariable($node->name));
			} else {
				Stack::push($node->name);
			}
		}
	}

	public static function leaveNode(Node &$node)
	{
		// intentionally left empty: no actions needed when leaving the node.
	}
}
