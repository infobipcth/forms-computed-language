<?php

namespace FormsComputedLanguage;

class Helpers
{
    /**
     * Get the last item in an array.
     *
     * @param array $arr Array.
     * @return mixed|null Returns the last item in the array or null if the array is empty.
     */
    public static function arrayEnd(array $arr)
    {
        return $arr[array_key_last($arr) ?? 0] ?? null;
    }

    /**
     * Given an array of parts of a namespaced constant or method in PHP, return its fully qualified name
     *
     * e.g. for this class the FQN is FormsComputedLanguage\Helpers 
     * @param array $parts
     * @return void
     */
    public static function getFqnFromParts(array $parts)
    {
        return implode("\\", $parts);
    }
}
