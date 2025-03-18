<?php

namespace Tests\Unit;

test('if works', function () {
    $this->languageRunner->setCode('if (true) { $a = 3.14; } elseif (true) { $a = 3.0; } else { $b = 2; }');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => 3.14]);
});

test('elseif works', function () {
    $this->languageRunner->setCode('if (false) { $a = 3.14; } elseif (true) { $a = 3.0; } else { $b = 2; }');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => 3.0]);
});

test('else works', function () {
    $this->languageRunner->setCode('if (false) { $a = 3.14; } elseif (false) { $a = 3.0; } else { $b = 2; }');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['b' => 2]);
});

test('only the first valid elseif is executed', function() {
    $this->languageRunner->setVars([]);
    $this->languageRunner->setCode(<<<'CODE'
    $a = 3; $b = 4; $c = -7; $d = 2;
    if ($a < 0) {
        $a = $a * -1;
    }
    elseif ($a < 5) {
        $a = $a + 2;
    }
    elseif ($b > 0) {
        $b = 100;
    }
    elseif ($c < 0) {
        $c = $c * -1;
    }
    else {
        $d = 2000;
    }
    CODE);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe([
        'a' => 5,
        'b' => 4,
        'c' => -7,
        'd' => 2,
    ]);
});

test('multiple elseifs with truthy conditions are not evaluated', function() {
    $this->languageRunner->setVars([]);
    $this->languageRunner->setCode(<<<'CODE'
    $a = 3; $b = 4; $c = -7; $d = 2;
    if ($a < 0) {
        $a = $a * -1;
    }
    elseif ($a > 2) {
        $a = $a + 2;
    }
    elseif ($a >= 2) {
        $a = $a + 100;
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
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe([
        'a' => 5,
        'b' => 4,
        'c' => -7,
        'd' => 2,
    ]);
});

test('ternaries work', function () {
    $this->languageRunner->setCode('$b = true ?  2 + 100 : 500; $c = false ? 100 : 400;');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['b' => 102, 'c' => 400]);
});

test('more complex ternaries work', function () {
	$this->languageRunner->setCode('
	$year1_overage = ($year1_answersMAS > $year1_mostSuitablePackage) ? ($year1_answersMAS - $year1_mostSuitablePackage) : 0;
	$year2_overage = ($year2_answersMAS < $year2_mostSuitablePackage) ? ($year2_answersMAS - $year2_mostSuitablePackage) : 0;
	');
	$this->languageRunner->setVars([
		'year1_answersMAS' => 10,
		'year1_mostSuitablePackage' => 5,
		'year2_answersMAS' => 10,
		'year2_mostSuitablePackage' => 5,
	]);
	$this->languageRunner->evaluate();
	expect($this->languageRunner->getVars())->toBe([
		'year1_answersMAS' => 10,
		'year1_mostSuitablePackage' => 5,
		'year2_answersMAS' => 10,
		'year2_mostSuitablePackage' => 5,
		'year1_overage' => 5,
		'year2_overage' => 0
	]);
});
