# Forms Computed Language

Forms computed language (FCL) is a subset of PHP designed to be safe to execute when the code is arbitrary user input,
while allowing users to manipulate variables, use flow control features and run functions.

It relies on @nikic/php-parser to produce an abstract syntax tree, and implements a "virtual machine" for evaluating a subset
of PHP tokens in a safe manner in PHP.

## Supported features and tokens
* Basic variables (numeric, boolean and string types)
* Arrays and `foreach` loops without references
* Fetching constants from PHP
* Arithmetic and logical operators (`+, -, /, *, !, &&, ||`)
* Assignment operators (`+=, .=` etc.)
* Comparision operators (`<, <=, ==`), string concatenation
* `if/elseif/else` blocks
* The ternary `if ? then : else` operator
* Unary plus and minus (e.g. `-1, +1` are valid)
* Function calls to FCL-provided functions (currently, `countSelectedItems`, `round` and `isSelected`)


## Notably missing or different
* `++`, `--` and `===` operators (an easy PR :))
* `switch` and `match` blocks
* User-defined functions
* OOP and namespaces
* References and unpacking
* Superglobals (`$_GET` etc.)
* Output to stdout, files etc. (you can not echo anything)
* Anonymous arrays in loops (e.g. `foreach([1, 2, 3] as $value){...}`)
* Breaking out of foreach loops with 

## Running FCL code

Basic example:
```php
$lr = LanguageRunner::getInstance();
$lr->setCode('$a = round($a);');
$lr->setVars(['a' => 3.14]);
$lr->evaluate();
// ['a' => 3]
var_dump($lr->getVars());
```

**IMPORTANT SECURITY NOTE**: for booleans to work, and so that users can use constants such as `PHP_ROUND_UP` etc., you need to have some sort of access to constants (at least `true` and `false` constants). HOWEVER, if your project contains sensitive information in constants or PHP is exposing sensitive constants, this will prove to be a security risk!!

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

Whitelist example:
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

The single notable difference is that FCL does not require an opening tag (no need to write `<?php`).

## Defining callee-provided functions

Users can not define functions, but the calling program can define additional functions that are available to users.
These are shared across all language runner instances, and aren't isolated in any way.

Rules:
1. you can not redeclare functions
2. you can not override standard library functions
3. you always get an array of arguments
4. you need to throw `ArgumentCountException` if the count of args is wrong
5. you need to throw `TypeException` if argument type is wrong

Example:
```php
  $testFunction = new class implements FunctionInterface {
		public static function run(array $args) {
      if (count($args) !== 2) {
        throw new ArgumentCountException("I need exactly two args");
      }
			return $args[0] + $args[1];
		}
	};

	FunctionStore::addFunction('testFunction', $testFunction);
	$this->languageRunner->setCode('$a = testFunction(1, 2);');
	$this->languageRunner->evaluate();
```
