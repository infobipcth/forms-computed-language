<?php

namespace FormsComputedLanguage\Functions;

use FormsComputedLanguage\Exceptions\ArgumentCountException;
use FormsComputedLanguage\Exceptions\TypeException;

/**
 * Implements the abs function.
 * Call: abs(int|float $num): int|float
 */
class Abs implements FunctionInterface
{
	public const string FUNCTION_NAME = 'abs';

	public static function getName(): string
	{
		return self::FUNCTION_NAME;
	}

	public static function getArguments(): array
	{
		return [
			'$num' => 'int|float'
		];
	}

	/**
	 * Runs the abs function.
	 *
	 * @param array $args Array of arguments. See class docblock for signature.
	 * @return int|float The absolute value of number.
	 *
	 * @throws TypeException
	 * @throws ArgumentCountException
	 */
	public static function run(array $args): int|float
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
