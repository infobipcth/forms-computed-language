<?php

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\Lifecycle\Stack;
use FormsComputedLanguage\Lifecycle\VariableStore;
use FormsComputedLanguage\Visitors\VisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;

class VariableVisitor implements VisitorInterface
{

    static public function enterNode(Node &$node)
    {
        if ($node instanceof Variable) { // If this node references a variable e.g. $x, push the variable value to the stack.
            if (!($node->getAttribute('parentIsAssignment', false))) {
                Stack::push(VariableStore::getVariable($node->name));
            }
            else {
                Stack::push($node->name);
            }
        }
    }

    static public function leaveNode(Node &$node)
    {
        // TODO: Implement leaveNode() method.
    }
}
