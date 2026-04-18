<?php

namespace TotalPollVendors\League\Container\Exception;

use TotalPollVendors\Interop\Container\Exception\NotFoundException as NotFoundExceptionInterface;
use InvalidArgumentException;

class NotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface
{
}
