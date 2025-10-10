<?php

use FormsComputedLanguage\Exceptions\UndeclaredVariableUsageException;

test('using an undeclared var throws', function () {
    $code = <<<'CODE'
    $b = $a;
    CODE;
    $this->languageRunner->setCode("$code");
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    $this->languageRunner->getVars();
})->throws(UndeclaredVariableUsageException::class);


test('using an undeclared var works when null-coalescing', function () {
    $code = <<<'CODE'
    $b = $a ?? 3;
    CODE;
    $this->languageRunner->setCode("$code");
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['b' => 3]);
});
