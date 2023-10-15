<?php

declare(strict_types=1);

/**
 * ==========================================
 * ================         =================
 * Controller Class
 * ================         =================
 * ==========================================
 */

namespace Bolt\Bolt;

use Bolt\Bolt\View\BoltView;
use Bolt\Bolt\Middleware\Middleware;

class Controller
{
    public BoltView $view;
    public string $action = '';

    /**
     * @var \Bolt\Bolt\Middleware\Middleware[]
     */
    protected array $middlewares = [];

    public function __construct()
    {
        $this->onConstruct();
        $this->view = new BoltView('', false, false);
        $this->view->setLayout("default");
    }

    public function registerMiddleware(Middleware $middleware)
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * @return \Bolt\Bolt\Middleware\Middleware[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function onConstruct(): void
    {
    }
}
