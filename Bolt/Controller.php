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

class Controller
{
    public BoltView $view;

    public function __construct()
    {
        $this->view = new BoltView('', true, false);
        $this->view->setLayout("default");

        $this->onConstruct();
    }

    public function onConstruct(): void
    {
    }
}
