<?php

use FormsComputedLanguage\LanguageRunner;

test('foreaching an array works', function () {
    $lr = new LanguageRunner;
    $code = <<<'CODE'
    $a = [3, 1, 4];
    foreach ($a as $index => $value) {
        $a[$index] = $value + 1;
    }
    CODE;
    $lr->setCode("$code");
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => [4, 2, 5]]);
});

test('foreaching an array works when index is ommited', function () {
    $lr = new LanguageRunner;
    $code = <<<'CODE'
    $a = [3, 1, 4];
    foreach ($a as $value) {
        $a[] = $value + 1;
    }
    CODE;
    $lr->setCode("$code");
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => [3, 1, 4, 4, 2, 5]]);
});
