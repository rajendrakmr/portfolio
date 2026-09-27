<?php
// Fill in your real SMTP provider details below.
// Gmail example: host smtp.gmail.com, port 587, secure 'tls' (use an App Password, not your normal password)
// Zoho example:  host smtp.zoho.com,  port 587, secure 'tls'
// Any provider that gives you host/port/username/password will work.

return [
    'smtp_host'     => 'smtp.gmail.com',
    'smtp_port'     => 587,
    'smtp_secure'   => 'tls',        // 'tls' for port 587, 'ssl' for port 465
    'smtp_username' => 'your-email@gmail.com',
    'smtp_password' => 'your-app-password',

    'to_email'      => 'hello@uptimelabs.io', // where form messages get delivered
    'from_email'    => 'your-email@gmail.com', // must usually match smtp_username
    'from_name'     => 'Uptime Labs Website',
];
