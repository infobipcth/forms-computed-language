# How FCL Evaluates a Program

Forms Computed Language (FCL) is not a native language; it is a safe, interpreted subset of PHP. Understanding its evaluation process is key to grasping how it ensures security and executes user-provided code.

The entire process is orchestrated by the `LanguageRunner` class.

## The Four Stages of Evaluation

FCL code goes through four main stages from raw string to final result.

### 1. Parsing into an Abstract Syntax Tree (AST)

When you call `$lr->setCode($code)`, FCL does not execute the code directly. Instead, it uses the powerful **`nikic/php-parser`** library to transform the code string into an Abstract Syntax Tree (AST).

An AST is a tree representation of the code's structure. Each node in the tree represents a construct, like a variable, an operator, or a control flow statement.

For example, the code `$a = 2; $b = $a + c;` is parsed into a tree like this:

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

This structured representation is what FCL actually works with, not the raw code string.

### 2. Bootstrapping the Environment

Before evaluation, the `LanguageRunner` sets up a secure, isolated environment using the `Harness` class. This involves:

-   **Initializing the `VariableStore`**: The variables you provide via `$lr->setVars()` are loaded into the `VariableStore`, creating the initial state for the program.
-   **Configuring Constants**: The allowlist/disallowlist rules for constants are prepared via the `ConstantsConfiguration`.
-   **Preparing the `FunctionStore`**: The registry of available functions is made ready.

### 3. Traversing the AST with the `Evaluator`

This is the core of FCL's execution model. FCL uses a **stack-based virtual machine** implemented in the `Evaluator` class.

The `Evaluator` is a `NodeVisitor` (from `nikic/php-parser`). It "walks" or "traverses" the AST, visiting each node one by one. The evaluation logic is based on the **Visitor Pattern**.

-   **`enterNode(Node $node)`**: This method is called when the traverser first encounters a node. It's used for setup tasks, like managing control flow logic for `if` statements.
-   **`leaveNode(Node $node)`**: This method is called after all of a node's children have been visited and evaluated. This is where most of the work happens.

### 4. Stack-Based Computation

The `Evaluator` uses a `Stack` to perform computations. When leaving a node, the results of its children's evaluations are on top of the stack.

Let's trace the evaluation of `5 + 3`:

1.  **Visit `Scalar_Int(5)`**: The `ScalarVisitor` pushes `5` onto the stack.
    -   Stack: `[5]`
2.  **Visit `Scalar_Int(3)`**: The `ScalarVisitor` pushes `3` onto the stack.
    -   Stack: `[5, 3]`
3.  **Leave `BinaryOp_Plus`**: The `BinaryOpVisitor` is triggered.
    -   It pops two values from the stack (`3`, then `5`).
    -   It performs the addition (`5 + 3`).
    -   It pushes the result (`8`) back onto the stack.
    -   Stack: `[8]`

This process continues until the entire tree has been traversed. For an assignment like `$a = 8`, the `AssignVisitor` would pop the value `8` and update the `VariableStore` to set `$a` to `8`.

### How Control Flow Works

Control flow statements like `if`/`elseif`/`else` are handled by their respective visitors (`IfVisitor`, `ElseIfVisitor`, `ElseVisitor`).

When an `If_` node is entered, the `IfVisitor` sets attributes on its child nodes. When the condition (`cond`) is evaluated, its boolean result is stored. Based on this result, the `Evaluator` can decide whether to **skip traversing** entire branches of the AST.

-   If the `if` condition is `true`, the `Evaluator` will not traverse the `elseif` or `else` branches at all, ensuring they are never executed.
-   If it's `false`, it proceeds to the next `elseif` or `else`.

A similar mechanism is used for `foreach` loops, where the `ForeachVisitor` repeatedly traverses the statement block for each item in the array.

## Final Result

After the `Evaluator` has finished traversing the entire AST, the `Stack` is cleared, and the final state of all variables is held in the `VariableStore`. Calling `$lr->getVars()` simply retrieves this final state.
