<?php

namespace FormsComputedLanguage\Functions;

use FormsComputedLanguage\Exceptions\ArgumentCountException;
use FormsComputedLanguage\Exceptions\TypeException;

/**
 * Implements the countSelectedItems() function.
 * Call: countSelectedItems(array $array)
 */
class CountSelectedItems
{
	public const FUNCTION_NAME = 'countSelectedItems';

	/**
	 * Run the countSelectedItems() function
	 *
	 * @param array $args Array of arguments.
	 * @return int Number of array items in array.
	 */
	public static function run($args)
	{
		$argc = (int)(count($args));
		if ($argc <= 0 || $argc >= 2) {
			throw new ArgumentCountException("the countSelectedItems() function called with {$argc} arguments, but has one required argument and no optional arguments");
		}
		if (!is_array($args[0])) {
			return 0;
		}

		return count($args[0]);
	}
}
