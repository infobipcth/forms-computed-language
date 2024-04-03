<?php

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\Visitors\ExecutionChangeExceptions\DontTraverseChildren;
use FormsComputedLanguage\Visitors\VisitorInterface;
use PhpParser\Node;
use PhpParser\NodeTraverser;

class ElseVisitor implements VisitorInterface
{
	public static function enterNode(Node &$node)
	{
		$parentIf = $node->getAttribute('parentIf');

		if ($parentIf->getAttribute('condTruthy') == true) {
			DontTraverseChildren::throw();
		}

		if ($parentIf->getAttribute('hasEvaluatedElifs') == true) {
			DontTraverseChildren::throw();
		}
	}

	public static function leaveNode(Node &$node)
	{
		// TODO: Implement leaveNode() method.
	}
}
