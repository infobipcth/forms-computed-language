<?php

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\LanguageRunner;
use FormsComputedLanguage\Lifecycle\VariableStore;
use FormsComputedLanguage\Visitors\ExecutionChangeExceptions\DontTraverseChildren;
use FormsComputedLanguage\Visitors\VisitorInterface;
use PhpParser\Node;
use PhpParser\NodeTraverser;

class ForeachVisitor implements VisitorInterface
{
	public static function enterNode(Node &$node)
	{
		$iteratedArray = VariableStore::getVariable($node->expr->name) ?? [];
		$iterationKeyVariableName = $node->keyVar?->name;
		$iterationValueVariableName = $node->valueVar?->name;
		$isolatedLoopContextTraverser = new NodeTraverser();
		$isolatedLoopContextEvaluator = LanguageRunner::getEvaluator();
		$isolatedLoopContextTraverser->addVisitor($isolatedLoopContextEvaluator);
		foreach ($iteratedArray as $iterationKey => $iterationValue) {
			if ($node?->keyVar) {
				VariableStore::setVariable($node->keyVar?->name, $iterationKey);
			}
			if ($node?->valueVar) {
				VariableStore::setVariable($node->valueVar?->name, $iterationValue);
			}
			$isolatedLoopContextTraverser->traverse($node->stmts);
		}
		$iterationKeyVariableName ? VariableStore::unset($iterationKeyVariableName) : null;
		$iterationValueVariableName ? VariableStore::unset($iterationValueVariableName) : null;
		DontTraverseChildren::throw();
	}

	public static function leaveNode(Node &$node)
	{
		// TODO: Implement leaveNode() method.
	}
}
