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
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class Evaluator extends NodeVisitorAbstract
{
    private array $vars = [];
    private array $stack = [];

    private const FUNCTION_CALLBACKS = [
        'round' => [Round::class, 'run'],
        'countSelectedItems' => [CountSelectedItems::class, 'run'],
    ];

    public function __construct(array $_vars)
    {
        $this->vars = $_vars;
        $this->stack = [];
    }

    public function enterNode(Node $node)
    {
        
        $parent = $node->getAttribute('parentIf');
        $parentElif = $node->getAttribute('parentElseif');
        $parentTernary = $node->getAttribute('parentTernary');

        $nodet = get_class($node);
        echo "ENTERING NODE {$nodet} \n";

        if ($parentElif) {
            if ($parent->getAttribute('condTruthy')) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
            if ($parent->getAttribute('hasEvaluatedElifs') === true) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
            $elifRel = $node->getAttribute('parentElseifRelationship');
            if ($elifRel === 'stmt' && $parentElif->getAttribute('condTruthy') == false) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
            if ($parentElif->getAttribute('condTruthy')) {
                $parent->setAttribute('hasEvaluatedElifs', true);
            }
        }

        if ($parentTernary) {
            echo 'PARENT TERNARY!';
            var_dump($parentTernary->getAttribute('condTruthy'));
            $parentRel = $node->getAttribute('parentTernaryRelationship');
            if ($parentTernary->getAttribute('condTruthy')) {
                if ($parentRel === 'else') {
                    return NodeTraverser::DONT_TRAVERSE_CHILDREN;
                }
            }
            else {
                if ($parentRel === 'if') {
                    return NodeTraverser::DONT_TRAVERSE_CHILDREN;
                }
            }
        }
        if ($parent) {
            if (
                $parent->getAttribute('condTruthy') == false
                && $node->getAttribute('parentRelationship') === 'stmt'
            ) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
        }

        if ($node instanceof Scalar) {
            $this->stack[] = $node->value ?? null;
        }

        if ($node instanceof Variable) {
            $this->stack[] = $this->vars[$node->name] ?? null;
        }

        if ($node instanceof If_) {
            if ($node->cond) {
                $node->cond->setAttribute('parentIf', $node);
                $node->cond->setAttribute('parentRelationship', 'cond');
            }
            foreach ($node->stmts ?? [] as $statement) {
                $statement->setAttribute('parentIf', $node);
                $statement->setAttribute('parentRelationship', 'stmt');
            }
            foreach ($node->elseifs ?? [] as $elseif) {
                $elseif->setAttribute('parentIf', $node);
                $elseif->setAttribute('parentRelationship', 'elif');
            }
            if ($node->else) {
                $node->else->setAttribute('parentIf', $node);
                $node->else->setAttribute('parentRelationship', 'else');
            }
            
            //return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }
        if ($node instanceof ElseIf_) {
            if ($parent->getAttribute('hasEvaluatedElifs')) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
            $node->cond->setAttribute('parentIf', $parent);
            $node->cond->setAttribute('parentElseif', $node);
            $node->cond->setAttribute('parentElseifRelationship', 'cond');
            foreach($node->stmts as $stmt) {
                $stmt->setAttribute('parentIf', $parent);
                $stmt->setAttribute('parentElseif', $node);
                $stmt->setAttribute('parentElseifRelationship', 'stmt');
            }
        }
        if ($node instanceof Else_) {
            if ($parent->getAttribute('condTruthy') == true) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
            if ($parent->getAttribute('hasEvaluatedElifs') == true) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
        }
        if ($node instanceof Ternary) {
            $node->cond->setAttribute('parentTernary', $node);
            $node->cond->setAttribute('parentTernaryRelationship', 'cond');
            $node->if->setAttribute('parentTernary', $node);
            $node->if->setAttribute('parentTernaryRelationship', 'if');
            $node->else->setAttribute('parentTernary', $node);
            $node->else->setAttribute('parentTernaryRelationship', 'else');

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
            }
            if ($node instanceof Plus) {
                $this->stack[] = $lhs + $rhs;
            }
            if ($node instanceof Minus) {
                $this->stack[] = $lhs - $rhs;
            }
            if ($node instanceof Mul) {
                $this->stack[] = $lhs * $rhs;
            }
            if ($node instanceof Div) {
                $this->stack[] = $lhs / $rhs;
            }
            if ($node instanceof Equal) {
                $this->stack[] = $lhs == $rhs;
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
        if ($node instanceof ConstFetch) {
            $this->stack[] = constant(Helpers::getFqnFromParts($node->name->parts));
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
            $this->stack[] = call_user_func_array(self::FUNCTION_CALLBACKS[$functionName], [$argv]);
        }

        if ($parentElseif = $node->getAttribute('parentElseif')) {
            if ($node->getAttribute('parentElseifRelationship') === 'cond') {
                $parentElseif->setAttribute('condTruthy', Helpers::arrayEnd($this->stack));
            }
        }
        if ($parent = $node->getAttribute('parentIf')) {
            if ($node->getAttribute('parentRelationship') === 'cond') {
                $parent->setAttribute('condTruthy', Helpers::arrayEnd($this->stack));
            }
        }
        if ($parentTernary = $node->getAttribute('parentTernary')) {
            if ($node->getAttribute('parentTernaryRelationship') === 'cond') {
                $parentTernary->setAttribute('condTruthy', Helpers::arrayEnd($this->stack));
            }
        }
    }

    public function afterTraverse(array $nodes)
    {
        var_dump($this->vars);
    }
}
