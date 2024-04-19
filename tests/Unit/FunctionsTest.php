<?php

namespace Tests\Unit;

use FormsComputedLanguage\Exceptions\ArgumentCountException;
use FormsComputedLanguage\Exceptions\TypeException;

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

test('countSelectedItems throws ArgumentCountException', function() {
    $this->languageRunner->setCode('countSelectedItems();');
    expect(fn() => $this->languageRunner->evaluate())->toThrow(ArgumentCountException::class);
});

test('countSelectedItems returns 0 if argument is not an array', function() {
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

test('isSelected throws ArgumentCountException', function() {
    $this->languageRunner->setCode('isSelected();');
    expect(fn() => $this->languageRunner->evaluate())->toThrow(ArgumentCountException::class);
});

test('isSelected returns false if argument is not an array', function() {
    $this->languageRunner->setCode('$a = isSelected("test", "best");');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => false]);
});


test('round throws ArgumentCountException', function() {
    $this->languageRunner->setCode('round();');
    expect(fn() => $this->languageRunner->evaluate())->toThrow(ArgumentCountException::class);
});

test('round throws TypeException for first argument', function() {
    $this->languageRunner->setCode('round("a", 1);');
    expect(fn() => $this->languageRunner->evaluate())->toThrow(TypeException::class);
});

test('round throws TypeException for second argument', function() {
    $this->languageRunner->setCode('round(12.5, "a");');
    expect(fn() => $this->languageRunner->evaluate())->toThrow(TypeException::class);
});

test('round throws TypeException for third argument', function() {
    $this->languageRunner->setCode('round(12.5, 2, "b");');
    expect(fn() => $this->languageRunner->evaluate())->toThrow(TypeException::class);
});
