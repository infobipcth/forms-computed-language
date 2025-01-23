<?php

namespace FormsComputedLanguage\Functions;

use FormsComputedLanguage\Exceptions\ArgumentCountException;

/**
 * Implements the countSelectedItems() function.
 * Call: countSelectedItems(array $array)
 */
class CountSelectedItems implements FunctionInterface
{
	public const string FUNCTION_NAME = 'countSelectedItems';

	public static function getName(): string
	{
		return self::FUNCTION_NAME;
	}

	public static function getArguments(): array
	{
		return [
			'$value' => 'array',
		];
	}

	/**
	 * Run the countSelectedItems() function
	 *
	 * @param array $args Array of arguments.
	 * @return int Number of array items in array.
	 *
	 * @throws ArgumentCountException
	 */
	public static function run(array $args): int
	{
		$argc = (int)(count($args));
		if ($argc <= 0 || $argc >= 2) {
			throw new ArgumentCountException("the countSelectedItems() function called with {$argc} arguments,
			but has one required argument and no optional arguments");
		}

		if (!is_array($args[0])) {
			return 0;
		}

		return count($args[0]);
	}
}
