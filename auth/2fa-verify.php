<?php
/**
 * TaskNest - Two-Factor Authentication Verification (Login)
 */

require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Must have pending 2FA verification
if (!$auth->isTwoFactorPending()) {
    redirect(SITE_URL . '/auth/login.php');
}

$errors = [];
$userId = $_SESSION['two_factor_user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !$auth->verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Security token invalid. Please try again.';
    } else {
        $code = trim($_POST['code'] ?? '');
        $secret = $auth->getTwoFactorSecret($userId);
        
        if (empty($code)) {
            $errors[] = 'Please enter a verification code';
        } elseif (strlen($code) === 8 && ctype_alnum($code)) {
            // Might be a backup code
            if ($auth->verifyBackupCode($userId, $code)) {
                $auth->completeTwoFactorVerification();
                $auth->logActivity($userId, 'two_factor_backup_used', 'user', $userId, '2FA backup code used for login');
                redirect(SITE_URL . '/dashboard.php');
            } else {
                $errors[] = 'Invalid backup code';
            }
        } elseif ($auth->verifyTotpCode($secret, $code)) {
            $auth->completeTwoFactorVerification();
            $auth->logActivity($userId, 'two_factor_login', 'user', $userId, '2FA verified for login');
            redirect(SITE_URL . '/dashboard.php');
        } else {
            $errors[] = 'Invalid code. Please try again.';
        }
    }
}

$page_title = 'Two-Factor Verification';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/assets/images/favicon.png">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/variables.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/reset.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/styles.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/responsive.css">
</head>
<body>
    <div class="guest-layout">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="tfa-lock-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="40" height="40"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    </div>
                    <h1>Two-Factor Verification</h1>
                    <p>Enter the 6-digit code from your authenticator app</p>
                </div>
                
                <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($auth->generateCsrfToken()); ?>">
                    
                    <div class="form-group">
                        <label for="code">Verification Code</label>
                        <input 
                            type="text" 
                            id="code" 
                            name="code" 
                            placeholder="000000" 
                            maxlength="8"
                            autocomplete="one-time-code"
                            required
                            class="form-input tfa-code-input"
                            autofocus
                        >
                        <small class="form-help">Enter the 6-digit code from your app, or an 8-character backup code</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Verify</button>
                </form>
                
                <div class="auth-footer">
                    <p>
                        <a href="<?php echo SITE_URL; ?>/auth/login.php">Back to Login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.getElementById('code')?.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9a-zA-Z]/g, '');
    });
    </script>
</body>
</html>
