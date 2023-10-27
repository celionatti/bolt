<?php

declare(strict_types=1);

/**
 * ==========================================
 * ================         =================
 * Controller Class
 * ================         =================
 * ==========================================
 */

namespace celionatti\Bolt;

use celionatti\Bolt\View\BoltView;
use celionatti\Bolt\Middleware\Middleware;

class Controller
{
    public BoltView $view;
    public string $action = '';
    protected $currentUser;

    /**
     * @var \celionatti\Bolt\Middleware\Middleware[]
     */
    protected array $middlewares = [];

    public function __construct()
    {
        $this->onConstruct();
        $this->view = new BoltView('', false, false);
        $this->view->setLayout("default");
    }

    public function setCurrentUser($user)
    {
        // Allow the developer to set the current user
        $this->currentUser = $user;
    }

    public function registerMiddleware(Middleware $middleware)
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * @return \celionatti\Bolt\Middleware\Middleware[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function onConstruct(): void
    {
    }
}
