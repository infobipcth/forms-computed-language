# FCL Internals

This document provides a high-level overview of the internal architecture of Forms Computed Language (FCL). It is intended for developers who want to contribute to the project or understand its design principles.

## Core Components

FCL's architecture is composed of several key components that work together to parse, evaluate, and secure user-provided code.

### `LanguageRunner`

-   **Path**: `src/LanguageRunner.php`
-   **Role**: The main public-facing API and entry point for all FCL operations.
-   **Design**: It is a **singleton**, ensuring that resources like the parser are not re-initialized unnecessarily. It orchestrates the entire process, from receiving code to returning the final variables.

### `Evaluator`

-   **Path**: `src/Evaluator.php`
-   **Role**: The heart of FCL, acting as a stack-based "virtual machine".
-   **Design**: It extends `PhpParser\NodeVisitorAbstract`. Its primary job is to traverse the Abstract Syntax Tree (AST) provided by `nikic/php-parser`. It uses a `enterNode()` and `leaveNode()` method to execute logic based on the AST's structure. It relies on a set of "Visitor" classes to handle the logic for specific node types.

#### In-depth: The `Evaluator`'s Traversal Strategy

The `Evaluator`'s power comes from its two-pass approach to each node (`enterNode` and `leaveNode`) and its ability to selectively skip branches of the AST.

##### `enterNode(Node $node)`
This method is called *before* any of a node's children are visited. Its primary role is **setup and control flow management**.

-   **Value Pushing**: For simple nodes like `Scalar` or `Variable`, their corresponding visitors push values onto the `Stack`.
-   **Control Flow Setup**: For complex structures like `If_`, the `IfVisitor` doesn't evaluate the condition here. Instead, it attaches attributes to the child nodes (`cond`, `stmts`, `elseifs`, `else`). These attributes create parent-child relationships that are used later to make decisions.
-   **Skipping Branches**: The most critical task in `enterNode` is deciding whether to skip a part of the code. For example, if an `if` condition has already evaluated to `true`, the `Evaluator` will see this and return `NodeVisitor::DONT_TRAVERSE_CHILDREN` for all subsequent `elseif` and `else` branches. This prevents their conditions and statements from ever being evaluated. This is the core of FCL's execution control.

##### `leaveNode(Node $node)`
This method is called *after* all of a node's children have been visited and their results are available on the `Stack`. Its primary role is **computation and state change**.

-   **Computation**: For a `BinaryOp` (like `+`), its visitor pops the two operands (which were pushed by its children) from the stack, computes the result, and pushes it back onto the stack.
-   **State Update**: For an `Assign` node, its visitor pops the computed value from the stack and updates the `VariableStore`.
-   **Control Flow Resolution**: For an `If_` or `Ternary` node, the condition's result is popped from the stack and stored as an attribute (e.g., `condTruthy`). This result is then used by the parent nodes to decide which blocks to execute.

### `Visitors`

-   **Path**: `src/Visitors/`
-   **Role**: Each visitor contains the evaluation logic for a specific type of AST node.
-   **Design**: This implements the **Visitor Pattern**. For example:
    -   `BinaryOpVisitor.php`: Handles operators like `+`, `-`, `*`, `/`. It pops two values from the stack, performs the operation, and pushes the result back.
    -   `IfVisitor.php`: Manages the logic for `if` statements, determining which branches of the AST should be executed or skipped.
    -   `FuncCallVisitor.php`: Handles function calls, both built-in and custom.
    -   `AssignVisitor.php`: Manages variable assignments, updating the `VariableStore`.
    -   `ScalarVisitor.php` / `VariableVisitor.php`: Push literal values or the current value of variables onto the stack.

This modular design makes it easy to add support for new PHP tokens by simply creating a new Visitor.

## The Lifecycle Components

The `Lifecycle` namespace contains classes that manage the state and environment of an FCL program during its execution.

### `Harness`

-   **Path**: `src/Lifecycle/Harness.php`
-   **Role**: A static class that bootstraps and holds the entire runtime environment for an FCL evaluation.
-   **Responsibilities**:
    -   Initializes and provides access to the `VariableStore`.
    -   Holds the configuration for constants via `ConstantsConfiguration`.

### `VariableStore`

-   **Path**: `src/Lifecycle/VariableStore.php`
-   **Role**: Manages the state of all variables within an FCL program.
-   **Design**: A static class that stores variables in an associative array. It provides methods to get, set, and unset variables, including support for array manipulation. This store is what allows FCL to maintain state across operations.

### `Stack`

-   **Path**: `src/Lifecycle/Stack.php`
-   **Role**: The computational stack for the `Evaluator`.
-   **Design**: A simple static class that wraps a PHP array to provide `push`, `pop`, and `peek` operations. All intermediate results of computations are stored here. For example, in `2 + 3`, `2` and `3` are pushed to the stack, then popped for the addition, and the result `5` is pushed back on.

### `FunctionStore`

-   **Path**: `src/Lifecycle/FunctionStore.php`
-   **Role**: A global registry for functions available to FCL programs.
-   **Design**: It holds a static array of both built-in functions (like `round`, `abs`) and custom, user-provided functions. The `FuncCallVisitor` consults this store to execute functions. It prevents function redeclaration to maintain a stable and secure environment.

### `ConstantsConfiguration`

-   **Path**: `src/Lifecycle/ConstantsConfiguration.php`
-   **Role**: Manages the security rules for accessing PHP constants.
-   **Design**: It holds the `allowlist` or `disallowlist` behavior, along with the lists of allowed or disallowed constants. The `ConstFetchVisitor` uses the `canAccessConstant()` method to determine if a user's code is permitted to access a given constant.

## Exception Handling

FCL defines a set of custom exceptions to provide clear error messages when user code violates rules.

-   **Path**: `src/Exceptions/`
-   **Examples**:
    -   `UnknownTokenException`: Thrown when the `Evaluator` encounters an AST node (a PHP feature) that it does not support.
    -   `UndeclaredVariableUsageException`: Thrown for using an undefined variable or a disallowed constant.
    -   `ArgumentCountException` / `TypeException`: Thrown by functions when they receive the wrong number or type of arguments.
    -   `FunctionRedeclarationException`: Thrown by `FunctionStore` if a function is declared more than once.

This structured exception system is crucial for providing feedback to the user and ensuring the FCL environment remains stable.
