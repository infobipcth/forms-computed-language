<?php

use FormsComputedLanguage\LanguageRunner;
use PHPUnit\Framework\TestCase;

uses(TestCase::class)->beforeEach(function() {
    $this->languageRunner = LanguageRunner::getInstance();
    $this->languageRunner->setConstantBehaviour('whitelist');
    $this->languageRunner->setAllowedConstants(['true', 'false']);
})->group('unit')->in('Unit');
