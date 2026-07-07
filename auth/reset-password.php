<?php
/**
 * TaskNest - Reset Password Page
 */

require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireGuest($auth);

$token = sanitize($_GET['token'] ?? '');
$errors = [];
$message = '';

if (empty($token)) {
    $errors[] = 'Invalid or missing password reset token';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !$auth->verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Security token invalid. Please try again.';
    }
    
    if (empty($errors)) {
        $token = sanitize($_POST['token'] ?? '');
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($token)) {
            $errors[] = 'Invalid password reset token';
        } else {
            $result = $auth->resetPassword($token, $new_password, $confirm_password);
            
            if ($result['success']) {
                redirect(SITE_URL . '/auth/login.php?message=' . urlencode($result['message']));
            } else {
                $errors = $result['errors'] ?? [];
            }
        }
    }
}

$page_title = 'Reset Password';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Reset your <?php echo SITE_NAME; ?> password">
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
                    <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="<?php echo SITE_NAME; ?> Logo" class="auth-logo">
                    <h1><?php echo SITE_NAME; ?></h1>
                    <p>Create a new password</p>
                </div>
                
                <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <?php if (empty($errors) && !empty($token)): ?>
                <form method="POST" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($auth->generateCsrfToken()); ?>">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password" 
                            placeholder="••••••••" 
                            required
                            class="form-input"
                        >
                        <small>Minimum 8 characters, must include uppercase and number</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="••••••••" 
                            required
                            class="form-input"
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                </form>
                
                <div class="auth-footer">
                    <p>
                        Remember your password? 
                        <a href="<?php echo SITE_URL; ?>/auth/login.php">Sign in</a>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/theme.js"></script>
</body>
</html>
