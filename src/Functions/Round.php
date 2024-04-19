<?php

namespace FormsComputedLanguage\Functions;

use FormsComputedLanguage\Exceptions\ArgumentCountException;
use FormsComputedLanguage\Exceptions\TypeException;

/**
 * Implements the round function.
 * Call: round(int|float $num, int $precision = 0, int $mode = PHP_ROUND_HALF_UP): float
 */
class Round
{
	/** Function name */
	public const FUNCTION_NAME = 'round';

	/**
	 * Runs the Round function.
	 *
	 * @param array $args Array of arguments. See class docblock for signature.
	 * @return float Rounded number.
	 * @noinspection PhpInconsistentReturnPointsInspection
	 */
	public static function run($args)
	{
		$argc = (int)(count($args));
		if ($argc <= 0 || $argc >= 4) {
			throw new ArgumentCountException(
				"the round() function called with {$argc} arguments, 
				but has one required argument and two optional arguments"
			);
		}
		if (!is_numeric($args[0])) {
			$type = gettype($args[0]);
			throw new TypeException("round() called with {$type} as first argument, requires a numeric argument");
		}
		if (isset($args[1]) && !is_int($args[1])) {
			$type = gettype($args[1]);
			throw new TypeException("round() called with {$type} as second argument, requires an int");
		}
		if (isset($args[2]) && !is_int($args[2])) {
			$type = gettype($args[2]);
			throw new TypeException("round() called with {$type} as third argument, requires an int");
		}
		switch ($argc) {
			case 1:
				return round($args[0]);
			case 2:
				return round($args[0], $args[1]);
			case 3:
				return round($args[0], $args[1], $args[2]);
		}
	}
}
