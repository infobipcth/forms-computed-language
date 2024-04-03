<?php

namespace FormsComputedLanguage\Visitors;

use Error;
use FormsComputedLanguage\Exceptions\UndeclaredVariableUsageException;
use FormsComputedLanguage\Helpers;
use FormsComputedLanguage\Lifecycle\Harness;
use FormsComputedLanguage\Lifecycle\Stack;
use PhpParser\Node;

class ConstFetchVisitor implements VisitorInterface
{
	public static function enterNode(Node &$node)
	{
		// intentionally left empty: no actions needed when entering the node.
	}

	public static function leaveNode(Node &$node)
	{
		$constfqn = Helpers::getFqnFromParts($node->name->parts);
		if (!Harness::getConstantsConfiguration()->canAccessConstant($constfqn)) {
			// Check the constants config to ensure constant access is allowed.
			throw new UndeclaredVariableUsageException("Tried to get the value of disallowed constant {$constfqn}");
		}
		try {
			Stack::push(constant($constfqn));
		} catch (Error) {
			throw new UndeclaredVariableUsageException("Tried to get the value of undefined constant {$constfqn}");
		}
	}
}
