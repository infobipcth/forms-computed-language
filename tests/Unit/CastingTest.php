<?php

namespace Tests\Unit;

test('casting variables works', function () {
    $code = <<<'CODE'
    $a = (int) 3.14;
	$b = (float) 3;
	$c = (bool) 1;
	$d = (string) 3.14;
	$e = (int) '3.14';
	$f = (float) '3';
	$g = (bool) '1';
CODE;
    $this->languageRunner->setCode("$code");
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe([
		'a' => 3,
		'b' => 3.0,
		'c' => true,
		'd' => '3.14',
		'e' => 3,
		'f' => 3.0,
		'g' => true,
	]);
});
