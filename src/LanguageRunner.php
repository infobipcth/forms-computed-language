<?php

namespace FormsComputedLanguage;

use Error;
use PhpParser\NodeDumper;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;

class LanguageRunner {
    private $parser;
    private $code;
    private $vars;
    private $ast;
    private $traverser;
    private $evaluator;

    public function __construct() {
        $this->parser = (new ParserFactory())->create(1);
    }

    public function setCode(string $code) {
        $this->code = '<?php ' . $code;

        try {
            $this->ast = $this->parser->parse($this->code);
        } catch (Error $error) {
            echo "Parse error: {$error->getMessage()}\n";
            return;
        }
    }

    public function setVars(array $vars) {
        $this->vars = $vars;
    }
    
    public function dumpAst() {
        $dumper = new NodeDumper;
        echo $dumper->dump($this->ast);
    }

    public function evaluate() {
        $traverser = new NodeTraverser;
        $this->evaluator = new Evaluator($this->vars, $this);
        $traverser->addVisitor($this->evaluator);
        $traverser->traverse($this->ast);
    }

    public function getVars() {
        return $this->vars;
    }
}
