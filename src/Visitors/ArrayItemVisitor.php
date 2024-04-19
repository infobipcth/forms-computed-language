<?php

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\Lifecycle\Stack;
use FormsComputedLanguage\StackObjects\ArrayItem as StackObjectsArrayItem;
use PhpParser\Node;

class ArrayItemVisitor implements VisitorInterface
{
	public static function enterNode(Node &$node)
	{
		// intentionally left empty: no actions needed when entering the node.
	}

	public static function leaveNode(Node &$node)
	{
		$arrayItemValue = Stack::pop();
		if ($node->key) {
			$arrayItemKey = Stack::pop();
		}
		$arrayItem = new StackObjectsArrayItem($arrayItemKey ?? null, $arrayItemValue);
		Stack::push($arrayItem);
	}
}
