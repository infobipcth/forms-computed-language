<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */

namespace Tests\Unit;

test('LanguageRunner factory works', function () {
    $this->languageRunner->setCode('$a = 2;');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => 2]);
});

test('Unserializing the LanguageRunner throws', function() {
	$serialized = serialize($this->languageRunner);
    expect(fn() => (unserialize($serialized)))->toThrow(\Exception::class);
});
