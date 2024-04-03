<?php

namespace FormsComputedLanguage\Visitors\ExecutionChangeExceptions;

class ExecutionChangeException extends \Exception
{
	public static int $change;
	public function getChange(): ?int
	{
		return static::$change ?? null;
	}

	public static function throw()
	{
		$exception = new (static::class);
		//$exception->change = $_change != -1234 ? $_change : static::$change;
		throw $exception;
	}
}
