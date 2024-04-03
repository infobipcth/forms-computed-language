<?php

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\Lifecycle\Stack;
use FormsComputedLanguage\Visitors\VisitorInterface;
use PhpParser\Node;

class ArrayVisitor implements VisitorInterface
{

    static public function enterNode(Node &$node)
    {
        // TODO: Implement enterNode() method.
    }

    static public function leaveNode(Node &$node)
    {
        $arraySize = count($node?->items);
        $array = [];
        for ($i = $arraySize - 1; $i >= 0; $i--) {
            $arrayItem = Stack::pop();
            if ($arrayItem?->key) {
                $array[$arrayItem->key] = $arrayItem->value;
            } else {
                $array[$i] = $arrayItem->value;
            }
        }
        Stack::push(array_reverse($array));
    }
}
