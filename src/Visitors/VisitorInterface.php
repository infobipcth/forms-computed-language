<?php

declare(strict_types=1);

namespace FormsComputedLanguage\Visitors;

use PhpParser\Node;

interface VisitorInterface
{
	public static function enterNode(Node &$node);
	public static function leaveNode(Node &$node);
}
