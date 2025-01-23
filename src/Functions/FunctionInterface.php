<?php

namespace FormsComputedLanguage\Functions;

/** Generic function interface */
interface FunctionInterface
{
	public static function getName(): string;
	public static function getArguments(): array;
	public static function run(array $args);
}
