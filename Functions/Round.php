<?php

namespace FormsComputedLanguage\Functions;

use FormsComputedLanguage\Exceptions\ArgumentCountException;
use FormsComputedLanguage\Exceptions\TypeException;

class Round {
    public const FUNCTION_NAME = 'round';

    public static function run($args) {
        $argc = (int)(count($args));
        if ($argc <= 0 || $argc >= 4) {
            throw new ArgumentCountException("the round() function called with {$argc} arguments, but has one required argument and two optional arguments");
        }
        if (!is_numeric($args[0])) {
            $type = gettype($args[0]);
            throw new TypeException("round() called with {$type} as first argument, requires a numeric argument");
        }
        switch ($argc) {
            case 1:
                return round($args[0]);
            case 2:
                return round($args[0], $args[1]);
            case 3:
                return round($args[0], $args[1], $args[2]);
        }
    }
}
