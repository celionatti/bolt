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
use Bolt\Bolt\Mailer\BoltMailer;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
use Bolt\Bolt\Authentication\BoltAuthentication;

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
        // $mailer = new BoltMailer();

        // try {
        //     $mailer->sendEmail(["femlovazglobalconceptslimited@gmail.com"], "hello just testing", "Testing Mailer function", "Celio Bolt Framework", "amisuusman@gmail.com", "Amisu usman");
        // } catch (\Bolt\Bolt\BoltException\BoltException $e) {
        //     throw $e;
        // }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = MAILER_EMAIL;
            $mail->Password = MAILER_PASSWORD;
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('amisuusman@gmail.com', 'Your Name');
            $mail->addAddress('amisuusman@gmail.com', 'Recipient Name');

            $mail->isHTML(true);
            $mail->Subject = 'Subject of the Email';
            $mail->Body = 'This is the HTML message body';

            $mail->send();
            echo 'Email has been sent.';
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
        }
    }
}
