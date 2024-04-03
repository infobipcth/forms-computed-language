<?php

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\Exceptions\UnknownFunctionException;
use FormsComputedLanguage\Functions\CountSelectedItems;
use FormsComputedLanguage\Functions\IsSelected;
use FormsComputedLanguage\Functions\Round;
use FormsComputedLanguage\Lifecycle\Stack;
use FormsComputedLanguage\Visitors\VisitorInterface;
use PhpParser\Node;

class FuncCallVisitor implements VisitorInterface
{
	/**
	 * Callbacks to run for available functions.
	 */
	private const FUNCTION_CALLBACKS = [
		'round' => [Round::class, 'run'],
		'countSelectedItems' => [CountSelectedItems::class, 'run'],
		'isSelected' => [IsSelected::class, 'run'],
	];
	public static function enterNode(Node &$node)
	{
		// TODO: Implement enterNode() method.
	}

	public static function leaveNode(Node &$node)
	{
		if (!empty((string) $node->name)) {
			$functionName = (string)($node->name);
		} else {
			$functionName = $node->name->getParts()[0];
		}
		$argv = [];
		foreach ($node->args as $arg) {
			$argv[] = Stack::pop();
		}

		$argv = array_reverse($argv);

		if (!isset(self::FUNCTION_CALLBACKS[$functionName])) {
			throw new UnknownFunctionException("Undefined function {$functionName} called");
		}

		Stack::push(call_user_func_array(self::FUNCTION_CALLBACKS[$functionName], [$argv]));
	}
}
