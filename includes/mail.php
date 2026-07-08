<?php
/**
 * TaskNest - Email Helper
 * Custom SMTP class — works on InfinityFree (no PHPMailer, no vendor needed)
 */

class TaskNestMailer {
    private $host, $port, $username, $password, $from_email, $from_name;
    private $errno = 0, $errstr = '';

    public function __construct($host, $port, $username, $password, $from_email, $from_name) {
        $this->host = $host;
        $this->port = (int)$port;
        $this->username = $username;
        $this->password = $password;
        $this->from_email = $from_email;
        $this->from_name = $from_name;
    }

    public function send($to, $subject, $html, $plain = '') {
        if (empty($plain)) $plain = strip_tags($html);

        $fp = @fsockopen($this->host, $this->port, $this->errno, $this->errstr, 10);
        if (!$fp) {
            return ['success' => false, 'message' => "SMTP connection failed: {$this->errstr}"];
        }

        $response = $this->readResponse($fp);
        $this->sendCommand($fp, "EHLO tasknest");
        $this->readResponse($fp);

        // Try STARTTLS
        $this->sendCommand($fp, "STARTTLS");
        $tlsResponse = $this->readResponse($fp);
        if (strpos($tlsResponse, '220') === 0) {
            stream_context_set_option($fp, 'ssl', 'verify_peer', false);
            stream_context_set_option($fp, 'ssl', 'verify_peer_name', false);
            $crypto = stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT);
            if (!$crypto) {
                fclose($fp);
                return ['success' => false, 'message' => 'STARTTLS failed'];
            }
            $this->sendCommand($fp, "EHLO tasknest");
            $this->readResponse($fp);
        }

        // Auth
        $this->sendCommand($fp, "AUTH LOGIN");
        $this->readResponse($fp);
        $this->sendCommand($fp, base64_encode($this->username));
        $this->readResponse($fp);
        $this->sendCommand($fp, base64_encode($this->password));
        $authResponse = $this->readResponse($fp);
        if (strpos($authResponse, '235') !== 0) {
            fclose($fp);
            return ['success' => false, 'message' => 'SMTP auth failed: ' . trim($authResponse)];
        }

        // Send email
        $this->sendCommand($fp, "MAIL FROM:<{$this->from_email}>");
        $this->readResponse($fp);
        $this->sendCommand($fp, "RCPT TO:<{$to}>");
        $this->readResponse($fp);
        $this->sendCommand($fp, "DATA");
        $this->readResponse($fp);

        $headers  = "From: =?UTF-8?B?" . base64_encode($this->from_name) . "?= <{$this->from_email}>\r\n";
        $headers .= "To: <{$to}>\r\n";
        $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Date: " . date('r') . "\r\n";
        $headers .= "\r\n";

        $body = $headers . $html . "\r\n.\r\n";
        $this->sendCommand($fp, $body);
        $dataResponse = $this->readResponse($fp);

        $this->sendCommand($fp, "QUIT");
        fclose($fp);

        if (strpos($dataResponse, '250') === 0) {
            return ['success' => true, 'message' => 'Email sent'];
        }
        return ['success' => false, 'message' => 'DATA failed: ' . trim($dataResponse)];
    }

    private function sendCommand($fp, $cmd) {
        fwrite($fp, $cmd . "\r\n");
    }

    private function readResponse($fp) {
        $response = '';
        while (true) {
            $line = fgets($fp, 512);
            if ($line === false) break;
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $response;
    }
}

/**
 * Send email — SMTP try, fail ho to mail(), dono fail ho to debug.
 */
function sendEmail($to, $subject, $html, $plain = '') {
    if (empty($plain)) $plain = strip_tags($html);

    // Try SMTP
    if (defined('SMTP_HOST') && SMTP_HOST) {
        $mailer = new TaskNestMailer(SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        $result = $mailer->send($to, $subject, $html, $plain);
        if ($result['success']) {
            logMessage("[SMTP] Email sent to $to: $subject", 'EMAIL');
            return $result;
        }
        logMessage("[SMTP] Failed to $to: {$result['message']}", 'EMAIL_ERROR');
    }

    // Fallback: PHP mail()
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM_EMAIL . ">\r\n";

    if (@mail($to, $subject, $html, $headers)) {
        logMessage("[mail()] Email sent to $to: $subject", 'EMAIL');
        return ['success' => true, 'message' => 'Email sent'];
    }

    // Both failed — store for debug
    $error = error_get_last();
    $errorMsg = $error['message'] ?? 'All methods failed';
    logMessage("[ALL FAILED] $to: $subject | $errorMsg", 'EMAIL_ERROR');
    storeDebugEmail($to, $subject, $html, $plain, $errorMsg);
    return ['success' => false, 'message' => $errorMsg];
}

function storeDebugEmail($to, $subject, $html, $plain, $error = '') {
    if (!isset($_SESSION['debug_emails'])) $_SESSION['debug_emails'] = [];
    $_SESSION['debug_emails'][] = [
        'to' => $to, 'subject' => $subject, 'html' => $html,
        'plain' => $plain, 'time' => date('Y-m-d H:i:s'), 'error' => $error,
    ];
}

function sendPasswordResetEmail($to, $resetLink) {
    $subject = SITE_NAME . " - Password Reset Request";
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:system-ui,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9;padding:40px 20px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08);">
  <tr><td style="background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:32px;text-align:center;">
    <h1 style="color:#fff;margin:0;">' . SITE_NAME . '</h1></td></tr>
  <tr><td style="padding:40px 32px;">
    <h2 style="color:#1f2937;margin:0 0 16px;">Password Reset Request</h2>
    <p style="color:#4b5563;line-height:1.6;">Reset password for <strong>' . htmlspecialchars($to) . '</strong>.</p>
    <p style="color:#4b5563;line-height:1.6;">Link expires in <strong>1 hour</strong>.</p>
    <a href="' . htmlspecialchars($resetLink) . '" style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;text-decoration:none;font-weight:600;border-radius:8px;margin:16px 0;">Reset My Password</a>
    <p style="color:#9ca3af;font-size:13px;margin-top:24px;">Or copy: <a href="' . htmlspecialchars($resetLink) . '" style="color:#6366f1;word-break:break-all;">' . htmlspecialchars($resetLink) . '</a></p>
  </td></tr>
  <tr><td style="background:#f9fafb;padding:24px 32px;text-align:center;border-top:1px solid #e5e7eb;">
    <p style="color:#9ca3af;margin:0;font-size:12px;">Ignore if you didn\'t request this.</p></td></tr>
</table></td></tr></table></body></html>';
    $plain = "Password Reset: $resetLink";

    $result = sendEmail($to, $subject, $html, $plain);
    if (isset($_SESSION['debug_emails'])) {
        $last = count($_SESSION['debug_emails']) - 1;
        if ($last >= 0) $_SESSION['debug_emails'][$last]['reset_link'] = $resetLink;
    }
    return $result;
}
