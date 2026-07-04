<?php
/**
 * TaskNest - Forgot Password Page
 */

require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireGuest($auth);

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !$auth->verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Security token invalid. Please try again.';
    }
    
    if (empty($errors)) {
        $email = sanitize($_POST['email'] ?? '');
        
        if (!isValidEmail($email)) {
            $errors[] = 'Please enter a valid email address';
        } else {
            $result = $auth->requestPasswordReset($email);
            // Always show success message for security
            $message = 'If an account exists for this email, a password reset link has been sent.';
        }
    }
}

$page_title = 'Forgot Password';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Reset your <?php echo SITE_NAME; ?> password">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/assets/images/logo-dark.png">
    
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
                    <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="<?php echo SITE_NAME; ?> Logo" class="auth-logo">
                    <h1><?php echo SITE_NAME; ?></h1>
                    <p>Reset your password</p>
                </div>
                
                <?php if ($message): ?>
                <div class="alert alert-success">
                    <p><?php echo htmlspecialchars($message); ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($_SESSION['debug_emails'])): ?>
                <div style="background:#fef3c7;border:1px solid #f59e0b;border-radius:8px;padding:16px;margin-bottom:20px;">
                    <p style="font-weight:600;color:#92400e;margin:0 0 8px;">Email could not be sent (local server). Use this link instead:</p>
                    <?php foreach (array_reverse($_SESSION['debug_emails']) as $email): ?>
                    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:6px;padding:12px;margin-bottom:8px;">
                        <p style="margin:0 0 4px;font-size:13px;color:#6b7280;"><strong>To:</strong> <?php echo htmlspecialchars($email['to']); ?></p>
                        <p style="margin:0 0 8px;font-size:13px;"><strong>Reset Link:</strong></p>
                        <?php if (!empty($email['reset_link'])): ?>
                        <a href="<?php echo htmlspecialchars($email['reset_link']); ?>" style="display:inline-block;background:#6366f1;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:600;margin-bottom:8px;">Click to Reset Password</a>
                        <p style="margin:8px 0 0;font-size:12px;color:#6b7280;word-break:break-all;">Or copy: <?php echo htmlspecialchars($email['reset_link']); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <?php unset($_SESSION['debug_emails']); ?>
                </div>
                <?php endif; ?>
                
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
                        <label for="email">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="your@email.com" 
                            required
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            class="form-input"
                        >
                        <small>We'll send a password reset link to this email</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
                </form>
                
                <div class="auth-footer">
                    <p>
                        Remember your password? 
                        <a href="<?php echo SITE_URL; ?>/login.php">Sign in</a>
                    </p>
                    <p>
                        Don't have an account? 
                        <a href="<?php echo SITE_URL; ?>/register.php">Create one</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/theme.js"></script>
</body>
</html>
