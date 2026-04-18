<?php

namespace TotalPollVendors\League\Container;

trait ContainerAwareTrait
{
    /**
     * @var \TotalPollVendors\League\Container\ContainerInterface
     */
    protected $container;

    /**
     * Set a container.
     *
     * @param  \TotalPollVendors\League\Container\ContainerInterface $container
     * @return $this
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the container.
     *
     * @return \TotalPollVendors\League\Container\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
