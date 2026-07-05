<?php
/**
 * TaskNest - Login Page
 */

require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireGuest($auth);

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !$auth->verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Security token invalid. Please try again.';
    }
    
    if (empty($errors)) {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember_me = isset($_POST['remember_me']);
        
        $result = $auth->login($email, $password, $remember_me);
        
        if ($result['success']) {
            // Check if 2FA is enabled for this user
            $userId = $auth->getUserId();
            if ($userId && $auth->isTwoFactorEnabled($userId)) {
                // Don't fully log in yet - set 2FA pending
                $_SESSION['user_id'] = null;
                unset($_SESSION['user_id']);
                $auth->setTwoFactorPending($userId);
                redirect(SITE_URL . '/2fa-verify.php');
            }
            redirect(SITE_URL . '/dashboard.php');
        } else {
            $errors = $result['errors'] ?? [];
        }
    }
}

$page_title = 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login to <?php echo SITE_NAME; ?>">
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
                    <p>Sign in to your account</p>
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
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="••••••••" 
                            required
                            class="form-input"
                        >
                    </div>
                    
                    <div class="form-group checkbox">
                        <input 
                            type="checkbox" 
                            id="remember_me" 
                            name="remember_me"
                            class="form-checkbox"
                        >
                        <label for="remember_me">Remember me for 7 days</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                </form>
                
                <div class="auth-footer">
                    <p>
                        Don't have an account? 
                        <a href="<?php echo SITE_URL; ?>/register.php">Create one</a>
                    </p>
                    <p>
                        <a href="<?php echo SITE_URL; ?>/forgot-password.php">Forgot your password?</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/theme.js"></script>
</body>
</html>
