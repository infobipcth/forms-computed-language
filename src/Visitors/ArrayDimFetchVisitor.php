<?php

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\Lifecycle\Stack;
use FormsComputedLanguage\Visitors\VisitorInterface;
use PhpParser\Node;

class ArrayDimFetchVisitor implements VisitorInterface
{
	public static function enterNode(Node &$node)
	{
		$node->var->setAttribute('parentIsAssignment', $node->getAttribute('parentIsAssignment', false));
	}

	public static function leaveNode(Node &$node)
	{
		if (!($node->getAttribute('parentIsAssignment', false))) {
			$arrayDim = Stack::pop();
			$array = Stack::pop();
			Stack::push($array[$arrayDim]);
		} else {
			$assignmentNode = $node->getAttribute('parentAssign');
			$assignmentNode->setAttribute('isArrayAssignment', true);
			if ($node->dim ?? false) {
				$assignmentNode->setAttribute('isArrayAssignmentByDim', true);
				$arrayDim = Stack::pop();
				Stack::push($arrayDim);
			}
			$arrayName = Stack::pop();
			Stack::push($arrayName);
		}
	}
}
