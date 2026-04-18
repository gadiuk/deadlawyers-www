<?php

namespace TotalPollVendors\TotalCore\Contracts\Http;

use TotalPollVendors\TotalCore\Contracts\Helpers\Arrayable;

/**
 * Interface File
 * @package TotalPollVendors\TotalCore\Contracts\Http
 */
interface File extends \Countable, Arrayable, \JsonSerializable {
	/**
	 * @return mixed
	 */
	public function getClientExtension();

	/**
	 * @return string
	 */
	public function getExtension();

	/**
	 * @return string
	 */
	public function getMimeType();

	/**
	 * @param $target
	 *
	 * @return bool|File
	 */
	public function move( $target );

	/**
	 * @return null
	 */
	public function getClientFilename();
}