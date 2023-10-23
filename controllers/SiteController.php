<?php

declare(strict_types=1);

/**
 * ===================================================
 * =================            ======================
 * SiteController
 * =================            ======================
 * ===================================================
 */

namespace Bolt\controllers;

use Bolt\Bolt\Authentication\BoltAuthentication;
use Bolt\Bolt\Controller;


class SiteController extends Controller
{
    public function onConstruct(): void
    {
        $this->currentUser = BoltAuthentication::currentUser();
    }

    public function welcome()
    {
        $view = [];

        $this->view->render("home", $view);
    }
}
