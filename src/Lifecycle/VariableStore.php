<?php

namespace FormsComputedLanguage\Lifecycle;

class VariableStore
{
	private static array $variables = [];

	/**
	 * Sets all variables in a context.
	 * @param array $variables Array of all variables. Keys are variable names, values are variable values.
	 * @param string $contextHandle A context handle. Defaults to global.
	 * @return void
	 */
	public static function setVariables(array $variables, string $contextHandle = 'global'): void
	{
		static::$variables[$contextHandle] = $variables;
	}

	/**
	 * Gets all variables in a context.
	 * @param string $contextHandle A context handle.
	 * @return array|null All variables for the given context.
	 */
	public static function getVariables(string $contextHandle = 'global'): ?array
	{
		return static::$variables[$contextHandle];
	}

	/**
	 * Sets a particular variable to a value.
	 * @param string $name Variable name.
	 * @param mixed $value Variable value.
	 * @param string $contextHandle A context handle.
	 * @return void
	 */
	public static function setVariable(string $name, $value, string $contextHandle = 'global')
	{
		static::$variables[$contextHandle][$name] = $value;
	}

	/**
	 * Sets an inner array value.
	 * @param string $arrayName Array variable name.
	 * @param mixed $arrayKey Array item key.
	 * @param mixed $value Array item value.
	 * @param string $contextHandle A context handle.
	 * @return void
	 */
	public static function setArrayVariable(string $arrayName, $arrayKey, $value, string $contextHandle = 'global')
	{
		static::$variables[$contextHandle][$arrayName][$arrayKey] = $value;
	}

	/**
	 * Appends an item to an array variable.
	 * @param string $arrayName Array variable name.
	 * @param mixed $value Item value.
	 * @param string $contextHandle A context handle.
	 * @return void
	 */
	public static function appendToArrayVariable(string $arrayName, $value, string $contextHandle = 'global')
	{
		static::$variables[$contextHandle][$arrayName][] = $value;
	}

	/**
	 * Gets an inner array item by key.
	 * @param string $arrayName Array variable name.
	 * @param mixed $arrayKey Array item key.
	 * @param string $contextHandle A context handle.
	 * @return mixed Value.
	 */
	public static function getArrayVariable(string $arrayName, $arrayKey, string $contextHandle = 'global')
	{
		return static::$variables[$contextHandle][$arrayName][$arrayKey];
	}

	/**
	 * Gets a variable.
	 * @param string $name Variable name.
	 * @param string $contextHandle A context handle.
	 * @return mixed Value.
	 */
	public static function getVariable(string $name, string $contextHandle = 'global')
	{
		return static::$variables[$contextHandle][$name];
	}


	/**
	 * Resets the whole variable store.
	 * @return void
	 */
	public static function reset()
	{
		static::$variables = [];
	}

	/**
	 * Resets the variable store for a particular context.
	 * @param string $contextHandle A context handle.
	 * @return array
	 */
	public static function resetContext(string $contextHandle = 'global')
	{
		return static::$variables[$contextHandle] = [];
	}

	/**
	 * Unsets a variable.
	 * @param string $name Variable name.
	 * @param string $contextHandle A context handle.
	 * @return void
	 */
	public static function unset(string $name, string $contextHandle = 'global')
	{
		unset(static::$variables[$contextHandle][$name]);
	}
}
