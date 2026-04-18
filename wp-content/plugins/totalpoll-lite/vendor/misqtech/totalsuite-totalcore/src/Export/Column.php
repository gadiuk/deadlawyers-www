<?php

namespace TotalPollVendors\TotalCore\Export;

/**
 * Class Column
 * @package TotalPollVendors\TotalCore\Export
 */
abstract class Column {
	/**
	 * @var string $title
	 */
	public $title;
	/**
	 * @var string $width
	 */
	public $width;

	/**
	 * Column constructor.
	 *
	 * @param      $title
	 * @param null $width
	 */
	public function __construct( $title, $width = null ) {
		$this->title = $title;
		$this->width = $width;
	}
}
