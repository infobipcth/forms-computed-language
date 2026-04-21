<?php

declare(strict_types=1);

namespace FormsComputedLanguage\Visitors\ExecutionChangeExceptions;

use PhpParser\NodeTraverser;

class DontTraverseChildren extends ExecutionChangeException
{
	public static int $change = NodeTraverser::DONT_TRAVERSE_CHILDREN;
}
