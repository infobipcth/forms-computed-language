<?php

use FormsComputedLanguage\LanguageRunner;

test('if works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('if (true) { $a = 3.14; } elseif (true) { $a = 3.0; } else { $b = 2; }');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => 3.14]);
});

test('elseif works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('if (false) { $a = 3.14; } elseif (true) { $a = 3.0; } else { $b = 2; }');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => 3.0]);
});

test('else works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('if (false) { $a = 3.14; } elseif (false) { $a = 3.0; } else { $b = 2; }');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['b' => 2]);
});

test('only the first valid elseif is executed', function() {
    $lr = new LanguageRunner;
    $lr->setVars([]);
    $lr->setCode(<<<'CODE'
    $a = 3; $b = 4; $c = -7; $d = 2;
    if ($a < 0) {
        $a = $a * -1;
    }
    elseif ($a < 2) {
        $a = $a + 2;
    }
    elseif ($b < 0) {
        $b = 100;
    }
    elseif ($c < 0) {
        $c = $c * -1;
    }
    else {
        $d = 2000;
    }
    CODE);
    $lr->evaluate();
    expect($lr->getVars())->toBe([
        'a' => 3,
        'b' => 4,
        'c' => 7,
        'd' => 2,
    ]);
});
