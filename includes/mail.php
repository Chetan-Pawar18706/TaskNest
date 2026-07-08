<?php
/**
 * TaskNest - Email Helper
 * PHPMailer based — Gmail SMTP
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/functions.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($to, $subject, $html, $plain = '') {
    if (empty($plain)) $plain = strip_tags($html);

    if (defined('SMTP_HOST') && SMTP_HOST) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = SMTP_PORT;
            $mail->CharSet    = 'UTF-8';
            $mail->Encoding   = 'base64';

            $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            $mail->addAddress($to);
            $mail->addReplyTo(MAIL_FROM_EMAIL, MAIL_FROM_NAME);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = $plain;

            if (!$mail->send()) {
                logMessage("[PHPMailer] Failed to $to: {$mail->ErrorInfo}", 'EMAIL_ERROR');
                return ['success' => false, 'message' => $mail->ErrorInfo];
            }
            logMessage("[PHPMailer] Email sent to $to: $subject", 'EMAIL');
            return ['success' => true, 'message' => 'Email sent'];
        } catch (Exception $e) {
            logMessage("[PHPMailer] Failed to $to: {$mail->ErrorInfo}", 'EMAIL_ERROR');
            return ['success' => false, 'message' => $mail->ErrorInfo];
        }
    }

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM_EMAIL . ">\r\n";

    if (@mail($to, $subject, $html, $headers)) {
        logMessage("[mail()] Email sent to $to: $subject", 'EMAIL');
        return ['success' => true, 'message' => 'Email sent'];
    }

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
