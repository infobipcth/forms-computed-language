<?php

namespace FormsComputedLanguage\Lifecycle;

use FormsComputedLanguage\Exceptions\FunctionRedeclarationException;
use FormsComputedLanguage\Exceptions\UnknownFunctionException;
use FormsComputedLanguage\Functions\FunctionInterface;
use FormsComputedLanguage\Visitors\FuncCallVisitor;

/**
 * Storage for callee-defined functions. Statically defined and global, provided by the calling program.
 * Can not be user-defined.
 */
class FunctionStore
{
	/**
	 * All defined functions. Must implement FunctionInterface.
	 *
	 * @var array<string, FunctionInterface> Key is the function name, value is a class implementing FunctionInterface.
	 */
	private static array $functions = [];

	public static function addFunction(string $functionName, FunctionInterface $function): void
	{
		// We need to check callee-defined functions as well as built-in functions.
		$existingFunctions = self::getFunctionList();

		if (in_array($functionName, $existingFunctions, true)) {
			throw new FunctionRedeclarationException("Function {$functionName} already declared, cannot redeclare.");
		}

		static::$functions[$functionName] = $function;
	}

	public static function getFunctionList(): array
	{
		return array_keys(static::$functions) + array_keys(FuncCallVisitor::FUNCTION_CALLBACKS);
	}

	public static function getFunctionsWithArgumentList(): array
	{
		$functions = static::$functions + FuncCallVisitor::FUNCTION_CALLBACKS;

		$listOut = [];

		foreach ($functions as $functionName => $functionCallbackInfo) {
			/*
			 * We have two cases to handle. Anonymous classes and regularly declared classes.
			 * For anonymous classes the $functionCallbackInfo is an object, otherwise it's
			 * an array containing the name of the class and run method name (callback).
			 */
			if (is_object($functionCallbackInfo)) {
				$classToFetch = $functionCallbackInfo;
			} elseif (is_array($functionCallbackInfo)) {
				$classToFetch = $functionCallbackInfo[0];
			} else {
				// Skip if something weird.
				continue;
			}

			$reflection = new \ReflectionClass($classToFetch);
			$functionArgsMethod = $reflection->getMethod('getArguments');
			$functionNameMethod = $reflection->getMethod('getName');
			$functionName = $functionNameMethod->invoke(null);
			$argumentList = $functionArgsMethod->invoke(null);

			$signatureParts = [];
			foreach ($argumentList as $arg => $type) {
				$signatureParts[] = "$type $arg";
			}

			$listOut[] = $functionName . '(' . implode(', ', $signatureParts) . ')';
		}

		return $listOut;
	}

	public static function runFunction(string $functionName, array $args)
	{
		if (isset(static::$functions[$functionName])) {
			return static::$functions[$functionName]->run($args);
		}

		throw new UnknownFunctionException("Undefined function {$functionName} called");
	}
}
