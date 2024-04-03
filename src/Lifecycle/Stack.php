<?php

namespace FormsComputedLanguage\Lifecycle;

use FormsComputedLanguage\Helpers;

class Stack
{
    private static $stack = [];
    public static function push($value): void
    {
            static::$stack[] = $value;
    }

    public static function pop() {
        return array_pop(static::$stack);
    }

    public static function peek() {
        return Helpers::arrayEnd(static::$stack);
    }

    public static function debug() {
        var_dump(static::$stack);
    }
}
