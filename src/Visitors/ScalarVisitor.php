<?php

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\Lifecycle\Stack;
use PhpParser\Node;
use PhpParser\Node\Scalar;

class ScalarVisitor implements VisitorInterface
{
	public static function enterNode(Node &$node)
	{
		if ($node instanceof Scalar) { // If this node is a scalar, push its value to the stack.
			Stack::push($node->value);
			// linters may recognize this as potentially undefined as it relies on class polymorphism.
			// however, all Scalar Nodes have a value member.
		}
	}

	public static function leaveNode(Node &$node)
	{
		// intentionally left empty: no actions needed when leaving the node.
	}
}
