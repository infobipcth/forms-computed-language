<?php

namespace FormsComputedLanguage\Lifecycle;

use FormsComputedLanguage\Helpers;

class Stack
{
	/**
	 * @var array Represents the execution stack.
	 */
	private static array $stack = [];

	/**
	 * Pushes a value to the stack.
	 * @param $value
	 * @return void
	 */
	public static function push($value): void
	{
			static::$stack[] = $value;
	}

	/**
	 * Pops a value from the stack.
	 * @return mixed|null
	 */
	public static function pop()
	{
		return array_pop(static::$stack);
	}

	/**
	 * Returns the top of the stack (last added item) without popping the stack.
	 * @return mixed|null
	 */
	public static function peek()
	{
		return Helpers::arrayEnd(static::$stack);
	}

	/**
	 * Dumps the stack in var_dump format to stdout.
	 * @return void
	 */
	public static function debug()
	{
		var_dump(static::$stack);
	}
}
