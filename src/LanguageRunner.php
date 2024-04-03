<?php

namespace FormsComputedLanguage;

use Error;
use FormsComputedLanguage\Lifecycle\Harness;
use FormsComputedLanguage\Lifecycle\VariableStore;
use PhpParser\NodeDumper;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;

/**
 * Boots and shuts down the evaluator.
 */
class LanguageRunner implements LanguageRunnerInterface
{
	private $parser;
	private $code;
	private $vars;
	private $ast;
	private static $evaluator;
	private static $instances = [];

	// @codeCoverageIgnoreStart
	protected function __clone()
	{
	}

	// LanguageRunner can not be serialized, so there is no way to test this.
	public function __wakeup()
	{
		throw new \Exception("Cannot unserialize a singleton.");
	}
	// @codeCoverageIgnoreEnd

	/**
	 * Construct the language runner. Initialize the parser.
	 */
	public function __construct()
	{
		$this->parser = (new ParserFactory())->create(3);
	}

	public static function getInstance(): LanguageRunner
	{
		$class = static::class;
		if (!isset(self::$instances[$class])) {
			self::$instances[$class] = new static();
		}

		return self::$instances[$class];
	}

	/**
	 * Set the constants blacklist.
	 *
	 * @param array $disallow Constants blacklist.
	 * @return void
	 */
	public function setDisallowedConstants(array $disallow)
	{
		Harness::getConstantsConfiguration()->setDisallowedConstants($disallow);
	}

	/**
	 * Set the constants whitelist.
	 *
	 * @param array $allow Constants whitelist.
	 * @return void
	 */
	public function setAllowedConstants(array $allow)
	{
		Harness::getConstantsConfiguration()->setAllowedConstants($allow);
	}

	/**
	 * Set to 'whitelist' to only allow whitelisted constants, or to 'blacklist' to allow all but blacklisted.
	 *
	 * @param string $type 'whitelist' or 'blacklist'.
	 * @return void
	 */
	public function setConstantBehaviour(string $type)
	{
		Harness::getConstantsConfiguration()->setConstantBehaviour($type);
	}

	/**
	 * Given a constant name, grants or denies access. Note that if behaviour is not set, all constants are always available!
	 *
	 * @param string $name Constant name.
	 * @return boolean true if settings allow access, false otherwise.
	 */
	public function canAccessConstant(string $name)
	{
		return Harness::getConstantsConfiguration()->canAccessConstant($name);
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
		VariableStore::reset();
		Harness::bootstrap(variables: $this->vars, _parser: $this->parser);
		$traverser = new NodeTraverser();
		self::$evaluator = new Evaluator($this);
		$traverser->addVisitor(self::$evaluator);
		$traverser->traverse($this->ast);
	}

	public static function getEvaluator()
	{
		return self::$evaluator;
	}

	/**
	 * Get the variables currently defined in the VM.
	 *
	 * @return array Variables defined in the VM. Keys are variable names, values are variable values.
	 */
	public function getVars()
	{
		return VariableStore::getVariables(); //$this->vars;
	}

	/**
	 * Get the constant behaviour settings.
	 * @deprecated Use Harness::getConstantsConfiguration() instead.
	 *
	 * @return array Constant behaviour settings.
	 */
	public function getConstantBehaviour()
	{
		return Harness::getConstantsConfiguration()->behaviour ?? [];
	}

	/**
	 * Set the constant behaviour settings. Dangerous: only call when bootstrapping a context-isolated language runner.
	 * @deprecated Use ConstantsConfiguration methods instead.
	 * @param array $constantSettings The constant settings array.
	 * @return array Constant behaviour settings.
	 */
	public function setConstantSettings(array $constantSettings)
	{
		Harness::getConstantsConfiguration()->setConstantBehaviour($constantSettings['behaviour']);
		Harness::getConstantsConfiguration()->setAllowedConstants($constantSettings['allow']);
		Harness::getConstantsConfiguration()->setDisallowedConstants($constantSettings['disallow']);
	}
}
