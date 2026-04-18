<?php

namespace TotalPollVendors\TotalCore\Contracts\Form;

use TotalPollVendors\TotalCore\Contracts\Helpers\Arrayable;

/**
 * Interface Form
 * @package TotalPollVendors\TotalCore\Contracts\Form
 */
interface Form extends \ArrayAccess, \Iterator, Arrayable, \Countable {
	public function validate();

	public function isValidated();

	public function errors();

	public function render();

	public function getFormElement();

	public function getSubmitButtonElement();
}