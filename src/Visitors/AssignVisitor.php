<?php

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\Lifecycle\Stack;
use FormsComputedLanguage\Lifecycle\VariableStore;
use PhpParser\Node;

class AssignVisitor implements VisitorInterface
{
	public static function enterNode(Node &$node)
	{
		// set up relationships.
		$node->var->setAttribute('parentIsAssignment', true);
		$node->var->setAttribute('parentAssign', $node);
	}

	public static function leaveNode(Node &$node)
	{
		if ($node->getAttribute('isArrayAssignment', false)) {
			// special case for array assignments ($array[] = 3)
			$dimensional = $node->getAttribute('isArrayAssignmentByDim', false);
			$value = Stack::pop();
			if ($dimensional) {
				// special case for dimensional array assignments ($array[2] = 3)
				$dim = Stack::pop();
			}

			$name = Stack::pop();

			if (!$dimensional) {
				VariableStore::appendToArrayVariable($name, $value);
			} else {
				VariableStore::setArrayVariable($name, $dim, $value);
			}
		} else {
			if (!($node->dim ?? false)) {
				VariableStore::setVariable($node->var->name, Stack::pop());
			} else {
				$arrayDim = Stack::pop();
				$arrayVal = Stack::pop();
				VariableStore::setArrayVariable($node->var->name, $arrayDim, $arrayVal);
			}
		}
	}
}
