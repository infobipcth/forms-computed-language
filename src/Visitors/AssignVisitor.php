<?php

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\Lifecycle\Stack;
use FormsComputedLanguage\Lifecycle\VariableStore;
use FormsComputedLanguage\Visitors\VisitorInterface;
use PhpParser\Node;

class AssignVisitor implements VisitorInterface
{

    static public function enterNode(Node &$node)
    {
        $node->var->setAttribute('parentIsAssignment', true);
        $node->var->setAttribute('parentAssign', $node);
    }

    static public function leaveNode(Node &$node)
    {
        if ($node->getAttribute('isArrayAssignment', false)) {
            $dimensional = $node->getAttribute('isArrayAssignmentByDim', false);
            $value = Stack::pop();
            if ($dimensional) {
                $dim = Stack::pop();
            }
            $name = Stack::pop();
            if (!$dimensional) {
                VariableStore::appendToArrayVariable($name, $value);
            }
            else {
                VariableStore::setArrayVariable($name, $dim, $value);
            }
        } else {
            if (!($node->dim ?? false)) {
                VariableStore::setVariable($node->var->name, Stack::pop());
            }
            else {
                $arrayDim = Stack::pop();
                $arrayVal = Stack::pop();
                VariableStore::setArrayVariable($node->var->name, $arrayDim, $arrayVal);
            }
        }
    }
}
