<?php
include 'vendor/autoload.php';
include 'Evaluator.php';

use FormsComputedLanguage\Evaluator;
use PhpParser\Error;
use PhpParser\NodeDumper;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;

include 'form.php';

$code = <<<'CODE'
<?php

// channels and tech kalkulacija
$channelsAndTechScore = round(countSelectedItems($productOfInterest) / 3);
$channelsAndTechScore += countSelectedItems($whereAreYouUsingAutomation);
$channelsAndTechScore += countSelectedItems($whereAI);

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
var_dump($form);
$traverser->addVisitor(new Evaluator($form));
$traverser->traverse($ast);
//echo $dumper->dump($ast) . "\n";
