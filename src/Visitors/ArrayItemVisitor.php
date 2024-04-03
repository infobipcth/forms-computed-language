<?php

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\Lifecycle\Stack;
use FormsComputedLanguage\StackObjects\ArrayItem as StackObjectsArrayItem;
use FormsComputedLanguage\Visitors\VisitorInterface;
use PhpParser\Node;

class ArrayItemVisitor implements VisitorInterface
{
	public static function enterNode(Node &$node)
	{
		// TODO: Implement enterNode() method.
	}

	public static function leaveNode(Node &$node)
	{
		$arrayItemValue = Stack::pop();
		if ($node?->key) {
			$arrayItemKey = Stack::pop();
		}
		$arrayItem = new StackObjectsArrayItem($arrayItemKey ?? null, $arrayItemValue);
		Stack::push($arrayItem);
	}
}
