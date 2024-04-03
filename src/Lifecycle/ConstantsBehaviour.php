<?php

namespace FormsComputedLanguage\Lifecycle;

enum ConstantsBehaviour : string
{
	case Allow = 'whitelist';
	case Disallow = 'blacklist';
}
