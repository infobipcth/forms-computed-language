<?php

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\Visitors\ExecutionChangeExceptions\DontTraverseChildren;
use PhpParser\Node;

class ElseVisitor implements VisitorInterface
{
	public static function enterNode(Node &$node)
	{
		// see Evaluator comments for explanations of relationship setup.
		$parentIf = $node->getAttribute('parentIf');

		if ($parentIf->getAttribute('condTruthy')) {
			DontTraverseChildren::throw();
		}

		if ($parentIf->getAttribute('hasEvaluatedElifs')) {
			DontTraverseChildren::throw();
		}
	}

	public static function leaveNode(Node &$node)
	{
		// intentionally left empty: no actions needed when leaving the node.
	}
}
