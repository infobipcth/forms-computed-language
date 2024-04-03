<?php

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\Exceptions\UndeclaredVariableUsageException;
use FormsComputedLanguage\Helpers;
use FormsComputedLanguage\Lifecycle\Harness;
use FormsComputedLanguage\Lifecycle\Stack;
use FormsComputedLanguage\Visitors\VisitorInterface;
use PhpParser\Node;

class ConstFetchVisitor implements VisitorInterface
{
	public static function enterNode(Node &$node)
	{
		// TODO: Implement enterNode() method.
	}

	public static function leaveNode(Node &$node)
	{
		$constfqn = Helpers::getFqnFromParts($node->name->parts);
		// todo: fix
		if (!Harness::getConstantsConfiguration()->canAccessConstant($constfqn)) { // Ask the language runner whether we can pass the constant.
			throw new UndeclaredVariableUsageException("Tried to get the value of disallowed constant {$constfqn}");
		}
		try {
			Stack::push(constant($constfqn));
		} catch (Error $e) {
			throw new UndeclaredVariableUsageException("Tried to get the value of undefined constant {$constfqn}");
		}
	}
}
