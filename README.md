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

Allowlist example - throws an error when a non-allowlisted constant is accessed:
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

## Developer Tools

FCL includes command-line tools to help you debug and test your FCL programs:

### fcldump.php
Dumps the Abstract Syntax Tree (AST) of an FCL file for debugging purposes.

```bash
php fcldump.php <file.fcl> <variables.json>
```

**Example:**
```bash
php fcldump.php scratches/min.fcl scratches/min.vars.json
```

### fcleval.php
Evaluates an FCL file with provided variables and outputs the resulting variable state as JSON.

```bash
php fcleval.php <file.fcl> <variables.json>
```

**Example:**
```bash
php fcleval.php scratches/a_program.fcl scratches/a_program.vars.json
```

## Testing

FCL uses [Pest](https://pestphp.com/) for testing with comprehensive test coverage.

### Running Tests

```bash
# Run all tests (standards, linting, and unit tests)
composer test

# Run only unit tests
composer test:unit

# Run code standards check with PHPCS
composer test:standards

# Run syntax linting
composer test:lint

# Run tests with coverage report
composer test:coverage
```

### Test Structure

Tests are organized in the `tests/Unit/` directory and cover:

- **ArrayTest.php** - Array operations and manipulation
- **CastingTest.php** - Type casting functionality
- **ControlFlowTest.php** - If/elseif/else and ternary operators
- **ControlStructuresTest.php** - Complex control structures
- **DisallowBehaviorTest.php** - Constant whitelist/blacklist security
- **FunctionsTest.php** - Built-in and custom functions
- **LoopTest.php** - Foreach loops and iteration
- **OperatorsTest.php** - Arithmetic, logical, and comparison operators
- **ProgramTest.php** - Complete program execution
- **UnknownTokensTest.php** - Error handling for unsupported syntax

### Writing Tests

All tests extend Pest's test case and automatically configure a `LanguageRunner` instance with safe defaults:

```php
test('your test description', function () {
    $this->languageRunner->setCode('$a = 2 + 2;');
    $this->languageRunner->setVars([]);
    $this->languageRunner->evaluate();
    expect($this->languageRunner->getVars())->toBe(['a' => 4]);
});
```

## Contributing

We welcome all contributions to Forms Computed Language! 

Here's how you can help:

### Getting Started

1. Fork the repository
2. Clone your fork: `git clone https://github.com/your-username/forms-computed-language.git`
3. Install dependencies: `composer install`
4. Create a feature branch: `git checkout -b feature/your-feature-name`

### Development Workflow

1. **Write tests first** - Add tests for new features or bug fixes in the appropriate test file
2. **Implement your changes** - Follow the existing code style and patterns
3. **Run the test suite** - Ensure all tests pass: `composer test`
4. **Check code standards** - Fix any style issues: `composer test:standards`
5. **Commit your changes** - Use clear, descriptive commit messages
6. **Push and create a PR** - Submit a pull request with a description of your changes

### Code Style

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards, but with tabs (see phpcs.xml)
- Use meaningful variable and method names
- Add PHPDoc comments for public methods
- Keep methods focused and single-purpose

### Adding New Features

#### Adding a new operator or token:
1. Create a new Visitor in `src/Visitors/` implementing `VisitorInterface`
2. Add the visitor to `Evaluator.php` in the appropriate `enterNode()` or `leaveNode()` section
3. Add comprehensive tests in `tests/Unit/`

#### Adding a new built-in function:
1. Create a new function class in `src/Functions/` implementing `FunctionInterface`
2. Register it in `FuncCallVisitor::FUNCTION_CALLBACKS`
3. Add tests in `tests/Unit/FunctionsTest.php`

### Pull Request Guidelines

- Ensure your PR has a clear title and description
- Reference any related issues
- Include tests for new functionality
- Make sure all CI checks pass
- Keep PRs focused on a single feature or fix
- Update documentation if needed

### Reporting Issues

When reporting bugs, please include:
- FCL code that reproduces the issue
- Expected behavior
- Actual behavior
- PHP version and environment details

### Security

If you discover a security vulnerability which is unsuitable to be reported publicly, please email us at web@infobip.com or use the [Infobip Coordinated Vulnerability Disclosure program](https://www.infobip.com/security-trust-center/cvd-policy).

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Documentation

For detailed documentation, please see the `docs` directory:

- [Integrating FCL into Your Project](./docs/integrating-fcl.md)
- [Understanding How FCL Evaluates a Program](./docs/evaluation.md)
- [FCL Internals](./docs/internals.md)
- [Developer Tools](./docs/tools.md)
- [Debugging FCL](./docs/debugging.md)

_Note that the documentation is AI-assisted and might be inaccurate at times. If you have any questions, feel free to open an issue._

## About

This project is maintained by Infobip's Creative Tech Hub - the team behind Infobip.com.
