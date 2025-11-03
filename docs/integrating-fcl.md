# Integrating FCL into Your Project

Forms Computed Language (FCL) is designed to be easily integrated into any PHP project. This guide will walk you through the process, from installation to advanced usage.

## Installation

You can install FCL using Composer. Run the following command in your project's root directory:

```bash
composer require infobipcth/forms-computed-language
```

This will add FCL as a dependency to your project and set up the autoloader.

## Basic Usage

The primary entry point for using FCL is the `LanguageRunner` class. It provides a simple, fluent interface for setting up and executing FCL code.

Here's a basic example:

```php
use FormsComputedLanguage\LanguageRunner;

// Get a singleton instance of the LanguageRunner
$lr = LanguageRunner::getInstance();

// Set the FCL code to execute
$lr->setCode('$a = round($a);');

// Provide initial variables
$lr->setVars(['a' => 3.14]);

// Run the evaluation
$lr->evaluate();

// Retrieve the final state of the variables
// Returns: ['a' => 3]
var_dump($lr->getVars());
```

### The `LanguageRunner` Workflow

1.  **`getInstance()`**: Retrieves a singleton instance of the `LanguageRunner`.
2.  **`setCode(string $code)`**: Parses the provided FCL code into an Abstract Syntax Tree (AST). Note that you don't need to include `<?php` tags.
3.  **`setVars(array $vars)`**: Sets the initial state of variables available to the FCL program. The array keys are variable names (without the `$`).
4.  **`evaluate()`**: Executes the parsed code.
5.  **`getVars()`**: Returns an associative array of all variables and their final values after execution.

## Security: Managing Constants

A critical feature of FCL is its ability to control access to PHP constants. This prevents users from accessing sensitive information (e.g., database credentials stored in constants).

**IMPORTANT**: By default, all PHP constants are accessible. You **must** configure the constant behavior to secure your application.

### Allowlist Mode (Recommended)

In this mode, only explicitly allowed constants are accessible.

```php
$lr = LanguageRunner::getInstance();
$lr->setCode('$a = SOME_SENSITIVE_CONSTANT;');

// Only 'true' and 'false' are allowed
$lr->setAllowedConstants(['true', 'false']);
$lr->setConstantBehaviour('whitelist');

// This will throw an UndeclaredVariableUsageException
$lr->evaluate();
```

### Blacklist Mode

In this mode, all constants are allowed *except* for those you explicitly disallow.

```php
$lr = LanguageRunner::getInstance();
$lr->setCode('$a = DB_PASSWORD;');

// Disallow access to specific constants
$lr->setDisallowedConstants(['DB_USER', 'DB_PASSWORD']);
$lr->setConstantBehaviour('blacklist');

// This will throw an UndeclaredVariableUsageException
$lr->evaluate();
```

## Extending FCL: Adding Custom Functions

You can provide custom PHP functions to your FCL programs through the `FunctionStore`. This allows you to expose safe, application-specific logic to your users.

### Rules for Custom Functions

1.  You cannot redeclare existing built-in or custom functions.
2.  The function must implement the `FormsComputedLanguage\Functions\FunctionInterface`.
3.  Your `run` method will always receive an array of arguments.
4.  You are responsible for validating argument count and types, throwing `ArgumentCountException` or `TypeException` as needed.

### Example: A Custom `add` Function

```php
<?php

namespace MyProject\CustomFunctions;

use FormsComputedLanguage\Functions\FunctionInterface;
use FormsComputedLanguage\Exceptions\ArgumentCountException;
use FormsComputedLanguage\Exceptions\TypeException;
use FormsComputedLanguage\Lifecycle\FunctionStore;

// 1. Create a class that implements FunctionInterface
$addFunction = new class implements FunctionInterface {
    public const string FUNCTION_NAME = 'add';

    public static function getName(): string
    {
        return self::FUNCTION_NAME;
    }

    public static function getArguments(): array
    {
        return [
            '$num1' => 'int|float',
            '$num2' => 'int|float',
        ];
    }

    public static function run(array $args) {
        if (count($args) !== 2) {
            throw new ArgumentCountException("The 'add' function requires exactly two arguments.");
        }

        if (!is_numeric($args[0]) || !is_numeric($args[1])) {
            throw new TypeException("Arguments for 'add' must be numeric.");
        }

        return $args[0] + $args[1];
    }
};

// 2. Register the function with the FunctionStore
FunctionStore::addFunction($addFunction::FUNCTION_NAME, $addFunction);

// 3. Use it in your FCL code
$lr = LanguageRunner::getInstance();
$lr->setCode('$result = add(10, 5);');
$lr->evaluate();

// $result will be 15
var_dump($lr->getVars());
```

By following these integration patterns, you can safely and powerfully extend your applications with user-defined logic.
