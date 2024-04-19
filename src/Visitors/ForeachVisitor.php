<?php

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\LanguageRunner;
use FormsComputedLanguage\Lifecycle\VariableStore;
use FormsComputedLanguage\Visitors\ExecutionChangeExceptions\BreakOutOfLoopException;
use FormsComputedLanguage\Visitors\ExecutionChangeExceptions\ContinueLoopException;
use FormsComputedLanguage\Visitors\ExecutionChangeExceptions\DontTraverseChildren;
use PhpParser\Node;
use PhpParser\NodeTraverser;

class ForeachVisitor implements VisitorInterface
{
	public static function enterNode(Node &$node)
	{
		// The Foreach visitor forces re-evaluation of all statements inside the
		// loop while adjusting the iterator variables in the variable store.
		$iteratedArray = VariableStore::getVariable($node->expr->name) ?? [];
		$iterationKeyVariableName = $node->keyVar?->name;
		$iterationValueVariableName = $node->valueVar?->name;
		$isolatedLoopContextTraverser = new NodeTraverser(); // we need a new NodeTraverser for a foreach block.
		$isolatedLoopContextEvaluator = LanguageRunner::getEvaluator(); // returns a copy of the current evaluator.
		$isolatedLoopContextTraverser->addVisitor($isolatedLoopContextEvaluator); // revisit the statements.
		foreach ($iteratedArray as $iterationKey => $iterationValue) {
			// set the iterator variables.
			if ($node?->keyVar) {
				VariableStore::setVariable($node->keyVar?->name, $iterationKey);
			}
			if ($node?->valueVar) {
				VariableStore::setVariable($node->valueVar?->name, $iterationValue);
			}
			// traverse the statements in a loop.

			try {
				// Catch break and continue exceptions.
				$isolatedLoopContextTraverser->traverse($node->stmts);
			} catch (\Exception $e) {
				// If exception is for break, remove from store and don't traverse children.
				if ($e instanceof BreakOutOfLoopException) {
					$iterationKeyVariableName ? VariableStore::unset($iterationKeyVariableName) : null;
					$iterationValueVariableName ? VariableStore::unset($iterationValueVariableName) : null;
					DontTraverseChildren::throw();
				} elseif ($e instanceof ContinueLoopException) {
					// continue to the next iteration.
					continue;
				}
			}
		}

		// unset the foreach iterators when out of foreach scope.
		$iterationKeyVariableName ? VariableStore::unset($iterationKeyVariableName) : null;
		$iterationValueVariableName ? VariableStore::unset($iterationValueVariableName) : null;
		DontTraverseChildren::throw();
	}

	public static function leaveNode(Node &$node)
	{
		// intentionally left empty: no actions needed when leaving the node.
	}
}
