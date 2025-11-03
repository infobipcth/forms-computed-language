# Debugging FCL Programs

Debugging in Forms Computed Language (FCL) involves a combination of command-line tools and a built-in debug mode. This guide covers the techniques you can use to diagnose issues in your FCL code.

## Using Command-Line Tools

The fastest way to debug is often with the provided tools.

### 1. Check the Final State with `fcleval.php`

If your program is producing incorrect results, the first step is to run it through `fcleval.php` to inspect the final state of all variables.

**Example**:
You expect `$c` to be `15`, but it's not.

**`my_program.fcl`**:
```php
$a = 5;
$b = 10;
$c = $a - $b;
```

**`vars.json`**:
```json
{}
```

**Command**:
```bash
php tools/fcleval.php my_program.fcl vars.json
```

**Output**:
```json
{
    "a": 5,
    "b": 10,
    "c": -5
}
```
This immediately tells you the final values and can help you pinpoint where the logic went wrong.

### 2. Inspect the AST with `fcldump.php`

If your code isn't behaving as you expect, especially with complex expressions, it might be because FCL is parsing it differently than you think. `fcldump.php` shows you the exact Abstract Syntax Tree that FCL evaluates.

**Example**:
You write `$a = 5 + 3 * 2;` expecting `16`, but you get `11`.

**Command**:
```bash
php tools/fcldump.php your_code.fcl your_vars.json
```

**Output (simplified)**:
```
array(
    0: Stmt_Expression(
        expr: Expr_Assign(
            var: Expr_Variable(
                name: a
            )
            expr: Scalar_Int(
                value: 2
            )
        )
    )
    1: Stmt_Expression(
        expr: Expr_Assign(
            var: Expr_Variable(
                name: b
            )
            expr: Expr_BinaryOp_Plus(
                left: Expr_Variable(
                    name: a
                )
                right: Expr_Variable(
                    name: c
                )
            )
        )
    )
)
```

## Using the `FCL_DEBUG` Environment Variable

For complex, step-by-step debugging, FCL includes a verbose debug mode that can be activated with the `FCL_DEBUG` environment variable.

### How to Activate

You can set the environment variable before running your script.

```bash
export FCL_DEBUG=debug
php tools/fcleval.php your_program.fcl your_vars.json
```
Or, you can set it for a single command:
```bash
FCL_DEBUG=debug php tools/fcleval.php your_program.fcl your_vars.json
```

### What It Does

When `FCL_DEBUG` is set to `debug`, the `Evaluator` will print detailed information to standard output for **every step** of the AST traversal.

For each node it enters and leaves, it will dump:
1.  The **type of node** being processed (e.g., `PhpParser\Node\Expr\BinaryOp\Plus`).
2.  The current state of the **`VariableStore`**.
3.  The current state of the **`Stack`**.

### Example Debug Output

Let's run this simple program with `FCL_DEBUG=debug`:

```php
$a = 2;
$b = $a + $c;
```

The variables are set to:
```json
{
  "c": 18
}
```

**Output Snippet**:
```Entering node
/app/forms-computed-language/src/Evaluator.php:94:
string(30) "PhpParser\Node\Stmt\Expression"
Variable store:
/app/forms-computed-language/src/Evaluator.php:96:
array(1) {
  'c' =>
  int(18)
}
Stack: 
/app/forms-computed-language/src/Lifecycle/Stack.php:48:
array(0) {
}
Entering node
/app/forms-computed-language/src/Evaluator.php:94:
string(26) "PhpParser\Node\Expr\Assign"
Variable store:
/app/forms-computed-language/src/Evaluator.php:96:
array(1) {
  'c' =>
  int(18)
}
Stack: 
/app/forms-computed-language/src/Lifecycle/Stack.php:48:
array(0) {
}
Entering node
/app/forms-computed-language/src/Evaluator.php:94:
string(28) "PhpParser\Node\Expr\Variable"
Variable store:
/app/forms-computed-language/src/Evaluator.php:96:
array(1) {
  'c' =>
  int(18)
}
Stack: 
/app/forms-computed-language/src/Lifecycle/Stack.php:48:
array(0) {
}
Leaving node
/app/forms-computed-language/src/Evaluator.php:350:
string(28) "PhpParser\Node\Expr\Variable"
Variable store:
/app/forms-computed-language/src/Evaluator.php:352:
array(1) {
  'c' =>
  int(18)
}
Stack: 
/app/forms-computed-language/src/Lifecycle/Stack.php:48:
array(1) {
  [0] =>
  string(1) "a"
}
Entering node
/app/forms-computed-language/src/Evaluator.php:94:
string(26) "PhpParser\Node\Scalar\Int_"
Variable store:
/app/forms-computed-language/src/Evaluator.php:96:
array(1) {
  'c' =>
  int(18)
}
Stack: 
/app/forms-computed-language/src/Lifecycle/Stack.php:48:
array(1) {
  [0] =>
  string(1) "a"
}
Leaving node
/app/forms-computed-language/src/Evaluator.php:350:
string(26) "PhpParser\Node\Scalar\Int_"
Variable store:
/app/forms-computed-language/src/Evaluator.php:352:
array(1) {
  'c' =>
  int(18)
}
Stack: 
/app/forms-computed-language/src/Lifecycle/Stack.php:48:
array(2) {
  [0] =>
  string(1) "a"
  [1] =>
  int(2)
}
Leaving node
/app/forms-computed-language/src/Evaluator.php:350:
string(26) "PhpParser\Node\Expr\Assign"
Variable store:
/app/forms-computed-language/src/Evaluator.php:352:
array(2) {
  'c' =>
  int(18)
  'a' =>
  int(2)
}
Stack: 
/app/forms-computed-language/src/Lifecycle/Stack.php:48:
array(0) {
}
Leaving node
/app/forms-computed-language/src/Evaluator.php:350:
string(30) "PhpParser\Node\Stmt\Expression"
Variable store:
/app/forms-computed-language/src/Evaluator.php:352:
array(2) {
  'c' =>
  int(18)
  'a' =>
  int(2)
}
Stack: 
/app/forms-computed-language/src/Lifecycle/Stack.php:48:
array(0) {
}
Entering node
/app/forms-computed-language/src/Evaluator.php:94:
string(30) "PhpParser\Node\Stmt\Expression"
Variable store:
/app/forms-computed-language/src/Evaluator.php:96:
array(2) {
  'c' =>
  int(18)
  'a' =>
  int(2)
}
Stack: 
/app/forms-computed-language/src/Lifecycle/Stack.php:48:
array(0) {
}
Entering node
/app/forms-computed-language/src/Evaluator.php:94:
string(26) "PhpParser\Node\Expr\Assign"
Variable store:
/app/forms-computed-language/src/Evaluator.php:96:
array(2) {
  'c' =>
  int(18)
  'a' =>
  int(2)
}
Stack: 
/app/forms-computed-language/src/Lifecycle/Stack.php:48:
array(0) {
}
Entering node
/app/forms-computed-language/src/Evaluator.php:94:
string(28) "PhpParser\Node\Expr\Variable"
Variable store:
/app/forms-computed-language/src/Evaluator.php:96:
array(2) {
  'c' =>
  int(18)
  'a' =>
  int(2)
}
Stack: 
/app/forms-computed-language/src/Lifecycle/Stack.php:48:
array(0) {
}
Leaving node
/app/forms-computed-language/src/Evaluator.php:350:
string(28) "PhpParser\Node\Expr\Variable"
Variable store:
/app/forms-computed-language/src/Evaluator.php:352:
array(2) {
  'c' =>
  int(18)
  'a' =>
  int(2)
}
Stack: 
/app/forms-computed-language/src/Lifecycle/Stack.php:48:
array(1) {
  [0] =>
  string(1) "b"
}
Entering node
/app/forms-computed-language/src/Evaluator.php:94:
string(33) "PhpParser\Node\Expr\BinaryOp\Plus"
Variable store:
/app/forms-computed-language/src/Evaluator.php:96:
array(2) {
  'c' =>
  int(18)
  'a' =>
  int(2)
}
Stack: 
/app/forms-computed-language/src/Lifecycle/Stack.php:48:
array(1) {
  [0] =>
  string(1) "b"
}
Entering node
/app/forms-computed-language/src/Evaluator.php:94:
string(28) "PhpParser\Node\Expr\Variable"
Variable store:
/app/forms-computed-language/src/Evaluator.php:96:
array(2) {
  'c' =>
  int(18)
  'a' =>
  int(2)
}
Stack: 
/app/forms-computed-language/src/Lifecycle/Stack.php:48:
array(1) {
  [0] =>
  string(1) "b"
}
Leaving node
/app/forms-computed-language/src/Evaluator.php:350:
string(28) "PhpParser\Node\Expr\Variable"
Variable store:
/app/forms-computed-language/src/Evaluator.php:352:
array(2) {
  'c' =>
  int(18)
  'a' =>
  int(2)
}
Stack: 
/app/forms-computed-language/src/Lifecycle/Stack.php:48:
array(2) {
  [0] =>
  string(1) "b"
  [1] =>
  int(2)
}
Entering node
/app/forms-computed-language/src/Evaluator.php:94:
string(28) "PhpParser\Node\Expr\Variable"
Variable store:
/app/forms-computed-language/src/Evaluator.php:96:
array(2) {
  'c' =>
  int(18)
  'a' =>
  int(2)
}
Stack: 
/app/forms-computed-language/src/Lifecycle/Stack.php:48:
array(2) {
  [0] =>
  string(1) "b"
  [1] =>
  int(2)
}
Leaving node
/app/forms-computed-language/src/Evaluator.php:350:
string(28) "PhpParser\Node\Expr\Variable"
Variable store:
/app/forms-computed-language/src/Evaluator.php:352:
array(2) {
  'c' =>
  int(18)
  'a' =>
  int(2)
}
Stack: 
/app/forms-computed-language/src/Lifecycle/Stack.php:48:
array(3) {
  [0] =>
  string(1) "b"
  [1] =>
  int(2)
  [2] =>
  int(18)
}
Leaving node
/app/forms-computed-language/src/Evaluator.php:350:
string(33) "PhpParser\Node\Expr\BinaryOp\Plus"
Variable store:
/app/forms-computed-language/src/Evaluator.php:352:
array(2) {
  'c' =>
  int(18)
  'a' =>
  int(2)
}
Stack: 
/app/forms-computed-language/src/Lifecycle/Stack.php:48:
array(2) {
  [0] =>
  string(1) "b"
  [1] =>
  int(20)
}
Leaving node
/app/forms-computed-language/src/Evaluator.php:350:
string(26) "PhpParser\Node\Expr\Assign"
Variable store:
/app/forms-computed-language/src/Evaluator.php:352:
array(3) {
  'c' =>
  int(18)
  'a' =>
  int(2)
  'b' =>
  int(20)
}
Stack: 
/app/forms-computed-language/src/Lifecycle/Stack.php:48:
array(0) {
}
Leaving node
/app/forms-computed-language/src/Evaluator.php:350:
string(30) "PhpParser\Node\Stmt\Expression"
Variable store:
/app/forms-computed-language/src/Evaluator.php:352:
array(3) {
  'c' =>
  int(18)
  'a' =>
  int(2)
  'b' =>
  int(20)
}
Stack: 
/app/forms-computed-language/src/Lifecycle/Stack.php:48:
array(0) {
}
{
    "c": 18,
    "a": 2,
    "b": 20
}
```

### How to Read the Debug Output

-   **Follow the `Stack`**: Watch how values are pushed and popped from the stack. This is the key to understanding the computation flow.
-   **Check the `VariableStore`**: See exactly when variables are assigned or modified.
-   **Match `Entering` and `Leaving`**: The output shows the full depth-first traversal. A node is only "left" after all its children have been entered and left.

This level of detail is invaluable for debugging complex control flow, loops, and nested expressions where simple output checking isn't enough.
