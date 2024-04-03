<?php

namespace FormsComputedLanguage\Lifecycle;

class VariableStore
{
    private static array $variables = [];
    public static function setVariables(array $variables, string $contextHandle = 'global') {
        static::$variables[$contextHandle] = $variables;
    }
    public static function getVariables(string $contextHandle = 'global') {
        return static::$variables[$contextHandle];
    }

    public static function setVariable(string $name, $value, string $contextHandle = 'global') {
        static::$variables[$contextHandle][$name] = $value;
    }

    public static function setArrayVariable(string $arrayName, $arrayKey, $value, string $contextHandle = 'global') {
        static::$variables[$contextHandle][$arrayName][$arrayKey] = $value;
    }

    public static function appendToArrayVariable(string $arrayName, $value, string $contextHandle = 'global') {
        static::$variables[$contextHandle][$arrayName][] = $value;
    }

    public static function getArrayVariable(string $arrayName, $arrayKey, string $contextHandle = 'global') {
        return static::$variables[$contextHandle][$arrayName][$arrayKey];
    }

    public static function getVariable(string $name, string $contextHandle = 'global') {
        return static::$variables[$contextHandle][$name];
    }

    public static function reset() {
        static::$variables = [];
    }

    public static function resetContext(string $contextHandle = 'global') {
        return static::$variables[$contextHandle] = [];
    }

    public static function unset(string $name, string $contextHandle = 'global') {
        unset(static::$variables[$contextHandle][$name]);
    }
}
