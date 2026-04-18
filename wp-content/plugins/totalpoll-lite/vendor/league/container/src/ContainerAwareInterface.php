<?php

namespace TotalPollVendors\League\Container;

interface ContainerAwareInterface
{
    /**
     * Set a container
     *
     * @param \TotalPollVendors\League\Container\ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container);

    /**
     * Get the container
     *
     * @return \TotalPollVendors\League\Container\ContainerInterface
     */
    public function getContainer();
}
