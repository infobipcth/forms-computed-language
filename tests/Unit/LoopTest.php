<?php

namespace Tests\Unit;

test('foreaching an array works', function () {
    $code = <<<'CODE'
    $a = [3, 1, 4];
    foreach ($a as $index => $value) {
        $a[$index] = $value + 1;
    }
    CODE;
    $this->languageRunner->setCode("$code");
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => [4, 2, 5]]);
});

test('foreaching an array works when index is ommited', function () {
    $code = <<<'CODE'
    $a = [3, 1, 4];
    foreach ($a as $value) {
        $a[] = $value + 1;
    }
    CODE;
    $this->languageRunner->setCode("$code");
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => [3, 1, 4, 4, 2, 5]]);
});

test('check that foreach with control flow works', function(){
    $code = <<<'CODE'
    $a = [1, 2, 3, 4, 5];
    foreach ($a as $index => $value) {
        if ($value > 2) {
            $a[$index] = $value + 1;
        } else {
            $a[$index] = $value - 1;
        }
    }
    CODE;
    $this->languageRunner->setCode("$code");
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => [0, 1, 4, 5, 6]]);
});
