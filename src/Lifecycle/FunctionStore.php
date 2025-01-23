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
			 * If $functionCallbackInfo is anonymously declared class (on the fly)
			 * We'll need to do some magic in order to extract info from it.
			 */
			if (is_object($functionCallbackInfo)) {
				$reflection = new \ReflectionClass($functionCallbackInfo);
				$argumentList = $reflection->getConstant('ARGUMENTS');
				$functionName = $reflection->getConstant('FUNCTION_NAME');

				$signatureParts = [];
				foreach ($argumentList as $arg => $type) {
					$signatureParts[] = "$type $arg";
				}

				$listOut[] = $functionName . '(' . implode(', ', $signatureParts) . ')';
				continue;
			}

			$argumentList = constant("$functionCallbackInfo[0]::ARGUMENTS");

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

		if (isset(FuncCallVisitor::FUNCTION_CALLBACKS[$functionName])) {
			return call_user_func_array(FuncCallVisitor::FUNCTION_CALLBACKS[$functionName], [$args]);
		}

		throw new UnknownFunctionException("Undefined function {$functionName} called");
	}
}
