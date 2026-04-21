<?php

declare(strict_types=1);

namespace Tests\Unit;

use FormsComputedLanguage\Functions\FunctionInterface;
use FormsComputedLanguage\Lifecycle\FunctionStore;

/* Plan Phase 1.6: Fix FunctionStore::getFunctionList array merge bug */

test('getFunctionList returns both custom and built-in function names', function () {
	FunctionStore::reset();

	$testFunc = new class implements FunctionInterface {
		public const string FUNCTION_NAME = 'myCustomFunc';

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
			return $args[0];
		}
	};

	FunctionStore::addFunction($testFunc::FUNCTION_NAME, $testFunc);

	$list = FunctionStore::getFunctionList();
	expect($list)
		->toBeArray()
		->toContain('myCustomFunc')
		->toContain('round')
		->toContain('abs')
		->toContain('countSelectedItems')
		->toContain('isSelected')
		->toContain('number_format');
});

/* Unhappy path: getFunctionList after reset returns only built-in functions */
test('getFunctionList after reset returns only built-in function names', function () {
	FunctionStore::reset();

	$list = FunctionStore::getFunctionList();
	expect($list)
		->toBeArray()
		->toContain('round')
		->toContain('abs')
		->not->toContain('myCustomFunc');
});

/* Unhappy path: getFunctionList with multiple custom functions all appear */
test('getFunctionList with multiple custom functions includes all of them', function () {
	FunctionStore::reset();

	$funcA = new class implements FunctionInterface {
		public const string FUNCTION_NAME = 'customA';
		public static function getName(): string { return self::FUNCTION_NAME; }
		public static function getArguments(): array { return []; }
		public static function run(array $args) { return null; }
	};

	$funcB = new class implements FunctionInterface {
		public const string FUNCTION_NAME = 'customB';
		public static function getName(): string { return self::FUNCTION_NAME; }
		public static function getArguments(): array { return []; }
		public static function run(array $args) { return null; }
	};

	FunctionStore::addFunction($funcA::FUNCTION_NAME, $funcA);
	FunctionStore::addFunction($funcB::FUNCTION_NAME, $funcB);

	$list = FunctionStore::getFunctionList();
	expect($list)
		->toContain('customA')
		->toContain('customB')
		->toContain('round')
		->toContain('abs');

	expect(count($list))->toBe(7); // 2 custom + 5 built-in
});
