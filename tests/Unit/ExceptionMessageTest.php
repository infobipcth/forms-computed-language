<?php

declare(strict_types=1);

namespace Tests\Unit;

use FormsComputedLanguage\Exceptions\UndeclaredVariableUsageException;

/* Plan Phase 1.3 / SR10 — threat I3: Exception message content */

/* TC10: UndeclaredVariableUsageException message does not leak shouldThrow */
test('UndeclaredVariableUsageException message does not contain shouldThrow', function () {
	$this->languageRunner->setCode('$b = $undeclaredVar;');
	$this->languageRunner->setVars([]);

	try {
		$this->languageRunner->evaluate();
		// Should not reach here
		expect(true)->toBeFalse();
	} catch (UndeclaredVariableUsageException $e) {
		expect($e->getMessage())->not->toContain('shouldThrow');
	}
});

/* TC10 happy path: Exception message still contains the variable name */
test('UndeclaredVariableUsageException message contains the variable name', function () {
	$this->languageRunner->setCode('$b = $myMissingVar;');
	$this->languageRunner->setVars([]);

	try {
		$this->languageRunner->evaluate();
		expect(true)->toBeFalse();
	} catch (UndeclaredVariableUsageException $e) {
		expect($e->getMessage())->toContain('myMissingVar');
	}
});

/* Unhappy path: Exception message does not leak internal implementation details */
test('UndeclaredVariableUsageException message does not contain internal parameter names', function () {
	$this->languageRunner->setCode('$b = $noSuchVar;');
	$this->languageRunner->setVars([]);

	try {
		$this->languageRunner->evaluate();
		expect(true)->toBeFalse();
	} catch (UndeclaredVariableUsageException $e) {
		$message = $e->getMessage();
		expect($message)->not->toContain('shouldThrow');
		expect($message)->not->toContain('contextHandle');
		expect($message)->not->toContain('static::');
	}
});
