<?php

namespace FormsComputedLanguage\Functions;

use FormsComputedLanguage\Exceptions\ArgumentCountException;
use FormsComputedLanguage\Exceptions\TypeException;

/**
 * Implements the abs function.
 * Call: abs(int|float $num): int|float
 */
class Abs
{
	/** Function name */
	public const string FUNCTION_NAME = 'abs';

	public const array ARGUMENTS = ['$num' => 'int|float'];

	/**
	 * Runs the abs function.
	 *
	 * @param array $args Array of arguments. See class docblock for signature.
	 * @return float The absolute value of number.
	 * @noinspection PhpInconsistentReturnPointsInspection
	 */
	public static function run($args)
	{
		$argc = (int)(count($args));

		if ($argc !== 1) {
			throw new ArgumentCountException(
				"the abs() function called with {$argc} arguments,
				but expects only one argument"
			);
		}

		if (!is_numeric($args[0])) {
			$type = gettype($args[0]);
			throw new TypeException("abs() called with {$type} as first argument, requires a numeric argument");
		}

		return abs($args[0]);
	}
}
