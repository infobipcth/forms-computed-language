<?php

declare(strict_types=1);

namespace FormsComputedLanguage\Visitors\ExecutionChangeExceptions;

use PhpParser\NodeTraverser;

class BreakOutOfLoopException extends ExecutionChangeException
{
	public static int $change = NodeTraverser::STOP_TRAVERSAL;
}
