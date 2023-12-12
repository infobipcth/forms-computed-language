<?php

use FormsComputedLanguage\LanguageRunner;

/** Defined functions work */
test('round works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = round(3.14); $b = round(3.14, 1); $c = round(2.5, 0, 3); $d = round(1.5, 0, 3);');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => 3.0, 'b' => 3.1, 'c' => 2.0, 'd' => 2.0]);
});

test('countSelectedItems works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = countSelectedItems($arr); $b = countSelectedItems($empty);');
    $lr->setVars(['arr' => ['a', 'b', 'c', 'd'], 'empty' => []]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['arr' => ['a', 'b', 'c', 'd'], 'empty' => [], 'a' => 4, 'b' => 0]);
});

test('isSelected works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = isSelected($arr, "a"); $b = isSelected($arr, "h");');
    $lr->setVars(['arr' => ['a', 'b', 'c', 'd']]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['arr' => ['a', 'b', 'c', 'd'], 'a' => true, 'b' => false]);
});
