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

test('accessing an array item by offset works', function () {
    $lr = new LanguageRunner;
    $code = <<<'CODE'
    $a = [3, 1, 4]; $b = $a[1];
    $c = ['a' => 3, 'b' => 383, 'd' => 93939, 'e' => 'string'];
    $items = [$a[1], $c['b'], $c['e']];
    CODE;
    $lr->setCode("$code");
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(
        [
            'a' => [3, 1, 4],
            'b' => 1,
            'c' => ['a' => 3, 'b' => 383, 'd' => 93939, 'e' => 'string'],
            'items' => [1, 383, 'string'],
        ]
    );
});

test('modifying an array item by offset works', function () {
    $lr = new LanguageRunner;
    $code = <<<'CODE'
    $a = [3, 1, 4];
    $a[0] = 77;
    CODE;
    $lr->setCode("$code");
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(
        [
            'a' => [77, 1, 4],
        ]
    );
});

test('pushing into an array works', function () {
    $lr = new LanguageRunner;
    $code = <<<'CODE'
    $a = [3, 1];
    $a[] = 4;
    CODE;
    $lr->setCode("$code");
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => [3, 1, 4]]);
});
