<?php

namespace TotalPollVendors\TotalCore\Contracts\Admin;

/**
 * Class Page
 * @package TotalPollVendors\TotalCore\Admin\Pages
 */
interface Page {
	/**
	 * Enqueue assets.
	 *
	 * @return mixed
	 */
	public function assets();

	/**
	 * Save page content or settings.
	 *
	 * @return mixed
	 */
	public function save();

	/**
	 * Render page.
	 *
	 * @return mixed
	 */
	public function render();
}