<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Must be logged in
if (!$auth->isLoggedIn()) {
    redirect(SITE_URL . '/login.php');
}

// Check vault unlock status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['vault_password'] ?? '';
    $stmt = safePrepare($mysqli, 'SELECT password_hash FROM users WHERE id = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row && password_verify($password, $row['password_hash'])) {
        $_SESSION['vault_unlocked'] = true;
        $_SESSION['vault_unlock_time'] = time();
        $_SESSION['vault_password'] = $password;
        redirect(SITE_URL . '/passwords.php');
    } else {
        $error = 'Invalid password. Please try again.';
    }
}

// If vault is unlocked and not expired (30 min timeout), go to list
if (!empty($_SESSION['vault_unlocked']) && !empty($_SESSION['vault_unlock_time'])) {
    $elapsed = time() - $_SESSION['vault_unlock_time'];
    if ($elapsed < 1800) {
        redirect(SITE_URL . '/modules/passwords/index.php');
    } else {
        unset($_SESSION['vault_unlocked']);
        unset($_SESSION['vault_unlock_time']);
    }
}

$page_title = 'Unlock Password Vault';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Unlock Vault</title>
    <link rel="icon" href="<?php echo SITE_URL; ?>/assets/images/logo.png" type="image/png">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/variables.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/reset.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/styles.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/theme.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/passwords.css">
</head>
<body class="vault-unlock-page">
    <div class="vault-unlock-box">
        <div class="vault-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
        </div>
        <h1>Password Vault</h1>
        <p>Enter your account password to unlock your saved passwords.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="password" name="vault_password" class="form-input" placeholder="Your account password" autofocus required>
            <button type="submit" class="btn btn-primary">Unlock Vault</button>
        </form>

        <a href="<?php echo SITE_URL; ?>/dashboard.php" class="vault-back-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Back to Dashboard
        </a>
    </div>
</body>
</html>
<?php exit; ?>
