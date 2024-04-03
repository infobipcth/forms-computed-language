<?php

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\Visitors\VisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt\If_;

class IfVisitor implements VisitorInterface
{
	public static function enterNode(Node &$node)
	{
		// Set up relationships and references for children of If, Elseif, Else blocks and ternary operators.
		if ($node instanceof If_) {
			if ($node->cond) {
				$node->cond->setAttribute('parentIf', $node);
				$node->cond->setAttribute('parentRelationship', 'cond');
			}

			foreach ($node->stmts ?? [] as $statement) {
				$statement->setAttribute('parentIf', $node);
				$statement->setAttribute('parentRelationship', 'stmt');
			}

			foreach ($node->elseifs ?? [] as $elseif) {
				$elseif->setAttribute('parentIf', $node);
				$elseif->setAttribute('parentRelationship', 'elif');
			}

			if ($node->else) {
				$node->else->setAttribute('parentIf', $node);
				$node->else->setAttribute('parentRelationship', 'else');
			}
		}
	}

	public static function leaveNode(Node &$node)
	{
		// TODO: Implement leaveNode() method.
	}
}
