<?php

declare(strict_types=1);

namespace Tests\Unit;

use FormsComputedLanguage\Functions\FunctionInterface;
use FormsComputedLanguage\Lifecycle\FunctionStore;
use FormsComputedLanguage\Lifecycle\Stack;

/* Plan Phase 1.3 / SR1 — threat T1: State leakage between evaluations */

/* TC1: Verify Stack is empty at the start of each evaluate() call */
test('Stack is reset between evaluations — stale values do not leak', function () {
	Stack::push('stale_value_1');
	Stack::push('stale_value_2');

	$this->languageRunner->setCode('$a = 1;');
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();

	expect($this->languageRunner->getVars())->toBe(['a' => 1]);
});

/* TC1 unhappy path: Stack corruption from exception mid-evaluation */
test('Stack is reset even after a previous evaluation throws an exception', function () {
	$this->languageRunner->setCode('$a = new \stdClass();');
	$this->languageRunner->setVars([]);

	try {
		$this->languageRunner->evaluate();
	} catch (\Exception $e) {
		// Expected — unknown token
	}

	$this->languageRunner->setCode('$b = 42;');
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();

	expect($this->languageRunner->getVars())->toBe(['b' => 42]);
});

/* TC2: Verify FunctionStore::reset() clears custom functions */
test('FunctionStore::reset clears custom functions', function () {
	$testFunction = new class implements FunctionInterface {
		public const string FUNCTION_NAME = 'ephemeralFunc';

		public static function getName(): string
		{
			return self::FUNCTION_NAME;
		}

		public static function getArguments(): array
		{
			return ['$x' => 'int'];
		}

		public static function run(array $args)
		{
			return $args[0] * 10;
		}
	};

	FunctionStore::addFunction($testFunction::FUNCTION_NAME, $testFunction);
	$list = FunctionStore::getFunctionList();
	expect($list)->toContain('ephemeralFunc');

	FunctionStore::reset();
	$listAfterReset = FunctionStore::getFunctionList();
	expect($listAfterReset)->not->toContain('ephemeralFunc');
});

/* TC2 unhappy path: FunctionStore reset does not affect built-in functions */
test('Built-in functions remain available after FunctionStore reset', function () {
	FunctionStore::reset();
	$list = FunctionStore::getFunctionList();
	expect($list)
		->toContain('round')
		->toContain('abs')
		->toContain('countSelectedItems')
		->toContain('isSelected')
		->toContain('number_format');
});

/* Custom functions persist across evaluations (by design — host-registered) */
test('Custom functions persist across evaluation cycles', function () {
	FunctionStore::reset();

	$testFunction = new class implements FunctionInterface {
		public const string FUNCTION_NAME = 'persistentFunc';

		public static function getName(): string
		{
			return self::FUNCTION_NAME;
		}

		public static function getArguments(): array
		{
			return ['$x' => 'int'];
		}

		public static function run(array $args)
		{
			return $args[0] + 100;
		}
	};

	FunctionStore::addFunction($testFunction::FUNCTION_NAME, $testFunction);

	$this->languageRunner->setCode('$a = persistentFunc(1);');
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();
	expect($this->languageRunner->getVars())->toBe(['a' => 101]);

	$this->languageRunner->setCode('$b = persistentFunc(2);');
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();
	expect($this->languageRunner->getVars())->toBe(['b' => 102]);
});
