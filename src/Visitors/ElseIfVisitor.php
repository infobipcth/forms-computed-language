<?php

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\Visitors\ExecutionChangeExceptions\DontTraverseChildren;
use PhpParser\Node;
use PhpParser\Node\Stmt\ElseIf_;

class ElseIfVisitor implements VisitorInterface
{
	public static function enterNode(Node &$node)
	{
		// see Evaluator comments for explanations into how relationships are set up.
		$parentIf = $node->getAttribute('parentIf');
		if ($node instanceof ElseIf_) {
			if ($parentIf->getAttribute('hasEvaluatedElifs')) {
				DontTraverseChildren::throw();
			}

			$node->cond->setAttribute('parentIf', $parentIf);
			$node->cond->setAttribute('parentElseif', $node);
			$node->cond->setAttribute('parentElseifRelationship', 'cond');

			foreach ($node->stmts as $stmt) {
				$stmt->setAttribute('parentIf', $parentIf);
				$stmt->setAttribute('parentElseif', $node);
				$stmt->setAttribute('parentElseifRelationship', 'stmt');
			}
		}
	}

	public static function leaveNode(Node &$node)
	{
		// intentionally left empty: no actions needed when leaving the node.
	}
}
