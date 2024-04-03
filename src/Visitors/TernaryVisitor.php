<?php

namespace FormsComputedLanguage\Visitors;

use PhpParser\Node;

class TernaryVisitor implements VisitorInterface
{
	public static function enterNode(Node &$node)
	{
		$node->cond->setAttribute('parentTernary', $node);
		$node->cond->setAttribute('parentTernaryRelationship', 'cond');
		$node->if->setAttribute('parentTernary', $node);
		$node->if->setAttribute('parentTernaryRelationship', 'if');
		$node->else->setAttribute('parentTernary', $node);
		$node->else->setAttribute('parentTernaryRelationship', 'else');
	}

	public static function leaveNode(Node &$node)
	{
		// intentionally left empty: no actions needed when leaving the node.
	}
}
