<?php

namespace FormsComputedLanguage\Functions;

use FormsComputedLanguage\Exceptions\ArgumentCountException;
use FormsComputedLanguage\Exceptions\TypeException;

/**
 * Implements the number_format function.
 * Call: number_format(int|float $num, int $decimals = 0, string $decimal = '.', string $thousands = ','): string
 */

class NumberFormat implements FunctionInterface {
	public const string FUNCTION_NAME = 'number_format';

	public static function getName(): string {
		return self::FUNCTION_NAME;
	}

	public static function getArguments(): array {
		return [
			'$num' => 'int|float',
			'$decimals = 0' => 'int',
			'$decimal = "."' => 'string',
			'$thousands = ","' => 'string',
		];
	}

	/**
	 * Runs the NumberFormat function.
	 *
	 * @param array $args Array of arguments. See class docblock for signature.
	 * @return string Formatted number.
	 *
	 * @throws ArgumentCountException
	 * @throws TypeException
	 */
	public static function run(array $args): string {
		$argc = (int)(count($args));
		if ($argc <= 0 || $argc >= 5) {
			throw new ArgumentCountException(
				"the number_format() function called with {$argc} arguments,
				but has one required argument and three optional arguments"
			);
		}

		if (!is_numeric($args[0])) {
			$type = gettype($args[0]);
			throw new TypeException("number_format() called with {$type} as first argument, requires a numeric argument");
		}

		if (isset($args[1]) && !is_int($args[1])) {
			$type = gettype($args[1]);
			throw new TypeException("number_format() called with {$type} as second argument, requires an int");
		}

		if (isset($args[2]) && !is_string($args[2])) {
			$type = gettype($args[2]);
			throw new TypeException("number_format() called with {$type} as third argument, requires a string");
		}

		if (isset($args[3]) && !is_string($args[3])) {
			$type = gettype($args[3]);
			throw new TypeException("number_format() called with {$type} as fourth argument, requires a string");
		}

		switch ($argc) {
			case 1:
				return number_format($args[0]);
			case 2:
				return number_format($args[0], $args[1]);
			case 3:
				return number_format($args[0], $args[1], $args[2]);
			case 4:
				return number_format($args[0], $args[1], $args[2], $args[3]);
		}
	}
}
