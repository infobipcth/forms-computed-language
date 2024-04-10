<?php

namespace FormsComputedLanguage\StackObjects;

class ArrayItem
{
	public $key;
	public $value;

	public function __construct($_key, $_value)
	{
		$this->key = $_key;
		$this->value = $_value;
	}
}
