<?php

namespace TotalPollVendors\TotalCore\Modules;

/**
 * Class Extension
 * @package TotalPollVendors\TotalCore\Modules
 */
abstract class Extension extends Module {
	/**
	 * Run the extension.
	 *
	 * @return mixed
	 */
	abstract public function run();
}