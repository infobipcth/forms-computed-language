<?php

namespace Tests\Unit;

use FormsComputedLanguage\Exceptions\UndeclaredVariableUsageException;

test('initializing an array works', function () {
    $code = <<<'CODE'
    $a = ['a' => 17, 'b' => 3, 98, 'some string'];
CODE;
    $this->languageRunner->setCode("$code");
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => ['a' => 17, 'b' => 3, 98, 'some string']]);
});

test('initializing another array works', function () {
    $code = <<<'CODE'
    $a = ['a' => 17, 'b' => 3, 98, 'some string', 'c' => ['a' => 17, 'b' => 3, 98, 'some string']];
CODE;
    $this->languageRunner->setCode("$code");
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => ['a' => 17, 'b' => 3, 98, 'some string', 'c' => ['a' => 17, 'b' => 3, 98, 'some string']]]);
});

test('accessing an array item by offset works', function () {
    $code = <<<'CODE'
    $a = [3, 1, 4]; $b = $a[1];
    $c = ['a' => 3, 'b' => 383, 'd' => 93939, 'e' => 'string'];
    $items = [$a[1], $c['b'], $c['e']];
    CODE;
    $this->languageRunner->setCode("$code");
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(
        [
            'a' => [3, 1, 4],
            'b' => 1,
            'c' => ['a' => 3, 'b' => 383, 'd' => 93939, 'e' => 'string'],
            'items' => [1, 383, 'string'],
        ]
    );
});

test('modifying an array item by offset works', function () {
    $code = <<<'CODE'
    $a = [3, 1, 4];
    $a[0] = 77;
    CODE;
    $this->languageRunner->setCode("$code");
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(
        [
            'a' => [77, 1, 4],
        ]
    );
});

test('pushing into an array works', function () {
    $code = <<<'CODE'
    $a = [3, 1];
    $a[] = 4;
    CODE;
    $this->languageRunner->setCode("$code");
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => [3, 1, 4]]);
});

test('null-coalescing an undefined array dimension works', function () {
	$code = <<<'CODE'
	$a = ['a' => 17, 'b' => 3, 98, 'some string'];
	$b = $a['c'] ?? 42;
	CODE;
	$this->languageRunner->setCode("$code");
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();
	expect($this->languageRunner->getVars())->toBe(['a' => ['a' => 17, 'b' => 3, 98, 'some string'], 'b' => 42]);
});

