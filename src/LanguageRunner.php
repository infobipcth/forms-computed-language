<?php

namespace FormsComputedLanguage;

use Error;
use PhpParser\NodeDumper;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;

/**
 * Boots and shuts down the evaluator.
 */
class LanguageRunner
{
    private $parser;
    private $code;
    private $vars;
    private $ast;
    private $traverser;
    private $evaluator;
    private $constantSettings;

    /**
     * Construct the language runner. Initialize the parser.
     */
    public function __construct()
    {
        $this->parser = (new ParserFactory())->create(1);
    }

    /**
     * Set the constants blacklist.
     *
     * @param array $disallow Constants blacklist.
     * @return void
     */
    public function setDisallowedConstants(array $disallow)
    {
        $this->constantSettings['disallow'] = $disallow;
    }

    /**
     * Set the constants whitelist.
     *
     * @param array $allow Constants whitelist.
     * @return void
     */
    public function setAllowedConstants(array $allow)
    {
        $this->constantSettings['allow'] = $allow;
    }

    /**
     * Set to 'whitelist' to only allow whitelisted constants, or to 'blacklist' to allow all but blacklisted.
     *
     * @param string $type 'whitelist' or 'blacklist'.
     * @return void
     */
    public function setConstantBehaviour(string $type)
    {
        $this->constantSettings['behaviour'] = $type;
    }

    /**
     * Given a constant name, grants or denies access. Note that if behaviour is not set, all constants are always available!
     *
     * @param string $name Constant name.
     * @return boolean true if settings allow access, false otherwise.
     */
    public function canAccessConstant(string $name)
    {
        if (!isset($this->constantSettings['behaviour'])) {
            return true;
        }

        if ($this->constantSettings['behaviour'] === 'whitelist') {
            return in_array($name, $this->constantSettings['allow'], true);
        } else {
            return !in_array($name, $this->constantSettings['disallow'], true);
        }
    }

    /**
     * Set the code to be executed. Parses the code.
     *
     * @param string $code Code to be executed.
     * @throws Error in case of parsing errors.
     * @return void
     */
    public function setCode(string $code)
    {
        $this->code = '<?php ' . $code;
        $this->ast = $this->parser->parse($this->code);
    }

    /**
     * Set initial variables for the VM.
     *
     * @param array $vars Array of variables. Array keys are variable names.
     * @return void
     */
    public function setVars(array $vars)
    {
        $this->vars = $vars;
    }

    /**
     * Dump the parsed AST to stdout.
     *
     * @return void
     */
    public function dumpAst()
    {
        $dumper = new NodeDumper();
        echo $dumper->dump($this->ast);
    }

    /**
     * Run the code.
     *
     * @return void
     */
    public function evaluate()
    {
        $traverser = new NodeTraverser();
        $this->evaluator = new Evaluator($this->vars, $this);
        $traverser->addVisitor($this->evaluator);
        $traverser->traverse($this->ast);
    }

    /**
     * Get the variables currently defined in the VM.
     *
     * @return array Variables defined in the VM. Keys are variable names, values are variable values.
     */
    public function getVars()
    {
        return $this->vars;
    }

    /**
     * Get the constant behaviour settings.
     * 
     * @return array Constant behaviour settings.
     */
    public function getConstantBehaviour() {
        return $this->constantSettings ?? [];
    }

    /**
     * Set the constant behaviour settings. Dangerous: only call when bootstrapping a context-isolated language runner.
     * 
     * @param array $constantSettings The constant settings array.
     * @return array Constant behaviour settings.
     */
    public function setConstantSettings(array $constantSettings) {
        $this->constantSettings = $constantSettings;
    }
}
