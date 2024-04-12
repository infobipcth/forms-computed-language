<?php

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\Visitors\ExecutionChangeExceptions\BreakOutOfLoopException;
use PhpParser\Node;

class BreakVisitor implements VisitorInterface
{
	public static function enterNode(Node &$node)
	{
		BreakOutOfLoopException::throw();
	}

	public static function leaveNode(Node &$node)
	{
		// intentionally left empty: no actions needed when leaving the node.
	}
}
