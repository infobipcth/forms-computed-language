<?php

namespace Tests\Unit;

test('breaking out of loop works', function () {
	$code = <<<'CODE'
    $a = [3, 1, 4];
    $b = [];
    foreach ($a as $index => $value) {
        if ($value == 1) {
            $b[] = $value;
            break;
        }

        $a[$index] = $value + 1;
    }
    CODE;
	$this->languageRunner->setCode("$code");
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();
	expect($this->languageRunner->getVars())->toBe(['a' => [4, 1, 4], 'b' => [1]]);
});

test('breaking out of loop with multiple nesting works', function () {
	$code = <<<'CODE'
    $a = '';
    $b = '';

    $arr = [1, 2, 3];

    foreach ($arr as $value) {
        foreach ($arr as $value) {
          $a = 'ok';
          break;
        }
        $b = 'wait';
        break;
    }
    CODE;
	$this->languageRunner->setCode("$code");
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();
	expect($this->languageRunner->getVars())->toBe(['a' => 'ok', 'b' => 'wait', 'arr' => [1, 2, 3]]);
});

test('breaking out of loop with one level works', function () {
	$code = <<<'CODE'
    $a = '';
    $b = '';

    $arr = [1, 2, 3];

    foreach ($arr as $value) {
        foreach ($arr as $value) {
          $a = 'ok';
          break 1;
        }
        $b = 'wait';
        break;
    }
    CODE;
	$this->languageRunner->setCode("$code");
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();
	expect($this->languageRunner->getVars())->toBe(['a' => 'ok', 'b' => 'wait', 'arr' => [1, 2, 3]]);
});

test('continuing the loop works', function () {
	$code = <<<'CODE'
    $numbers = [1, 2, 3, 4, 5];
    $evenNumbers = [];

    foreach ($numbers as $number) {
        if ($number % 2 == 1) {
            continue;
        }
        $evenNumbers[] = $number;
    }
    CODE;
	$this->languageRunner->setCode("$code");
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();
	expect($this->languageRunner->getVars())->toBe(['numbers' => [1, 2, 3, 4, 5], 'evenNumbers' => [2, 4]]);
});
