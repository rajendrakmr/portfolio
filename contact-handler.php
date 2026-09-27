<?php
require __DIR__ . '/SmtpMailer.php';
$config = require __DIR__ . '/config.php';

function backToForm(string $status): void
{
    header('Location: index.php?status=' . $status . '#contact');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    backToForm('error');
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    backToForm('invalid');
}

$subject = "New contact form message from $name";
$body = "You've received a new message from the website contact form.\n\n"
      . "Name: $name\n"
      . "Email: $email\n\n"
      . "Message:\n$message\n";

try {
    $mailer = new SmtpMailer(
        $config['smtp_host'],
        $config['smtp_port'],
        $config['smtp_username'],
        $config['smtp_password'],
        $config['smtp_secure']
    );

    $mailer->send(
        $config['from_email'],
        $config['from_name'],
        $config['to_email'],
        $subject,
        $body,
        $email // replies from the team go straight to the visitor
    );

    backToForm('success');
} catch (Throwable $e) {
    error_log('Contact form SMTP error: ' . $e->getMessage());
    backToForm('error');
}
