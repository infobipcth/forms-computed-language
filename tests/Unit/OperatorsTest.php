<?php

namespace Tests\Unit;

/** Variable assignment operators */
test('Assignment operator works for all basic types', function () {
    $this->languageRunner->setCode('$a = 2; $b = "b"; $c = true; $d = 3.14;');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => 2, 'b' => 'b', 'c' => true, 'd' => 3.14]);
});

test('Plus assignment operator works', function () {
    $this->languageRunner->setCode('$a = 2; $a += 2;');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => 4]);
});

test('Minus assignment operator works', function () {
    $this->languageRunner->setCode('$a = 2; $a -= 2;');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => 0]);
});

test('Division assignment operator works', function () {
    $this->languageRunner->setCode('$a = 2; $a /= 2;');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => 1]);
});

test('Multiply assignment operator works', function () {
    $this->languageRunner->setCode('$a = 2; $a *= 3;');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => 6]);
});

test('Concatenation assignment operator works', function () {
    $this->languageRunner->setCode('$a = "ba"; $a .= "nana";');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => 'banana']);
});

/** Binary operators */
test('Concatenation operator works', function () {
    $this->languageRunner->setCode('$a = "ba"."nana";');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => 'banana']);
});

test('Plus operator works', function () {
    $this->languageRunner->setCode('$a = 2+8;');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => 10]);
});

test('Minus operator works', function () {
    $this->languageRunner->setCode('$a = 2-8;');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => -6]);
});

test('Multiply operator works', function () {
    $this->languageRunner->setCode('$a = 2*8;');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => 16]);
});

test('Division operator works', function () {
    $this->languageRunner->setCode('$a = 6 / 2;');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => 3]);
});

test('Equal operator works', function () {
    $this->languageRunner->setCode('$a = (2 == 8); $b = (2 == 2);');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => false, 'b' => true]);
});

test('Not equal operator works', function () {
    $this->languageRunner->setCode('$a = (2 != 8); $b = (2 != 2);');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => true, 'b' => false]);
});

test('Smaller operator works', function () {
    $this->languageRunner->setCode('$a = (2 < 8); $b = (2 < 2); $c = (2 < 0);');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => true, 'b' => false, 'c' => false]);
});

test('Smaller or equal operator works', function () {
    $this->languageRunner->setCode('$a = (2 <= 8); $b = (2 <= 2); $c = (2 <= 0);');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => true, 'b' => true, 'c' => false]);
});

test('Greater operator works', function () {
    $this->languageRunner->setCode('$a = (8 > 2); $b = (2 > 2); $c = (0 > 2);');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => true, 'b' => false, 'c' => false]);
});

test('Greater or equal operator works', function () {
    $this->languageRunner->setCode('$a = (8 >= 2); $b = (2 >= 2); $c = (0 >= 2);');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => true, 'b' => true, 'c' => false]);
});

test('Boolean AND operator works', function () {
    $this->languageRunner->setCode('$a = true && true; $b = true && false; $c = false && false;');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => true, 'b' => false, 'c' => false]);
});

test('Boolean OR operator works', function () {
    $this->languageRunner->setCode('$a = true || true; $b = true || false; $c = false || false;');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => true, 'b' => true, 'c' => false]);
});

/** Boolean not */

test('Boolean NOT operator works', function () {
    $this->languageRunner->setCode('$a = !false; $b = !!true; $c = !true;');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => true, 'b' => true, 'c' => false]);
});

/** Unary operators */

test('Unary minus works', function () {
    $this->languageRunner->setCode('$a = 3 * -1;');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => -3]);
});

test('Unary plus works', function () {
    $this->languageRunner->setCode('$a = +3 * +1;');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => 3]);
});
