<?php

declare(strict_types=1);

/**
 * ==========================================
 * ================         =================
 * Bolt Router
 * ================         =================
 * ==========================================
 */

namespace Bolt\Bolt\Router;

use Bolt\Bolt\Router\Router;

class BoltRouter extends Router
{   
    public function __construct(Router $parentRouter)
    {
        $this->request = $parentRouter->request;
        $this->response = $parentRouter->response;
    }
}
