<?php

namespace FormsComputedLanguage;

use FormsComputedLanguage\Exceptions\UndeclaredVariableUsageException;
use FormsComputedLanguage\Exceptions\UnknownFunctionException;
use FormsComputedLanguage\Functions\CountSelectedItems;
use FormsComputedLanguage\Functions\Round;
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
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class Evaluator extends NodeVisitorAbstract {
    private array $vars = [];
    private array $stack = [];

    private const FUNCTION_CALLBACKS = [
        'round' => [Round::class, 'run'],
        'countSelectedItems' => [CountSelectedItems::class, 'run'],
    ];

    public function __construct(array $_vars) {
        $this->vars = $_vars;
        $this->stack = [];
    }

    public function enterNode(Node $node)
    {
        $nodet = get_class($node);
        echo "ENTERING NODE {$nodet} \n";
        if ($node instanceof Scalar) {
            $this->stack[] = $node->value ?? null;
        }

        if ($node instanceof Variable) {
            //echo "ADDING VARIABLE {$node->name} TO STACK \n";
            //$v = $this->vars[$node->name] ?? null;
            $this->stack[] = $this->vars[$node->name] ?? null;
        }

        if ($node instanceof Expression) {
            //$this->stack[] = $node->expr;
        }

        if ($node instanceof If_) {
            $this->enterNode($node->cond);
            var_dump($this->stack);
            $condEvalutesTo = array_pop($this->stack);
            if ($condEvalutesTo) {
                foreach ($node->stmts as $childNode) {
                    $this->enterNode($childNode);
                }
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
            
            foreach ($node->elseifs as $elif) {
                $this->enterNode($elif->cond);
                $condEvalutesTo = array_pop($this->stack);
                if ($condEvalutesTo) {
                    foreach ($elif->stmts as $childNode) {
                        $this->enterNode($childNode);
                    }
                    return NodeTraverser::DONT_TRAVERSE_CHILDREN;
                }
            }

            foreach($node->else->stmts as $el) {
                $this->enterNode($el);
            }            
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }


    }

    public function leaveNode(Node $node)
    {
        $nodet = get_class($node);
        echo "LEAVING NODE {$nodet} \n";
        if ($node instanceof Assign) {
            $this->vars[$node->var->name] = array_pop($this->stack);
        }

        if ($node instanceof AssignOp) {
            if ($node instanceof AssignOpPlus) {
                $this->vars[$node->var->name] += array_pop($this->stack);
            }
            if ($node instanceof AssignOpMinus) {
                $this->vars[$node->var->name] -= array_pop($this->stack);
            }
            if ($node instanceof AssignOpMul) {
                $this->vars[$node->var->name] *= array_pop($this->stack);
            }
            if ($node instanceof AssignOpDiv) {
                $this->vars[$node->var->name] /= array_pop($this->stack);
            }
            if ($node instanceof AssignOpConcat) {
                $this->vars[$node->var->name] .= array_pop($this->stack);
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

        if ($node instanceof FuncCall) {
            $functionName = $node->name->getParts()[0];
            $argv = [];
            foreach ($node->args as $arg) {
                $argv[] = array_pop($this->stack);
            }
            $argv = array_reverse($argv);

            if (!isset(self::FUNCTION_CALLBACKS[$functionName])) {
                throw new UnknownFunctionException("Undefined function {$functionName} called");
            }
            //var_dump($functionName, $argv);
            $this->stack[] = call_user_func_array(self::FUNCTION_CALLBACKS[$functionName], [$argv]);
        }
    }

    public function afterTraverse(array $nodes)
    {
        var_dump($this->vars);
    }
}
