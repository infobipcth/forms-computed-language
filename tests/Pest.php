<?php

use FormsComputedLanguage\LanguageRunnerFactory;
use PHPUnit\Framework\TestCase;

uses(TestCase::class)->beforeEach(function() {
    $this->languageRunner = LanguageRunnerFactory::create();
})->group('unit')->in('Unit');
