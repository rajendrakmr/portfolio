SETUP — Contact Form with SMTP
================================

Files:
- index.php            → the website, with a working contact form
- contact-handler.php   → receives the form POST and sends the email
- SmtpMailer.php        → small SMTP client (no external libraries needed)
- config.php            → your SMTP credentials — EDIT THIS FILE

1. Upload all four files to the same folder on any PHP host (shared hosting,
   VPS, etc. — just needs PHP with the openssl extension, which almost all do).

2. Open config.php and fill in:
   - smtp_host / smtp_port / smtp_secure  → from your email provider
   - smtp_username / smtp_password        → your SMTP login
       Gmail: use an "App Password", not your normal Gmail password
       (Google Account → Security → 2-Step Verification → App Passwords)
   - to_email    → where form submissions should land
   - from_email  → usually must match smtp_username for the provider to accept it

3. Visit index.php in a browser, fill the form, submit — you should get
   an email at "to_email", and the sender's address is set as Reply-To so you
   can hit reply directly.

4. If it fails, check your host's PHP error log — SmtpMailer throws a clear
   message (e.g. wrong password, wrong port, blocked outbound SMTP — some
   hosts block port 25/587 outbound, in which case ask your host to enable it
   or use their own SMTP relay).

Note: this uses raw SMTP over sockets — no Composer, no PHPMailer, nothing to
install. Just PHP + these four files.
##
