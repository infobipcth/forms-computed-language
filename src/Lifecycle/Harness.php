<?php

namespace FormsComputedLanguage\Lifecycle;

use PhpParser\Parser;

/**
 * A runtime harness around execution.
 */
class Harness
{
	private static ?Parser $parser;
	private static ?VariableStore $variableStore;
	private static ConstantsConfiguration $constantsConfiguration;

	/**
	 * Bootstraps the execution harness.
	 * @param array $variables An array of variables to define. Keys are variable names.
	 * @param string $variableStoreContext A context handle for the variable store.
	 * @param Parser|null $_parser A Parser instance.
	 * @return void
	 */
	public static function bootstrap(
		array $variables = [],
		string $variableStoreContext = 'global',
		?Parser $_parser = null
	): void {
		if (!isset(static::$constantsConfiguration)) {
			static::$constantsConfiguration = new ConstantsConfiguration();
		}
		static::$variableStore = new VariableStore();
		static::$variableStore->setVariables($variables, $variableStoreContext);
		static::$parser = $_parser;
	}

	public static function setVariableStore($_variableStore): void
	{
		static::$variableStore = $_variableStore;
	}

	public static function getVariableStore(): VariableStore
	{
		return static::$variableStore;
	}

	public static function setConstantsConfiguration(ConstantsConfiguration $_constantsConfiguration): void
	{
		if (!isset(static::$constantsConfiguration)) {
			static::$constantsConfiguration = new ConstantsConfiguration();
		}
		static::$constantsConfiguration = $_constantsConfiguration;
	}

	public static function getConstantsConfiguration(): ConstantsConfiguration
	{
		if (!isset(static::$constantsConfiguration)) {
			static::$constantsConfiguration = new ConstantsConfiguration();
		}
		return static::$constantsConfiguration;
	}

	public static function getParser(): Parser
	{
		return static::$parser;
	}
}
