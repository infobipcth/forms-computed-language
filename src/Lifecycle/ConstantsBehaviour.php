<?php

declare(strict_types=1);

namespace FormsComputedLanguage\Lifecycle;

enum ConstantsBehaviour : string
{
	case Allow = 'whitelist';
	case Disallow = 'blacklist';
}
