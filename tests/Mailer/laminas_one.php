<?php
require 'vendor/autoload.php';

use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;
use Laminas\Mail\Message;

// SMTP configuration
$smtpConfig = [
    'name' => 'your_smtp_server',
    'host' => 'your_smtp_host',
    'port' => 587, // Replace with your SMTP server port
    'connection_class' => 'login',
    'connection_config' => [
        'username' => 'your_email@example.com',
        'password' => 'your_password',
        'ssl' => 'tls', // Use 'ssl' for SSL encryption or 'tls' for TLS encryption
    ],
];

// Create an instance of the SMTP transport
$transport = new SmtpTransport(new SmtpOptions($smtpConfig));

// Create a message
$message = new Message();
$message->addFrom('your_email@example.com')
        ->addTo('recipient@example.com')
        ->setSubject('Email Subject')
        ->setBody('This is the email content.');

// Send the email
$transport->send($message);

echo 'Email sent successfully.';
