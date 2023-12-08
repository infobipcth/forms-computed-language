<?php

namespace FormsComputedLanguage;

class Helpers {
    public static function arrayEnd(array $arr) {
        return $arr[array_key_last($arr) ?? 0] ?? null;
    }

    public static function getFqnFromParts(array $parts) {
        return implode("::", $parts);
    }
}
