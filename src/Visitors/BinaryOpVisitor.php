<?php

namespace FormsComputedLanguage\Visitors;

use FormsComputedLanguage\Exceptions\UnknownTokenException;
use FormsComputedLanguage\Lifecycle\Stack;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\BinaryOp\Div;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\BinaryOp\Mul;
use PhpParser\Node\Expr\BinaryOp\Mod;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\Plus;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;

/**
 * Class handling all binary operators in FCL.
 */
class BinaryOpVisitor implements VisitorInterface
{
	public static function enterNode(Node &$node)
	{
		// intentionally left empty: no actions needed when entering the node.
	}

	public static function leaveNode(Node &$node)
	{
		$nodeType = get_class($node);

		$rhs = Stack::pop();
		$lhs = Stack::pop();

		if ($node instanceof Concat) {
			Stack::push($lhs . $rhs);
		} elseif ($node instanceof Plus) {
			Stack::push($lhs + $rhs);
		} elseif ($node instanceof Minus) {
			Stack::push($lhs - $rhs);
		} elseif ($node instanceof Mul) {
			Stack::push($lhs * $rhs);
		} elseif ($node instanceof Div) {
			Stack::push($lhs / $rhs);
		} elseif ($node instanceof Equal) {
			Stack::push($lhs == $rhs);
		} elseif ($node instanceof NotEqual) {
			Stack::push($lhs != $rhs);
		} elseif ($node instanceof Smaller) {
			Stack::push($lhs < $rhs);
		} elseif ($node instanceof SmallerOrEqual) {
			Stack::push($lhs <= $rhs);
		} elseif ($node instanceof Greater) {
			Stack::push($lhs > $rhs);
		} elseif ($node instanceof GreaterOrEqual) {
			Stack::push($lhs >= $rhs);
		} elseif ($node instanceof BooleanAnd) {
			Stack::push(($lhs && $rhs));
		} elseif ($node instanceof BooleanOr) {
			Stack::push(($lhs || $rhs));
		} elseif ($node instanceof Mod) {
			Stack::push(($lhs % $rhs));
		} else {
			throw new UnknownTokenException("Unknown boolean operator {$nodeType} used");
		}
	}
}
