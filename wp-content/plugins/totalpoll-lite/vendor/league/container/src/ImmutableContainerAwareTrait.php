<?php

namespace TotalPollVendors\League\Container;

use TotalPollVendors\Interop\Container\ContainerInterface as InteropContainerInterface;

trait ImmutableContainerAwareTrait
{
    /**
     * @var \TotalPollVendors\Interop\Container\ContainerInterface
     */
    protected $container;

    /**
     * Set a container.
     *
     * @param  \TotalPollVendors\Interop\Container\ContainerInterface $container
     * @return $this
     */
    public function setContainer(InteropContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the container.
     *
     * @return \TotalPollVendors\League\Container\ImmutableContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
