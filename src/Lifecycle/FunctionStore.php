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
class FunctionStore {
	/**
	 * All defined functions. Must implement FunctionInterface.
	 *
	 * @var array<string, FunctionInterface> Key is the function name, value is a class implementing FunctionInterface.
	 */
	private static array $functions = [];

	public static function addFunction(string $functionName, FunctionInterface $function) {
		// We need to check callee-defined functions as well as built-in functions.
		$existingFunctions = array_keys(static::$functions) + array_keys(FuncCallVisitor::FUNCTION_CALLBACKS);

		if (in_array($functionName, $existingFunctions, true)) {
			throw new FunctionRedeclarationException("Function {$functionName} already declared, can not redeclare.");
		}

		static::$functions[$functionName] = $function;
	}

	public static function runFunction(string $functionName, array $args) {
		if (isset(static::$functions[$functionName])) {
			return static::$functions[$functionName]->run($args);
		}

		if (isset(FuncCallVisitor::FUNCTION_CALLBACKS[$functionName])) {
			return call_user_func_array(FuncCallVisitor::FUNCTION_CALLBACKS[$functionName], [$args]);
		}

		throw new UnknownFunctionException("Undefined function {$functionName} called");
	}
}
