<?php

namespace Tests\Unit;

use FormsComputedLanguage\Exceptions\ArgumentCountException;
use FormsComputedLanguage\Exceptions\TypeException;
use FormsComputedLanguage\Exceptions\UnknownFunctionException;
use FormsComputedLanguage\Lifecycle\FunctionStore;
use FormsComputedLanguage\Functions\FunctionInterface;
use FormsComputedLanguage\Exceptions\FunctionRedeclarationException;

/** Defined functions work */
test('round works', function () {
	$this->languageRunner->setCode('$a = round(3.14); $b = round(3.14, 1); $c = round(2.5, 0, 3); $d = round(1.5, 0, 3);');
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();
	expect($this->languageRunner->getVars())->toBe(['a' => 3.0, 'b' => 3.1, 'c' => 2.0, 'd' => 2.0]);
});

test('countSelectedItems works', function () {
	$this->languageRunner->setCode('$a = countSelectedItems($arr); $b = countSelectedItems($empty);');
	$this->languageRunner->setVars(['arr' => ['a', 'b', 'c', 'd'], 'empty' => []]);
	$this->languageRunner->evaluate();
	expect($this->languageRunner->getVars())->toBe(['arr' => ['a', 'b', 'c', 'd'], 'empty' => [], 'a' => 4, 'b' => 0]);
});

test('countSelectedItems throws ArgumentCountException', function () {
	$this->languageRunner->setCode('countSelectedItems();');
	expect(fn() => $this->languageRunner->evaluate())->toThrow(ArgumentCountException::class);
});

test('countSelectedItems returns 0 if argument is not an array', function () {
	$this->languageRunner->setCode('$a = countSelectedItems("test");');
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();
	expect($this->languageRunner->getVars())->toBe(['a' => 0]);
});

test('isSelected works', function () {
	$this->languageRunner->setCode('$a = isSelected($arr, "a"); $b = isSelected($arr, "h");');
	$this->languageRunner->setVars(['arr' => ['a', 'b', 'c', 'd']]);
	$this->languageRunner->evaluate();
	expect($this->languageRunner->getVars())->toBe(['arr' => ['a', 'b', 'c', 'd'], 'a' => true, 'b' => false]);
});

test('isSelected throws ArgumentCountException', function () {
	$this->languageRunner->setCode('isSelected();');
	expect(fn() => $this->languageRunner->evaluate())->toThrow(ArgumentCountException::class);
});

test('isSelected returns false if argument is not an array', function () {
	$this->languageRunner->setCode('$a = isSelected("test", "best");');
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();
	expect($this->languageRunner->getVars())->toBe(['a' => false]);
});

test('round throws ArgumentCountException', function () {
	$this->languageRunner->setCode('round();');
	expect(fn() => $this->languageRunner->evaluate())->toThrow(ArgumentCountException::class);
});

test('round throws TypeException for first argument', function () {
	$this->languageRunner->setCode('round("a", 1);');
	expect(fn() => $this->languageRunner->evaluate())->toThrow(TypeException::class);
});

test('round throws TypeException for second argument', function () {
	$this->languageRunner->setCode('round(12.5, "a");');
	expect(fn() => $this->languageRunner->evaluate())->toThrow(TypeException::class);
});

test('round throws TypeException for third argument', function () {
	$this->languageRunner->setCode('round(12.5, 2, "b");');
	expect(fn() => $this->languageRunner->evaluate())->toThrow(TypeException::class);
});

test('abs works', function () {
	$this->languageRunner->setCode('$a = abs(-5); $b = abs(5); $c = abs(-5.5); $d = abs(5.5);');
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();
	expect($this->languageRunner->getVars())->toBe(['a' => 5, 'b' => 5, 'c' => 5.5, 'd' => 5.5]);
});

test('abs throws TypeException if the number of arguments is zero', function () {
	$this->languageRunner->setCode('abs();');
	$this->languageRunner->setVars([]);
	expect(fn() => $this->languageRunner->evaluate())->toThrow(ArgumentCountException::class);
});

test('abs throws TypeException if the number of arguments is wrong', function () {
	$this->languageRunner->setCode('abs(1, 2);');
	$this->languageRunner->setVars([]);
	expect(fn() => $this->languageRunner->evaluate())->toThrow(ArgumentCountException::class);
});

test('abs throws TypeException if the type of arguments is wrong', function () {
	$this->languageRunner->setCode('abs("one");');
	$this->languageRunner->setVars([]);
	expect(fn() => $this->languageRunner->evaluate())->toThrow(TypeException::class);
});

test('Function call to undefined function throws UnknownFunctionException', function () {
	$this->languageRunner->setCode('randomFunction("bla");');
	$this->languageRunner->setVars([]);
	expect(fn() => $this->languageRunner->evaluate())->toThrow(UnknownFunctionException::class);
});

test('Functions can be declared and run properly', function () {
	$testFunction = new class implements FunctionInterface {
		public const string FUNCTION_NAME = 'testFunction';

		public const array ARGUMENTS = [
			'$firstNum' => 'int|float',
			'$secondNum' => 'int|float',
		];

		public static function run(array $args) {
			return $args[0] + $args[1];
		}
	};

	FunctionStore::addFunction($testFunction::FUNCTION_NAME, $testFunction);
	$this->languageRunner->setCode('$a = testFunction(1, 2);');
	$this->languageRunner->evaluate();
	expect($this->languageRunner->getVars())->toBe(['a' => 3]);
});

test('Callee-defined functions cannot be redeclared', function () {
	expect(fn() => FunctionStore::addFunction('testFunction', new class implements FunctionInterface {
		public static function run(array $args) {
			return $args[0] + $args[1];
		}
	}))->toThrow(FunctionRedeclarationException::class);
});

test('Standard library-defined functions cannot be redeclared', function () {
	expect(fn() => FunctionStore::addFunction('countSelectedItems', new class implements FunctionInterface {
		public static function run(array $args) {
			return $args[0] + $args[1];
		}
	}))->toThrow(FunctionRedeclarationException::class);
});

test('calling getFunctionList will return an array of function names', function () {
	expect(FunctionStore::getFunctionList())
		->toBeArray()
		->toContain('abs', 'isSelected');
});

test('calling getFunctionsWithArgumentList will return an array of function names with arguments', function () {
	expect(FunctionStore::getFunctionsWithArgumentList())
		->toBeArray()
		->toContain('round(int|float $num, int $precision = 0, int $mode = 1)', 'countSelectedItems(array $value)', 'isSelected(array $haystack, mixed $needle)', 'abs(int|float $num)');
});
