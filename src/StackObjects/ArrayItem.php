<?php

declare(strict_types=1);

namespace FormsComputedLanguage\StackObjects;

class ArrayItem
{
	public mixed $key;
	public mixed $value;

	public function __construct(mixed $_key, mixed $_value)
	{
		$this->key = $_key;
		$this->value = $_value;
	}
}
