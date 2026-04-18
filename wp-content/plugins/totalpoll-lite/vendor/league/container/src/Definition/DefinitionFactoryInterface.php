<?php

namespace TotalPollVendors\League\Container\Definition;

use TotalPollVendors\League\Container\ImmutableContainerAwareInterface;

interface DefinitionFactoryInterface extends ImmutableContainerAwareInterface
{
    /**
     * Return a definition based on type of concrete.
     *
     * @param  string $alias
     * @param  mixed  $concrete
     * @return mixed
     */
    public function getDefinition($alias, $concrete);
}
