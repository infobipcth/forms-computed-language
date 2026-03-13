<?php

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
			// If this node references a variable e.g. $x, push the variable value to the stack.
			Stack::push((int) Stack::pop());
		} elseif ($node instanceof Double) {
			// If this node references a variable e.g. $x, push the variable value to the stack.
			Stack::push((float) Stack::pop());
		} elseif ($node instanceof Bool_) {
			// If this node references a variable e.g. $x, push the variable value to the stack.
			Stack::push((bool) Stack::pop());
		} elseif ($node instanceof String_) {
			// If this node references a variable e.g. $x, push the variable value to the stack.
			Stack::push((string) Stack::pop());
		}
	}
}
