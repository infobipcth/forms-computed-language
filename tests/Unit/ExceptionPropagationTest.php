<?php

declare(strict_types=1);

namespace Tests\Unit;

use FormsComputedLanguage\Exceptions\UnknownTokenException;
use FormsComputedLanguage\Exceptions\UndeclaredVariableUsageException;

/* Plan Phase 1.3 / SR2 — threat T3: ForeachVisitor exception swallowing */

/* TC3: Unknown token inside foreach propagates to caller */
test('UnknownTokenException inside foreach loop is not swallowed', function () {
	$code = <<<'CODE'
	$arr = [1, 2, 3];
	foreach ($arr as $item) {
		$obj = new \stdClass();
	}
	CODE;

	$this->languageRunner->setCode($code);
	$this->languageRunner->setVars([]);

	expect(fn() => $this->languageRunner->evaluate())->toThrow(UnknownTokenException::class);
});

/* TC3 unhappy path: UndeclaredVariableUsageException inside foreach propagates */
test('UndeclaredVariableUsageException inside foreach loop is not swallowed', function () {
	$code = <<<'CODE'
	$arr = [1, 2, 3];
	foreach ($arr as $item) {
		$x = $undeclaredVar;
	}
	CODE;

	$this->languageRunner->setCode($code);
	$this->languageRunner->setVars([]);

	expect(fn() => $this->languageRunner->evaluate())->toThrow(UndeclaredVariableUsageException::class);
});

/* TC3 happy path: break inside foreach still works normally */
test('break inside foreach loop still works after exception fix', function () {
	$code = <<<'CODE'
	$arr = [1, 2, 3, 4, 5];
	$result = 0;
	foreach ($arr as $item) {
		if ($item == 3) {
			break;
		}
		$result += $item;
	}
	CODE;

	$this->languageRunner->setCode($code);
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();

	expect($this->languageRunner->getVars()['result'])->toBe(3);
});

/* TC3 happy path: continue inside foreach still works normally */
test('continue inside foreach loop still works after exception fix', function () {
	$code = <<<'CODE'
	$arr = [1, 2, 3, 4, 5];
	$result = 0;
	foreach ($arr as $item) {
		if ($item == 3) {
			continue;
		}
		$result += $item;
	}
	CODE;

	$this->languageRunner->setCode($code);
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();

	expect($this->languageRunner->getVars()['result'])->toBe(12);
});

/* Unhappy path: nested foreach with exception in inner loop propagates */
test('Exception in inner nested foreach propagates through outer loop', function () {
	$code = <<<'CODE'
	$outer = [1, 2];
	$inner = [1, 2];
	foreach ($outer as $a) {
		foreach ($inner as $b) {
			$x = new \stdClass();
		}
	}
	CODE;

	$this->languageRunner->setCode($code);
	$this->languageRunner->setVars([]);

	expect(fn() => $this->languageRunner->evaluate())->toThrow(UnknownTokenException::class);
});
