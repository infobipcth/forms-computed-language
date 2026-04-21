<?php

declare(strict_types=1);

namespace Tests\Unit;

use FormsComputedLanguage\LanguageRunner;
use FormsComputedLanguage\Lifecycle\ConstantsConfiguration;
use FormsComputedLanguage\Lifecycle\Harness;

/* Plan Phase 1.3 / SR3 — threat I1: Constant access deprecation warning */

/* TC4 prep: Verify deprecation warning when no behaviour is configured */
test('Accessing constants without configured behaviour triggers E_USER_DEPRECATED', function () {
	Harness::setConstantsConfiguration(new ConstantsConfiguration());
	$lr = new LanguageRunner();
	$lr->setCode('$a = true;');
	$lr->setVars([]);

	$deprecationTriggered = false;
	set_error_handler(function ($errno, $errstr) use (&$deprecationTriggered) {
		if ($errno === E_USER_DEPRECATED && str_contains($errstr, 'constant behaviour is deprecated')) {
			$deprecationTriggered = true;
		}
		return true;
	});

	try {
		$lr->evaluate();
	} finally {
		restore_error_handler();
	}

	expect($deprecationTriggered)->toBeTrue();
});

/* Happy path: No deprecation when behaviour is explicitly configured as whitelist */
test('No deprecation warning when constant behaviour is set to whitelist', function () {
	$deprecationTriggered = false;
	set_error_handler(function ($errno, $errstr) use (&$deprecationTriggered) {
		if ($errno === E_USER_DEPRECATED) {
			$deprecationTriggered = true;
		}
		return true;
	});

	try {
		$this->languageRunner->setCode('$a = true;');
		$this->languageRunner->setVars([]);
		$this->languageRunner->evaluate();
	} finally {
		restore_error_handler();
	}

	expect($deprecationTriggered)->toBeFalse();
});

/* Happy path: No deprecation when behaviour is explicitly configured as blacklist */
test('No deprecation warning when constant behaviour is set to blacklist', function () {
	$lr = new LanguageRunner();
	$lr->setConstantBehaviour('blacklist');
	$lr->setDisallowedConstants([]);
	$lr->setCode('$a = true;');
	$lr->setVars([]);

	$deprecationTriggered = false;
	set_error_handler(function ($errno, $errstr) use (&$deprecationTriggered) {
		if ($errno === E_USER_DEPRECATED) {
			$deprecationTriggered = true;
		}
		return true;
	});

	try {
		$lr->evaluate();
	} finally {
		restore_error_handler();
	}

	expect($deprecationTriggered)->toBeFalse();
});

/* TC12: User code cannot read host-defined PHP constants without behaviour set (Phase 2 — for now, verify deprecation) */
test('Deprecation message mentions configuring constant behaviour', function () {
	Harness::setConstantsConfiguration(new ConstantsConfiguration());
	$lr = new LanguageRunner();
	$lr->setCode('$a = true;');
	$lr->setVars([]);

	$deprecationMessage = '';
	set_error_handler(function ($errno, $errstr) use (&$deprecationMessage) {
		if ($errno === E_USER_DEPRECATED) {
			$deprecationMessage = $errstr;
		}
		return true;
	});

	try {
		$lr->evaluate();
	} finally {
		restore_error_handler();
	}

	expect($deprecationMessage)->toContain('setConstantBehaviour');
});
