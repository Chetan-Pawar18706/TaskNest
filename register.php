<?php
/**
 * TaskNest - Registration Page
 */

require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireGuest($auth);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !$auth->verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Security token invalid. Please try again.';
    }
    
    if (empty($errors)) {
        $username = sanitize($_POST['username'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $first_name = sanitize($_POST['first_name'] ?? '');
        $last_name = sanitize($_POST['last_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        $result = $auth->register($username, $email, $password, $confirm_password, $first_name, $last_name);
        
        if ($result['success']) {
            redirect(SITE_URL . '/login.php?message=' . urlencode($result['message']));
        } else {
            $errors = $result['errors'] ?? [];
        }
    }
}

$page_title = 'Create Account';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Register for <?php echo SITE_NAME; ?>">
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
                    <p>Create your account to get started</p>
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
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input 
                                type="text" 
                                id="first_name" 
                                name="first_name" 
                                placeholder="John"
                                value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                                class="form-input"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input 
                                type="text" 
                                id="last_name" 
                                name="last_name" 
                                placeholder="Doe"
                                value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                                class="form-input"
                            >
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            placeholder="johndoe" 
                            required
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                            class="form-input"
                        >
                        <small>3-50 characters, letters and numbers only</small>
                    </div>
                    
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
                    
                    <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                </form>
                
                <div class="auth-footer">
                    <p>
                        Already have an account? 
                        <a href="<?php echo SITE_URL; ?>/login.php">Sign in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/theme.js"></script>
</body>
</html>
