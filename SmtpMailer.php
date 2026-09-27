<?php
/**
 * Minimal dependency-free SMTP mailer.
 * Supports STARTTLS (port 587) and implicit SSL (port 465) with AUTH LOGIN.
 * No external libraries required — works on any host with PHP's openssl extension enabled.
 */
class SmtpMailer
{
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private string $secure; // 'tls' or 'ssl'
    private int $timeout;

    public function __construct(string $host, int $port, string $username, string $password, string $secure = 'tls', int $timeout = 15)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->secure = $secure;
        $this->timeout = $timeout;
    }

    public function send(string $from, string $fromName, string $to, string $subject, string $body, ?string $replyTo = null): bool
    {
        $errno = 0;
        $errstr = '';
        $transport = ($this->secure === 'ssl') ? 'ssl://' . $this->host : $this->host;

        $socket = @stream_socket_client($transport . ':' . $this->port, $errno, $errstr, $this->timeout);
        if (!$socket) {
            throw new Exception("Could not connect to SMTP server: $errstr ($errno)");
        }

        $this->expect($socket, 220);
        $this->command($socket, 'EHLO ' . $this->localName(), 250);

        if ($this->secure === 'tls') {
            $this->command($socket, 'STARTTLS', 220);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception('STARTTLS negotiation failed');
            }
            $this->command($socket, 'EHLO ' . $this->localName(), 250);
        }

        $this->command($socket, 'AUTH LOGIN', 334);
        $this->command($socket, base64_encode($this->username), 334);
        $this->command($socket, base64_encode($this->password), 235);

        $this->command($socket, "MAIL FROM:<$from>", 250);
        $this->command($socket, "RCPT TO:<$to>", 250);
        $this->command($socket, 'DATA', 354);

        $headers = [];
        $headers[] = 'From: ' . $this->encodeHeader($fromName) . " <$from>";
        $headers[] = "To: <$to>";
        if ($replyTo) {
            $headers[] = "Reply-To: <$replyTo>";
        }
        $headers[] = 'Subject: ' . $this->encodeHeader($subject);
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $headers[] = 'Date: ' . date('r');

        // Escape lines that start with a lone "." per SMTP DATA rules
        $escapedBody = preg_replace('/^\./m', '..', $body);

        $message = implode("\r\n", $headers) . "\r\n\r\n" . $escapedBody . "\r\n.";
        $this->command($socket, $message, 250);

        $this->command($socket, 'QUIT', 221);
        fclose($socket);

        return true;
    }

    private function localName(): string
    {
        return $_SERVER['SERVER_NAME'] ?? 'localhost';
    }

    private function encodeHeader(string $text): string
    {
        return '=?UTF-8?B?' . base64_encode($text) . '?=';
    }

    private function command($socket, string $data, int $expectedCode): string
    {
        fwrite($socket, $data . "\r\n");
        return $this->expect($socket, $expectedCode);
    }

    private function expect($socket, int $expectedCode): string
    {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            // Multi-line SMTP responses use "250-" until the final "250 "
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        $code = (int) substr($response, 0, 3);
        if ($code !== $expectedCode) {
            throw new Exception("Unexpected SMTP response (expected $expectedCode): $response");
        }
        return $response;
    }
}
