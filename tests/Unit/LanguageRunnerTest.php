<?php

use FormsComputedLanguage\LanguageRunner;

test('LanguageRunner works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = 2;');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => 2]);
});
