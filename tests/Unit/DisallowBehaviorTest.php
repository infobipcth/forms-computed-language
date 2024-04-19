<?php

namespace Tests\Unit;

use FormsComputedLanguage\Exceptions\UndeclaredVariableUsageException;
use FormsComputedLanguage\LanguageRunner;

test('disallowing certain constants work', function () {
	$lr = new LanguageRunner();
	$lr->setCode('$a = DB_USER;');
	$lr->setVars([]);
	$lr->setDisallowedConstants(['DB_USER', 'DB_HOST', 'DB_PASSWORD', 'DB_NAME']);
	$lr->setConstantBehaviour('blacklist');

	expect(fn() => $lr->evaluate())->toThrow(UndeclaredVariableUsageException::class)
		->and($lr->canAccessConstant('DB_USER'))->toBeFalse();

});


