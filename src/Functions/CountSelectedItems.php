<?php

namespace FormsComputedLanguage\Functions;

use FormsComputedLanguage\Exceptions\ArgumentCountException;
use FormsComputedLanguage\Exceptions\TypeException;

class CountSelectedItems {
    public const FUNCTION_NAME = 'countSelectedItems';

    public static function run($args) {
        $argc = (int)(count($args));
        if ($argc <= 0 || $argc >= 2) {
            throw new ArgumentCountException("the countSelectedItems() function called with {$argc} arguments, but has one required argument and no optional arguments");
        }
        if (!is_array($args[0])) {
            $type = gettype($args[0]);
            throw new TypeException("countSelectedItems() called with {$type} as first argument, requires an array");
        }
        return count($args[0]);
    }
}
