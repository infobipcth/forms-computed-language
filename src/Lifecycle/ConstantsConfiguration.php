<?php

namespace FormsComputedLanguage\Lifecycle;

class ConstantsConfiguration
{
	public ?ConstantsBehaviour $behaviour;
	private ?array $allowedConstants;
	private ?array $disallowedConstants;

	/**
	 * Set the constants blacklist.
	 *
	 * @param array $disallow Constants blacklist.
	 * @return void
	 */
	public function setDisallowedConstants(array $disallow): void
	{
		$this->disallowedConstants = $disallow;
	}

	/**
	 * Set the constants whitelist.
	 *
	 * @param array $allow Constants whitelist.
	 * @return void
	 */
	public function setAllowedConstants(array $allow): void
	{
		$this->allowedConstants = $allow;
	}

	/**
	 * Set to 'whitelist' to only allow whitelisted constants, or to 'blacklist' to allow all but blacklisted.
	 *
	 * @param string|ConstantsBehaviour $type 'whitelist' or 'blacklist'.
	 * @return void
	 */
	public function setConstantBehaviour(string|ConstantsBehaviour $type): void
	{
		$this->behaviour = is_string($type) ? ConstantsBehaviour::from($type) : $type;
	}

	/**
	 * Given a constant name, grants or denies access.
	 * Note that if behaviour is not set, all constants are always available!
	 *
	 * @param string $name Constant name.
	 * @return boolean true if settings allow access, false otherwise.
	 */
	public function canAccessConstant(string $name): bool
	{
		if (!isset($this->behaviour)) {
			return true;
		}

		if ($this->behaviour === ConstantsBehaviour::Allow) {
			return in_array($name, $this->allowedConstants, true);
		} else {
			return !in_array($name, $this->disallowedConstants, true);
		}
	}
}
