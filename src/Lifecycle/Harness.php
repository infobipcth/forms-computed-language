<?php

namespace FormsComputedLanguage\Lifecycle;

use PhpParser\Parser;

class Harness
{
	private static ?Parser $parser;
	private static ?VariableStore $variableStore;
	private static ConstantsConfiguration $constantsConfiguration;

	public static function bootstrap(array $variables = [], string $variableStoreContext = 'global', ?Parser $_parser = null): void
	{
		if (!isset(static::$constantsConfiguration)) {
			static::$constantsConfiguration = new ConstantsConfiguration();
		}
		static::$variableStore = new VariableStore();
		static::$variableStore->setVariables($variables, $variableStoreContext);
		//static::$constantsConfiguration = new ConstantsConfiguration();
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

	public static function &getConstantsConfiguration(): ConstantsConfiguration
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
