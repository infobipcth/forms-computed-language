<?php

use FormsComputedLanguage\LanguageRunner;

test('initializing an array works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = [3, 1, 4];');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => [3, 1, 4]]);
});
