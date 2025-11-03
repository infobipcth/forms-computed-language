# Developer Tools

Forms Computed Language (FCL) comes with two command-line tools to aid in the development and debugging of FCL programs. These tools are located in the `tools/` directory.

## `fcleval.php` - Evaluate an FCL File

The `fcleval.php` tool allows you to execute an FCL script with a given set of initial variables and see the final result. It's the primary way to test and debug your FCL code from the command line.

### Usage

```bash
php tools/fcleval.php <path/to/your/file.fcl> <path/to/your/variables.json>
```

-   `<file.fcl>`: The path to the FCL file containing the code you want to execute.
-   `<variables.json>`: A JSON file containing the initial variables for the script. The keys of the JSON object are the variable names.

### Example

Imagine you have a script to calculate a marketing ROI.

**`scratches/roi.fcl`**:
```php
$roi = ($revenue - $cost) / $cost;
$roi_percent = $roi * 100;
```

**`scratches/roi.vars.json`**:
```json
{
  "revenue": 5000,
  "cost": 2000
}
```

You can execute it like this:

```bash
php tools/fcleval.php scratches/roi.fcl scratches/roi.vars.json
```

### Output

The tool will run the FCL code and print the final state of all variables as a JSON object to standard output.

```json
{
    "revenue": 5000,
    "cost": 2000,
    "roi": 1.5,
    "roi_percent": 150
}
```

This is extremely useful for verifying that your FCL logic behaves as expected before integrating it into a larger application.

## `fcldump.php` - Dump the Abstract Syntax Tree (AST)

The `fcldump.php` tool parses an FCL file and prints its Abstract Syntax Tree (AST). This is an advanced debugging tool for understanding exactly how FCL "sees" your code. It can be helpful for diagnosing syntax issues or understanding the evaluation flow.

### Usage

```bash
php tools/fcldump.php <path/to/your/file.fcl> <path/to/your/variables.json>
```

### Example

Using the same files as above.

**`scratches/min.fcl`**:
```php
$a = 2;
$b = $a + $c;
```

**`scratches/min.vars.json`**:
```json
{
  "c": 20 
}
```

Run the dumper:

```bash
php tools/fcldump.php scratches/min.fcl scratches/min.vars.json
```

### Output

The output is a textual representation of the AST, showing the structure of your code.

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

This output shows that the code is parsed as an `AssignOp_Plus` expression (the `+=` operator), which adds the number `1` to the variable `a`. This can be invaluable for debugging complex expressions or control structures.
