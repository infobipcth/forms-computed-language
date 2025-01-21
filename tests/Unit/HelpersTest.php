<?php
namespace Tests\Unit;

use FormsComputedLanguage\Helpers;

test('getting a FQN from parts works', function () {
	expect(
		Helpers::getFqnFromParts(['Namespace', 'Subnamespace', 'Class'])
	)->toBe('Namespace\\Subnamespace\\Class');
});
