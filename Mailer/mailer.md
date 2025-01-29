// Configuration example
$mailConfig = [
    'driver' => 'smtp',
    'host' => 'smtp.example.com',
    'port' => 587,
    'username' => 'user@example.com',
    'password' => 'secret',
    'encryption' => 'tls',
    'from' => [
        'address' => 'noreply@example.com',
        'name' => 'My App'
    ],
    'pretend' => false // Set to true to enable pretend mode
];

// Initialize mailer
$mailer = new MailService($mailConfig);
$mailer->setViewsPath(__DIR__ . '/views/emails');

// Send email
$mailer->to('recipient@example.com')
       ->subject('Test Email')
       ->view('welcome', ['name' => 'John Doe'])
       ->send();


===============================================================

