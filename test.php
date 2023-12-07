<?php
include 'vendor/autoload.php';

use FormsComputedLanguage\Evaluator;
use PhpParser\Error;
use PhpParser\NodeDumper;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;

include 'form.php';

$code = <<<'CODE'
<?php

$radeLiFunkcijeIOperatori = round(5/2 + 0.02);
$radeLiFunkcijeIOperatori += 1;

// channels and tech kalkulacija
$channelsAndTechScore = round(countSelectedItems($dropdown_multiple) / 3);
$channelsAndTechScore += countSelectedItems($checkbox_multiple);

$a = 0;

if ($a < 3) {
    $a = 2;
}
elseif($a <= 5) {
    $a = 8;
}
else {
    $a = $a + 10;
}
CODE;

$morecode = <<<'CODE'
<?php

switch ($dropdown) {
    case 'Value 1':
        $channelsAndTechScore += 0;
    break;
    case 'Value 2':
        $channelsAndTechScore += 1;
    break;
    case 'Value 3':
        $channelsAndTechScore += 3;
    break;
}

switch ($omnichannel) {
    case 'a':
        $channelsAndTechScore += 0;
    break;
    case 'b':
        $channelsAndTechScore += 1;
    break;
    case 'c':
        $channelsAndTechScore += 3;
    break;
}



// communication management kalkulacija
$communication = 0;

if ($customerSupportInquiries == 'a') {
    $communication += 1;
}
elseif ($communicationSupportInquiries == 'b') {
    $communication += 2;
}
else {
    $communication += 3;
}



// neke očite stvari koje želimo podržavat
$marketing = 0;
$marketing = ($communication + $channelsAndTechScore - 100 * $c) * 2;

$b -= 7;

if ($a < 3) {
    $a++;
}
elseif($a <= 5) {
    $a--;
}
elseif($a >= 8) {
    $test = 'blah';
}
elseif($a <= 10) {
    $test .= 'balalala';
}

if (!($a > 7)) {
    $r = 11;
}

if ($a > 7 && $b < 10) {
    $z = 1;
}
if ($a > 10 || $b > 1000) {
    $z = 2;
}
if ($a > 7 && ($b < 1010 || $c > 212)) {
    $z = 3;
}
CODE;

$parser = (new ParserFactory())->create(1);
try {
    $ast = $parser->parse($code);
} catch (Error $error) {
    echo "Parse error: {$error->getMessage()}\n";
    return;
}

$dumper = new NodeDumper;

$traverser = new NodeTraverser;
//var_dump($form);
echo $dumper->dump($ast) . "\n";
$eval = new Evaluator($form);
$traverser->addVisitor($eval);
$traverser->traverse($ast);
