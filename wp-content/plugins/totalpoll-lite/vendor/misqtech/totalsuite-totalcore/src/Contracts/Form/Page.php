<?php

namespace TotalPollVendors\TotalCore\Contracts\Form;

use TotalPollVendors\TotalCore\Contracts\Helpers\Arrayable;

/**
 * Interface Page
 * @package TotalPollVendors\TotalCore\Contracts\Form
 */
interface Page extends \ArrayAccess, \Iterator, Arrayable, \Countable {
	/**
	 * @return mixed
	 */
	public function validate();

	/**
	 * @return mixed
	 */
	public function errors();

	/**
	 * @return mixed
	 */
	public function render();
}