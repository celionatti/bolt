<?php

declare(strict_types=1);

/**
 * =====================================
 * Bolt - NotFoundException ============
 * =====================================
 */

namespace celionatti\Bolt\Container\Exception;

use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends \Exception implements NotFoundExceptionInterface
{

}