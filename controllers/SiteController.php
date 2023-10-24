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

use Bolt\Bolt\Controller;
use Bolt\Bolt\Authentication\BoltAuthentication;
use Bolt\Bolt\Mailer\LaminasMailer;

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

    public function send()
    {
        $emailSender = new LaminasMailer();
        $from = 'your_email@example.com';
        $to = 'recipient@example.com';
        $subject = 'Email Subject';
        $body = 'This is the email content.';

        if ($emailSender->sendEmail($from, $to, $subject, $body)) {
            echo 'Email sent successfully.';
        } else {
            echo 'Email sending failed.';
        }
    }
}
