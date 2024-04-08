<?php

namespace Tests\Unit;

use FormsComputedLanguage\Exceptions\UnknownTokenException;
use PhpParser\Error;

test('known PHP token that does not exist in FCL throws an UnknownTokenException', function() {
    $this->languageRunner->setCode('$a = new \stdClass();');
    $this->languageRunner->setVars([]);
    expect(fn () => $this->languageRunner->evaluate())->toThrow(UnknownTokenException::class);
});


test('unknown PHP token throws a parsing exception', function() {
    expect(fn () => $this->languageRunner->setCode('$a = [1, 2, 3]; forInch ($a kao $item) { $item++; }'))->toThrow(Error::class);
});
