<?php

declare(strict_types=1);

namespace Tests\Unit;

/* Plan Phase 1.3 / SR6 — threat I2: Debug output removal */

/* TC9: Verify FCL_DEBUG does not produce stdout output */
test('FCL_DEBUG environment variable does not produce stdout output', function () {
	$previousValue = getenv('FCL_DEBUG');
	putenv('FCL_DEBUG=debug');

	ob_start();
	$this->languageRunner->setCode('$a = 1 + 2;');
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();
	$output = ob_get_clean();

	// Restore env var
	if ($previousValue === false) {
		putenv('FCL_DEBUG');
	} else {
		putenv("FCL_DEBUG={$previousValue}");
	}

	expect($output)->toBe('');
});

/* TC9 unhappy path: Debug with complex expression also produces no output */
test('FCL_DEBUG does not produce stdout for complex programs', function () {
	$previousValue = getenv('FCL_DEBUG');
	putenv('FCL_DEBUG=debug');

	$code = <<<'CODE'
	$arr = [1, 2, 3];
	$sum = 0;
	foreach ($arr as $v) {
		$sum += $v;
	}
	CODE;

	ob_start();
	$this->languageRunner->setCode($code);
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();
	$output = ob_get_clean();

	if ($previousValue === false) {
		putenv('FCL_DEBUG');
	} else {
		putenv("FCL_DEBUG={$previousValue}");
	}

	expect($output)->toBe('');
});

/* TC9 unhappy path: Other debug values also produce no output */
test('FCL_DEBUG with arbitrary value does not produce stdout', function () {
	$previousValue = getenv('FCL_DEBUG');
	putenv('FCL_DEBUG=verbose');

	ob_start();
	$this->languageRunner->setCode('$a = 1;');
	$this->languageRunner->setVars([]);
	$this->languageRunner->evaluate();
	$output = ob_get_clean();

	if ($previousValue === false) {
		putenv('FCL_DEBUG');
	} else {
		putenv("FCL_DEBUG={$previousValue}");
	}

	expect($output)->toBe('');
});
