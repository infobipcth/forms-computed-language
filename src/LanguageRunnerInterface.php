<?php

namespace FormsComputedLanguage;

interface LanguageRunnerInterface
{

    public function setDisallowedConstants(array $disallow);
    public function setAllowedConstants(array $allow);
    public function setConstantBehaviour(string $type);
    public function canAccessConstant(string $name);
    public function setCode(string $code);
    public function setVars(array $vars);
    public function evaluate();
    public function getVars();
}
