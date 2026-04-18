<?php

namespace TotalPoll\Contracts\Modules;

use TotalPollVendors\TotalCore\Contracts\Modules\Repository as RepositoryContract;

/**
 * Interface RepositoryService
 * @package TotalPoll\Contracts\Modules
 */
interface Repository extends RepositoryContract {
	/**
	 * Get defaults.
	 *
	 * @param $moduleId
	 *
	 * @return mixed
	 */
	public function getDefaults( $moduleId );

	/**
	 * Get preview.
	 *
	 * @param $moduleId
	 *
	 * @return mixed
	 */
	public function getPreview( $moduleId );

	/**
	 * Get settings.
	 *
	 * @param $moduleId
	 *
	 * @return mixed
	 */
	public function getSettings( $moduleId );

	/**
	 * Get stylesheet.
	 *
	 * @param $moduleId
	 *
	 * @return mixed
	 */
	public function getStylesheet( $moduleId );
}