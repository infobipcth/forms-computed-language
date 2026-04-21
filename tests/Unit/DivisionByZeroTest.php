<?php

declare(strict_types=1);

namespace Tests\Unit;

use FormsComputedLanguage\Exceptions\DivisionByZeroException;

/* Plan Phase 1.3 / SR7 — threats D3/D4: Division and modulo by zero */

/* TC7: Division by zero throws DivisionByZeroException */
test('Division by zero throws DivisionByZeroException', function () {
	$this->languageRunner->setCode('$a = 1; $b = 0; $c = $a / $b;');
	$this->languageRunner->setVars([]);

	expect(fn() => $this->languageRunner->evaluate())->toThrow(DivisionByZeroException::class);
});

/* TC8: Modulo by zero throws DivisionByZeroException */
test('Modulo by zero throws DivisionByZeroException', function () {
	$this->languageRunner->setCode('$a = 10; $b = 0; $c = $a % $b;');
	$this->languageRunner->setVars([]);

	expect(fn() => $this->languageRunner->evaluate())->toThrow(DivisionByZeroException::class);
});

/* TC7 unhappy path: Division by zero with float zero */
test('Division by float zero throws DivisionByZeroException', function () {
	$this->languageRunner->setCode('$a = 5; $b = 0.0; $c = $a / $b;');
	$this->languageRunner->setVars([]);

	expect(fn() => $this->languageRunner->evaluate())->toThrow(DivisionByZeroException::class);
});

/* TC7 happy path: Division by non-zero works normally */
test('Division by non-zero value works correctly', function () {
	$this->languageRunner->setCode('$a = 10; $b = 3; $c = $a / $b;');
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();

	$vars = $this->languageRunner->getVars();
	expect($vars['c'])->toBeFloat();
});

/* TC8 happy path: Modulo by non-zero works normally */
test('Modulo by non-zero value works correctly', function () {
	$this->languageRunner->setCode('$a = 10; $b = 3; $c = $a % $b;');
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();

	expect($this->languageRunner->getVars())->toBe(['a' => 10, 'b' => 3, 'c' => 1]);
});

/* G3: Modulo operator basic test */
test('Modulo operator works for basic cases', function () {
	$this->languageRunner->setCode('$a = 17 % 5; $b = 10 % 2; $c = 7 % 7;');
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();

	expect($this->languageRunner->getVars())->toBe(['a' => 2, 'b' => 0, 'c' => 0]);
});

/* Unhappy path: Division by zero with variable-based zero */
test('Division by zero with variable holding zero throws', function () {
	$this->languageRunner->setCode('$c = $a / $b;');
	$this->languageRunner->setVars(['a' => 100, 'b' => 0]);

	expect(fn() => $this->languageRunner->evaluate())->toThrow(DivisionByZeroException::class);
});

/* Unhappy path: Modulo with negative operands and zero */
test('Modulo by zero with negative dividend throws', function () {
	$this->languageRunner->setCode('$a = -10; $b = 0; $c = $a % $b;');
	$this->languageRunner->setVars([]);

	expect(fn() => $this->languageRunner->evaluate())->toThrow(DivisionByZeroException::class);
});

/* F1: /= assignment operator with zero divisor throws DivisionByZeroException */
test('Division assignment by zero throws DivisionByZeroException', function () {
	$this->languageRunner->setCode('$a = 10; $b = 0; $a /= $b;');
	$this->languageRunner->setVars([]);

	expect(fn() => $this->languageRunner->evaluate())->toThrow(DivisionByZeroException::class);
});

/* F1 happy path: /= with non-zero divisor works */
test('Division assignment by non-zero value works correctly', function () {
	$this->languageRunner->setCode('$a = 10; $b = 2; $a /= $b;');
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();

	expect($this->languageRunner->getVars()['a'])->toBe(5);
});
