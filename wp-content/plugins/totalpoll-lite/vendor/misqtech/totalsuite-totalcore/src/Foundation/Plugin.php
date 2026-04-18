<?php

namespace TotalPollVendors\TotalCore\Foundation;

use TotalPollVendors\League\Container\ContainerInterface as ContainerContract;
use TotalPollVendors\TotalCore\Application;
use TotalPollVendors\TotalCore\Contracts\Foundation\Plugin as PluginContract;

/**
 * Class Plugin
 * @package TotalPollVendors\TotalCore\Foundation
 */
abstract class Plugin implements PluginContract {
	/**
	 * @var Application $application
	 */
	protected $application;
	/**
	 * @var ContainerContract $container
	 */
	protected $container;

	/**
	 * Get application.
	 *
	 * @return Application
	 */
	final public function getApplication() {
		return $this->application;
	}

	/**
	 * Set application.
	 *
	 * @param Application $application
	 */
	final public function setApplication( Application $application ) {
		$this->application = $application;
		$this->container   = $application->container();
	}

    /**
     * @return array
     */
	public function objectsCount() {
	    return [];
    }
}