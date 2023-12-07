<?php

namespace FormsComputedLanguage;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\AssignOp\Concat as AssignOpConcat;
use PhpParser\Node\Expr\AssignOp\Div as AssignOpDiv;
use PhpParser\Node\Expr\AssignOp\Minus as AssignOpMinus;
use PhpParser\Node\Expr\AssignOp\Mul as AssignOpMul;
use PhpParser\Node\Expr\AssignOp\Plus as AssignOpPlus;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\BinaryOp\Div;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\BinaryOp\Mul;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\Plus;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class Evaluator extends NodeVisitorAbstract {
    private static array $vars;
    private array $stack = [];

    public function construct(array $vars) {
        self::$vars = $vars;
        $this->stack = [];
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Scalar) {
            $this->stack[] = $node->value ?? null;
        }

        if ($node instanceof Variable) {
            $this->stack[] = self::$vars[$node->name] ?? null;
        }

        if ($node instanceof Expression) {
            //$this->stack[] = $node->expr;
        }

    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Assign) {
            self::$vars[$node->var->name] = array_pop($this->stack);
        }

        if ($node instanceof AssignOp) {
            if ($node instanceof AssignOpPlus) {
                self::$vars[$node->var->name] += array_pop($this->stack);
            }
            if ($node instanceof AssignOpMinus) {
                self::$vars[$node->var->name] -= array_pop($this->stack);
            }
            if ($node instanceof AssignOpMul) {
                self::$vars[$node->var->name] *= array_pop($this->stack);
            }
            if ($node instanceof AssignOpDiv) {
                self::$vars[$node->var->name] /= array_pop($this->stack);
            }
            if ($node instanceof AssignOpConcat) {
                self::$vars[$node->var->name] .= array_pop($this->stack);
            }
        }
        if ($node instanceof BinaryOp) {
            $rhs = array_pop($this->stack);
            $lhs = array_pop($this->stack);
            if ($node instanceof Concat) {
                $this->stack[] = $lhs . $rhs;
                return;
            }
            if ($node instanceof Plus) {    
                $this->stack[] = $lhs + $rhs;
                return;
            }
            if ($node instanceof Minus) {    
                $this->stack[] = $lhs - $rhs;
                return;
            }
            if ($node instanceof Mul) {
                $this->stack[] = $lhs * $rhs;
                return;
            }
            if ($node instanceof Div) {
                $this->stack[] = $lhs / $rhs;
                return;
            }
            if ($node instanceof Equal) {
                $this->stack[] = $lhs == $rhs;
                return;
            }
            if ($node instanceof NotEqual) {
                $this->stack[] = $lhs != $rhs;
            }
            if ($node instanceof Smaller) {
                $this->stack[] = $lhs < $rhs;
            }
            if ($node instanceof SmallerOrEqual) {
                $this->stack[] = $lhs <= $rhs;
            }
            if ($node instanceof Greater) {
                $this->stack[] = $lhs > $rhs;
            }
            if ($node instanceof GreaterOrEqual) {
                $this->stack[] = $lhs >= $rhs;
            }
            if ($node instanceof BooleanAnd) {
                $this->stack[] = ($lhs && $rhs);
            }
            if ($node instanceof BooleanOr) {
                $this->stack[] = ($lhs || $rhs);
            }
        }

    }

    public function afterTraverse(array $nodes)
    {
        var_dump(self::$vars);
    }
}
