<?php

namespace FormsComputedLanguage\Visitors\ExecutionChangeExceptions;

use Exception;

class ExecutionChangeException extends Exception
{
	public static int $change;
	public function getChange(): ?int
	{
		return static::$change ?? null;
	}

	public static function throw()
	{
		throw new (static::class);
	}
}
