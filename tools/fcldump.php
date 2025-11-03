<?php

if ($argc !== 3) {
	echo "fcldump.php - Dumps the AST of a FCL file\n";
	echo "Usage: php fcldump.php <file.fcl> <variables.json>\n";
}

$file = $argv[1];
$variables = $argv[2];

$cwd = getcwd();

if ($file[0] !== '/') {
	$file = $cwd . '/' . $file;
}
if ($variables[0] !== '/') {
	$variables = $cwd . '/' . $variables;
}

$code = file_get_contents($file);
$variables = json_decode(file_get_contents($variables), true);

include __DIR__ . '/../vendor/autoload.php';

use FormsComputedLanguage\LanguageRunner;

$lr = new LanguageRunner();

$lr->setCode($code);
$lr->setVars($variables);

$lr->dumpAst();
