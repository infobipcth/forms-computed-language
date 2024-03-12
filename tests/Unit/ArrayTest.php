<?php

use FormsComputedLanguage\LanguageRunner;

test('initializing an array works', function () {
    $lr = new LanguageRunner;
    $code = <<<'CODE'
    $a = ['a' => 17, 'b' => 3, 98, 'some string'];
CODE;
    $lr->setCode("$code");
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => ['a' => 17, 'b' => 3, 98, 'some string']]);
});

test('initializing another array works', function () {
    $lr = new LanguageRunner;
    $code = <<<'CODE'
    $a = ['a' => 17, 'b' => 3, 98, 'some string', 'c' => ['a' => 17, 'b' => 3, 98, 'some string']];
CODE;
    $lr->setCode("$code");
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => ['a' => 17, 'b' => 3, 98, 'some string', 'c' => ['a' => 17, 'b' => 3, 98, 'some string']]]);
});
