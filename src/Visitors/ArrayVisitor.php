<?php

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\Lifecycle\Stack;
use PhpParser\Node;

class ArrayVisitor implements VisitorInterface
{
	public static function enterNode(Node &$node)
	{
		// intentionally left empty: no actions needed when entering the node.
	}

	public static function leaveNode(Node &$node)
	{
		$arraySize = count($node->items);
		$array = [];
		for ($i = $arraySize - 1; $i >= 0; $i--) {
			$arrayItem = Stack::pop();
			if ($arrayItem?->key) {
				$array[$arrayItem->key] = $arrayItem->value;
			} else {
				$array[$i] = $arrayItem->value;
			}
		}
		Stack::push(array_reverse($array));
	}
}
