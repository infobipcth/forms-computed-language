<?php

use FormsComputedLanguage\LanguageRunner;

/** Variable assignment operators */
test('Assignment operator works for all basic types', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = 2; $b = "b"; $c = true; $d = 3.14;');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => 2, 'b' => 'b', 'c' => true, 'd' => 3.14]);
});

test('Plus assignment operator works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = 2; $a += 2;');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => 4]);
});

test('Minus assignment operator works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = 2; $a -= 2;');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => 0]);
});

test('Division assignment operator works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = 2; $a /= 2;');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => 1]);
});

test('Multiply assignment operator works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = 2; $a *= 3;');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => 6]);
});

test('Concatenation assignment operator works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = "ba"; $a .= "nana";');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => 'banana']);
});

/** Binary operators */
test('Concatenation operator works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = "ba"."nana";');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => 'banana']);
});

test('Plus operator works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = 2+8;');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => 10]);
});

test('Minus operator works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = 2-8;');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => -6]);
});

test('Multiply operator works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = 2*8;');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => 16]);
});

test('Division operator works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = 6 / 2;');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => 3]);
});

test('Equal operator works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = (2 == 8); $b = (2 == 2);');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => false, 'b' => true]);
});

test('Not equal operator works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = (2 != 8); $b = (2 != 2);');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => true, 'b' => false]);
});

test('Smaller operator works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = (2 < 8); $b = (2 < 2); $c = (2 < 0);');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => true, 'b' => false, 'c' => false]);
});

test('Smaller or equal operator works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = (2 <= 8); $b = (2 <= 2); $c = (2 <= 0);');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => true, 'b' => true, 'c' => false]);
});

test('Greater operator works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = (8 > 2); $b = (2 > 2); $c = (0 > 2);');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => true, 'b' => false, 'c' => false]);
});

test('Greater or equal operator works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = (8 >= 2); $b = (2 >= 2); $c = (0 >= 2);');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => true, 'b' => true, 'c' => false]);
});

test('Boolean AND operator works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = true && true; $b = true && false; $c = false && false;');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => true, 'b' => false, 'c' => false]);
});

test('Boolean OR operator works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = true || true; $b = true || false; $c = false || false;');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => true, 'b' => true, 'c' => false]);
});

/** Boolean not */

test('Boolean NOT operator works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = !false; $b = !!true; $c = !true;');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => true, 'b' => true, 'c' => false]);
});

/** Unary operators */

test('Unary minus works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = 3 * -1;');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => -3]);
});

test('Unary plus works', function () {
    $lr = new LanguageRunner;
    $lr->setCode('$a = +3 * +1;');
    $lr->setVars([]);
    $lr->evaluate();
    expect($lr->getVars())->toBe(['a' => 3]);
});
