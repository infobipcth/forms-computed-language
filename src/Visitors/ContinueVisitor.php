<?php

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\Visitors\ExecutionChangeExceptions\ContinueLoopException;
use PhpParser\Node;

class ContinueVisitor implements VisitorInterface
{
	public static function enterNode(Node &$node)
	{
		ContinueLoopException::throw();
	}

	public static function leaveNode(Node &$node)
	{
		// intentionally left empty: no actions needed when leaving the node.
	}
}
