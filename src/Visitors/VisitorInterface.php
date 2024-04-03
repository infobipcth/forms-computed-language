<?php

namespace FormsComputedLanguage\Visitors;

use PhpParser\Node;

interface VisitorInterface
{
    static public function enterNode(Node& $node);
    static public function leaveNode(Node& $node);
}
