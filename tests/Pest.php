<?php

use FormsComputedLanguage\LanguageRunner;
use PHPUnit\Framework\TestCase;

uses(TestCase::class)->beforeEach(function() {
    $this->languageRunner = LanguageRunner::getInstance();
})->group('unit')->in('Unit');
