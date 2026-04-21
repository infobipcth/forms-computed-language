<?php

declare(strict_types=1);

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\Lifecycle\Stack;
use PhpParser\Node;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\Cast\Double;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\Cast\String_;

class CastVisitor implements VisitorInterface
{
	public static function enterNode(Node &$node)
	{
	}

	public static function leaveNode(Node &$node)
	{
		if ($node instanceof Int_) {
			Stack::push((int) Stack::pop());
		} elseif ($node instanceof Double) {
			Stack::push((float) Stack::pop());
		} elseif ($node instanceof Bool_) {
			Stack::push((bool) Stack::pop());
		} elseif ($node instanceof String_) {
			Stack::push((string) Stack::pop());
		}
	}
}
