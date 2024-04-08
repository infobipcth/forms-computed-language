<?php

namespace FormsComputedLanguage\Functions;

use FormsComputedLanguage\Exceptions\ArgumentCountException;

/**
 * Run the isSelected() function, a wrapper over in_array.
 * Call: isSelected(array $array, string $option)
 */
class IsSelected
{
	public const FUNCTION_NAME = 'isSelected';

	/**
	 * Run the isSelected() function.
	 *
	 * @param array $args Array of arguments.
	 * @return bool Indicates whether the option is in the array of selected items.
	 */
	public static function run($args)
	{
		$argc = (int)(count($args));
		if ($argc !== 2) {
			throw new ArgumentCountException("the isSelected() function called with {$argc} arguments, 
			but has exactly two required arguments");
		}
		if (!is_array($args[0])) {
			return false;
		}
		return in_array($args[1], $args[0], true);
	}
}
