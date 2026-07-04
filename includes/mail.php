<?php
/**
 * TaskNest - Email Helper
 * Sends emails using PHP's native mail() with HTML support.
 * In DEBUG mode, logs emails and shows them on screen.
 */

/**
 * Send an email.
 *
 * @param string $to      Recipient email address
 * @param string $subject Email subject
 * @param string $html    HTML body
 * @param string $plain   Plain text fallback
 * @return array ['success' => bool, 'message' => string]
 */
function sendEmail($to, $subject, $html, $plain = '') {
    if (empty($plain)) {
        $plain = strip_tags($html);
    }

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . MAIL_FROM_EMAIL . "\r\n";
    $headers .= "X-Mailer: TaskNest/" . SITE_NAME . "\r\n";

    $result = @mail($to, $subject, $html, $headers);

    if ($result) {
        logMessage("Email sent to $to: $subject", 'EMAIL');
        return ['success' => true, 'message' => 'Email sent successfully'];
    }

    // Mail failed - log for debugging
    $error = error_get_last();
    $errorMsg = $error['message'] ?? 'PHP mail() failed. Check Mercury mail server is running.';
    logMessage("Email FAILED to $to: $subject | Error: $errorMsg", 'EMAIL_ERROR');

    // Always store for debug display so users can get the link
    if (!isset($_SESSION['debug_emails'])) {
        $_SESSION['debug_emails'] = [];
    }
    $_SESSION['debug_emails'][] = [
        'to'      => $to,
        'subject' => $subject,
        'html'    => $html,
        'plain'   => $plain,
        'time'    => date('Y-m-d H:i:s'),
    ];

    return ['success' => false, 'message' => 'Failed to send email. ' . (DEBUG ? $errorMsg : '')];
}

/**
 * Send a password reset email.
 *
 * @param string $to         User email
 * @param string $resetLink  Full reset URL with token
 * @return array
 */
function sendPasswordResetEmail($to, $resetLink) {
    $subject = SITE_NAME . " - Password Reset Request";

    $html = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9;padding:40px 20px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08);">
  <tr>
    <td style="background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:32px;text-align:center;">
      <h1 style="color:#ffffff;margin:0;font-size:24px;">' . SITE_NAME . '</h1>
    </td>
  </tr>
  <tr>
    <td style="padding:40px 32px;">
      <h2 style="color:#1f2937;margin:0 0 16px;font-size:20px;">Password Reset Request</h2>
      <p style="color:#4b5563;margin:0 0 24px;line-height:1.6;">
        We received a request to reset the password for your account associated with <strong>' . htmlspecialchars($to) . '</strong>.
      </p>
      <p style="color:#4b5563;margin:0 0 24px;line-height:1.6;">
        Click the button below to set a new password. This link will expire in <strong>1 hour</strong>.
      </p>
      <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
        <tr>
          <td style="background:linear-gradient(135deg,#6366f1,#8b5cf6);border-radius:8px;">
            <a href="' . htmlspecialchars($resetLink) . '" style="display:inline-block;padding:14px 32px;color:#ffffff;text-decoration:none;font-weight:600;font-size:16px;">
              Reset My Password
            </a>
          </td>
        </tr>
      </table>
      <p style="color:#9ca3af;margin:32px 0 0;font-size:13px;line-height:1.6;">
        If the button doesn\'t work, copy and paste this link into your browser:<br>
        <a href="' . htmlspecialchars($resetLink) . '" style="color:#6366f1;word-break:break-all;">' . htmlspecialchars($resetLink) . '</a>
      </p>
    </td>
  </tr>
  <tr>
    <td style="background:#f9fafb;padding:24px 32px;text-align:center;border-top:1px solid #e5e7eb;">
      <p style="color:#9ca3af;margin:0;font-size:12px;">
        If you didn\'t request this, you can safely ignore this email. Your password will remain unchanged.
      </p>
    </td>
  </tr>
</table>
</td></tr>
</table>
</body>
</html>';

    $plain = "Password Reset Request\n\n"
           . "We received a request to reset the password for your account ($to).\n\n"
           . "Visit this link to set a new password (expires in 1 hour):\n"
           . $resetLink . "\n\n"
           . "If you didn't request this, ignore this email.";

    $result = sendEmail($to, $subject, $html, $plain);

    // Store reset link for debug display
    if (isset($_SESSION['debug_emails'])) {
        $last = count($_SESSION['debug_emails']) - 1;
        if ($last >= 0) {
            $_SESSION['debug_emails'][$last]['reset_link'] = $resetLink;
        }
    }

    return $result;
}
