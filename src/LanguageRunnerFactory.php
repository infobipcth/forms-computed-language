<?php

namespace FormsComputedLanguage;

use Error;

/**
 * Static factory
 */
final class LanguageRunnerFactory
{
    public static function create(): ?LanguageRunner
    {
        $langRunner = null;

        if ($langRunner === null) {
            $langRunner = new LanguageRunner();
        }

        return $langRunner;
    }
}
