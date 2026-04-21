<?php

declare(strict_types=1);

namespace Tests\Unit;

use FormsComputedLanguage\Exceptions\UndeclaredVariableUsageException;
use FormsComputedLanguage\Lifecycle\Harness;
use FormsComputedLanguage\Lifecycle\Stack;
use FormsComputedLanguage\Lifecycle\VariableStore;

/* Plan Phase 1.5 / G4-G7: Lifecycle method coverage */

/* G7: Stack::peek() */
test('Stack::peek returns the top value without removing it', function () {
	Stack::reset();
	Stack::push('first');
	Stack::push('second');

	expect(Stack::peek())->toBe('second');
	expect(Stack::pop())->toBe('second');
	expect(Stack::pop())->toBe('first');
});

/* G7 unhappy path: Stack::peek on empty stack */
test('Stack::peek returns null on empty stack', function () {
	Stack::reset();

	expect(Stack::peek())->toBeNull();
});

/* G7 unhappy path: Stack::pop on empty stack */
test('Stack::pop returns null on empty stack', function () {
	Stack::reset();

	expect(Stack::pop())->toBeNull();
});

/* SR1: Stack::reset clears all values */
test('Stack::reset clears the stack completely', function () {
	Stack::push(1);
	Stack::push(2);
	Stack::push(3);
	Stack::reset();

	expect(Stack::pop())->toBeNull();
});

/* G4: VariableStore::getArrayVariable with declared member */
test('VariableStore::getArrayVariable returns value for declared array member', function () {
	VariableStore::reset();
	VariableStore::setVariables(['arr' => ['key1' => 'val1', 'key2' => 'val2']]);

	expect(VariableStore::getArrayVariable('arr', 'key1'))->toBe('val1');
});

/* G4 unhappy path: getArrayVariable with undeclared member throws */
test('VariableStore::getArrayVariable throws for undeclared array member', function () {
	VariableStore::reset();
	VariableStore::setVariables(['arr' => ['key1' => 'val1']]);

	expect(fn() => VariableStore::getArrayVariable('arr', 'nonexistent'))
		->toThrow(UndeclaredVariableUsageException::class);
});

/* G4 unhappy path: getArrayVariable with shouldThrow=false returns null */
test('VariableStore::getArrayVariable returns null when shouldThrow is false', function () {
	VariableStore::reset();
	VariableStore::setVariables(['arr' => ['key1' => 'val1']]);

	expect(VariableStore::getArrayVariable('arr', 'nonexistent', 'global', false))->toBeNull();
});

/* G4 unhappy path: getArrayVariable with undeclared array name throws */
test('VariableStore::getArrayVariable throws for undeclared array name', function () {
	VariableStore::reset();
	VariableStore::setVariables([]);

	expect(fn() => VariableStore::getArrayVariable('nonexistent', 'key'))
		->toThrow(UndeclaredVariableUsageException::class);
});

/* G7: VariableStore::resetContext */
test('VariableStore::resetContext clears variables for a specific context', function () {
	VariableStore::reset();
	VariableStore::setVariables(['x' => 1, 'y' => 2], 'testCtx');

	$result = VariableStore::resetContext('testCtx');
	expect($result)->toBe([]);
	expect(VariableStore::getVariables('testCtx'))->toBe([]);
});

/* G7 unhappy path: resetContext on non-existent context */
test('VariableStore::resetContext on non-existent context returns empty array', function () {
	VariableStore::reset();

	$result = VariableStore::resetContext('nonexistent');
	expect($result)->toBe([]);
});

/* G6: Harness::getParser returns the parser set during bootstrap */
test('Harness::getParser returns the parser set during bootstrap', function () {
	$this->languageRunner->setCode('$a = 1;');
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();

	$parser = Harness::getParser();
	expect($parser)->toBeInstanceOf(\PhpParser\Parser::class);
});

/* Unhappy path: VariableStore::getVariable with context that does not exist */
test('VariableStore::getVariable throws for variable in non-existent context', function () {
	VariableStore::reset();

	expect(fn() => VariableStore::getVariable('x', 'nonexistent'))
		->toThrow(UndeclaredVariableUsageException::class);
});

/* VariableStore::appendToArrayVariable */
test('VariableStore::appendToArrayVariable adds item to existing array', function () {
	VariableStore::reset();
	VariableStore::setVariables(['arr' => [1, 2]]);

	VariableStore::appendToArrayVariable('arr', 3);
	expect(VariableStore::getVariables())->toBe(['arr' => [1, 2, 3]]);
});
