<?php

namespace Tests\Unit;

test('LanguageRunner factory works', function () {
    $this->languageRunner->setCode('$a = 2;');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => 2]);
});

test('Serializing the LanguageRunner throws', function() {
    expect(fn() => (serialize($this->languageRunner)))->toThrow(\Exception::class);
});
