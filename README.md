# Forms Computed Language

Forms Computed Language (FCL) is an interpreted language designed to be safe to execute when the code is arbitrary user input,
while allowing users to manipulate variables, use flow control features and run functions.

FCL is based on PHP syntax and relies on @nikic/php-parser to produce an abstract syntax tree, while reimplementing an evaluator for a subset of PHP's tokens in PHP itself.

## Supported features and tokens
* Scalar variables (numeric, boolean and string types)
* Arrays and `foreach` loops without references
* Fetching constants from PHP
* Arithmetic and logical operators (`+, -, /, *, !, &&, ||`)
* Assignment operators (`+=, .=` etc.)
* Comparision operators (`<, <=, ==`), string concatenation
* `if/elseif/else` blocks
* The ternary `if ? then : else` operator
* Unary plus and minus (e.g. `-1, +1` are valid)
* Function calls to FCL-provided functions (currently, `countSelectedItems`, `round` and `isSelected`) and `FunctionStore` functions

## Notably missing or different
* `++`, `--` and `===` operators (an easy PR :))
* `switch` and `match` blocks
* User-defined functions (developers integrating FCL can use `FunctionStore` to provide custom functions)
* OOP and namespaces
* References and unpacking
* Superglobals (`$_GET` etc.)
* Output to stdio, files etc. (you can not echo anything)
* Anonymous arrays in loops (e.g. `foreach([1, 2, 3] as $value){...}`)

## Getting started

You can install FCL using Composer by running a `composer require` command in your project:

```bash
composer require infobipcth/forms-computed-language
```

As with any other package, you can also install it by adding it manually to `composer.json` or installing it manually by downloading a release.

## Running FCL code

Basic example of running FCL code:
```php
$lr = LanguageRunner::getInstance();
$lr->setCode('$a = round($a);');
$lr->setVars(['a' => 3.14]);
$lr->evaluate();
// ['a' => 3]
var_dump($lr->getVars());
```

## Constants and security

**IMPORTANT SECURITY NOTE**: for booleans to work, and so that users can use constants such as `PHP_ROUND_UP` etc., you need to have some sort of access to constants (at least `true` and `false` constants). HOWEVER, if your project contains sensitive information in constants or PHP is exposing sensitive constants, this will prove to be a security risk!

To mitigate this, you can provide a list of allowed or disallowed constants to the Language Runner prior to code evaluation.

Blacklist example:
```php
$lr = LanguageRunner::getInstance();
$lr->setCode('$a = DB_USER;');
$lr->setVars([]);
$lr->setDisallowedConstants(['DB_USER', 'DB_HOST', 'DB_PASSWORD', 'DB_NAME']);
// IMPORTANT: IF YOU DO NOT SET CONSTANT BEHAVIOUR ALL CONSTANTS ARE ALLOWED!
$lr->setConstantBehaviour('blacklist');
// throws FormsComputedLanguage\Exceptions\UndeclaredVariableUsageException
$lr->evaluate();
var_dump($lr->getVars());
```

Whitelist example - throws an error when a non-whitelisted constant is accessed:
```php
$lr = LanguageRunner::getInstance();
$lr->setCode('$a = DB_USER;');
$lr->setVars([]);
$lr->setAllowedConstants(['true', 'false']);
// IMPORTANT: IF YOU DO NOT SET CONSTANT BEHAVIOUR ALL CONSTANTS ARE ALLOWED!
$lr->setConstantBehaviour('whitelist');
// throws FormsComputedLanguage\Exceptions\UndeclaredVariableUsageException
$lr->evaluate();
var_dump($lr->getVars());
```

Misconfiguration example - DO NOT USE!:
```php
$lr = LanguageRunner::getInstance();
$lr->setCode('$a = DB_USER;');
$lr->setVars([]);
// wrong wrong wrong
$lr->setDisallowedConstants(['true', 'false']);
// very wrong
$lr->setConstantBehaviour('blacklist');
// does not throw
$lr->evaluate();
// ['a' => 'root']
var_dump($lr->getVars());
```

## Writing FCL code
You can write FCL code similarly as you would write PHP. You can use all of the defined tokens, `if` for flow control and call our functions.

A notable difference is that FCL does not require an opening tag (no need to write `<?php` or similar).

## Defining callee-provided functions (FunctionStore)

Users can not define functions on the fly, but the calling program can define additional functions that are available to users.
These are shared across all language runner instances, and aren't isolated in any way. 

You can do this in your project at any time prior to evaluating an FCL program.

Rules:
1. You can not redeclare functions
2. You can not override standard library functions
3. You always get an array of arguments
4. You need to throw `FormsComputedLanguage\Exceptions\ArgumentCountException` if the count of args is wrong
5. You need to throw `FormsComputedLanguage\Exceptions\TypeException` if argument type is wrong

Example:
```php
<?php

namespace MyCustomFunctions;

use FormsComputedLanguage\Functions\FunctionInterface;
use FormsComputedLanguage\Exceptions\ArgumentCountException;
use FormsComputedLanguage\Exceptions\TypeException;


$testFunction = new class implements FunctionInterface {
	public const string FUNCTION_NAME = 'testFunction';

	public static function getName(): string
	{
		return self::FUNCTION_NAME;
	}

	public static function getArguments(): array
	{
		return [
			'$firstNum' => 'int|float',
			'$secondNum' => 'int|float',
		];
	}

	public static function run(array $args) {
		if (count($args) !== 2) {
			throw new ArgumentCountException("The function expects exactly two arguments!");
		}

		if (!is_numeric($args[0]) || !is_numeric($args[1])) {
			throw new TypeException("The function arguments must be numeric!");
		}

		return $args[0] + $args[1];
	}
};

FunctionStore::addFunction($testFunction::FUNCTION_NAME, $testFunction);
$this->languageRunner->setCode('$a = testFunction(1, 2);');
$this->languageRunner->evaluate();
```
