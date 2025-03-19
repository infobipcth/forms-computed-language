<?php

if ($argc !== 3) {
	echo "fcleval.php - Evaluates an FCL file \n";
	echo "Usage: php fcldump.php <file.fcl> <variables.json>\n";
}

$file = $argv[1];
$variables = $argv[2];

$code = file_get_contents($file);
$variables = json_decode(file_get_contents($variables), true);

include '../vendor/autoload.php';

use FormsComputedLanguage\LanguageRunner;

$lr = new LanguageRunner();

$lr->setCode($code);
$lr->setVars($variables);

$lr->evaluate();

$vars = $lr->getVars();

echo json_encode($vars, JSON_PRETTY_PRINT);
