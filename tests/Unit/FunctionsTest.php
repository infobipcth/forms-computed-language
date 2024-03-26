<?php

namespace Tests\Unit;

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

test('isSelected works', function () {
    $this->languageRunner->setCode('$a = isSelected($arr, "a"); $b = isSelected($arr, "h");');
    $this->languageRunner->setVars(['arr' => ['a', 'b', 'c', 'd']]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['arr' => ['a', 'b', 'c', 'd'], 'a' => true, 'b' => false]);
});
