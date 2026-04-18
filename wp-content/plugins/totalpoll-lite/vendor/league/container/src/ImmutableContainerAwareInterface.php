<?php

namespace TotalPollVendors\League\Container;

use TotalPollVendors\Interop\Container\ContainerInterface as InteropContainerInterface;

interface ImmutableContainerAwareInterface
{
    /**
     * Set a container
     *
     * @param \TotalPollVendors\Interop\Container\ContainerInterface $container
     */
    public function setContainer(InteropContainerInterface $container);

    /**
     * Get the container
     *
     * @return \TotalPollVendors\League\Container\ImmutableContainerInterface
     */
    public function getContainer();
}
