<?php

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\Visitors\ExecutionChangeExceptions\DontTraverseChildren;
use FormsComputedLanguage\Visitors\VisitorInterface;
use PhpParser\Node;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\NodeTraverser;

class ElseIfVisitor implements VisitorInterface
{

    static public function enterNode(Node &$node)
    {
        $parentIf = $node->getAttribute('parentIf');
        if ($node instanceof ElseIf_) {
            if ($parentIf->getAttribute('hasEvaluatedElifs')) {
                DontTraverseChildren::throw();
            }

            $node->cond->setAttribute('parentIf', $parentIf);
            $node->cond->setAttribute('parentElseif', $node);
            $node->cond->setAttribute('parentElseifRelationship', 'cond');

            foreach ($node->stmts as $stmt) {
                $stmt->setAttribute('parentIf', $parentIf);
                $stmt->setAttribute('parentElseif', $node);
                $stmt->setAttribute('parentElseifRelationship', 'stmt');
            }
        }
    }

    static public function leaveNode(Node &$node)
    {
        // TODO: Implement leaveNode() method.
    }
}
