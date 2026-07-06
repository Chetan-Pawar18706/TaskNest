<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin($auth);

// Vault must be unlocked
if (empty($_SESSION['vault_unlocked']) || empty($_SESSION['vault_unlock_time']) || (time() - $_SESSION['vault_unlock_time'] >= 1800)) {
    unset($_SESSION['vault_unlocked']);
    unset($_SESSION['vault_unlock_time']);
    redirect(SITE_URL . '/passwords.php');
}

$user_id = $auth->getUserId();
$password_id = (int)($_GET['id'] ?? 0);

if ($password_id <= 0) {
    redirect(SITE_URL . '/passwords.php');
}

ensurePasswordTablesExist($mysqli);
$encryptionKey = deriveEncryptionKey($_SESSION['vault_password'], 'tasknest-' . $user_id . '-vault');
$categories = getPasswordCategories($mysqli, $user_id);

// Get existing password
$stmt = safePrepare($mysqli, 'SELECT * FROM saved_passwords WHERE id = ? AND user_id = ? AND is_deleted = 0');
$stmt->bind_param('ii', $password_id, $user_id);
$stmt->execute();
$password = $stmt->get_result()->fetch_assoc();

if (!$password) {
    redirect(SITE_URL . '/passwords.php');
}

// Decrypt password for form
$password['password'] = decryptPassword($password['encrypted_password'], $encryptionKey);

$page_title = 'Edit Password - ' . $password['title'];
$additional_css = ['passwords.css'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCsrfToken($csrf)) {
        $error = 'Invalid CSRF token. Please try again.';
    } else {
        $result = savePasswordHandler($mysqli, $user_id, $_POST, $encryptionKey);
        if ($result['success']) {
            redirect(SITE_URL . '/passwords.php');
        } else {
            $error = $result['message'];
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="passwords-page">
    <div class="passwords-toolbar">
        <div>
            <h1 class="passwords-title">Edit Password</h1>
            <p class="passwords-subtitle">Update: <?php echo htmlspecialchars($password['title']); ?></p>
        </div>
        <div class="passwords-toolbar-actions">
            <a href="<?php echo SITE_URL; ?>/passwords.php" class="btn btn-secondary">Back to Passwords</a>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="password-form-container">
        <form method="POST" class="password-form" id="passwordForm">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <input type="hidden" name="password_id" value="<?php echo $password_id; ?>">

            <div class="form-group">
                <label for="title" class="form-label">Title *</label>
                <input type="text" name="title" id="title" class="form-input" required value="<?php echo htmlspecialchars($password['title']); ?>">
            </div>

            <div class="form-group">
                <label for="username" class="form-label">Username / Email</label>
                <input type="text" name="username" id="username" class="form-input" value="<?php echo htmlspecialchars($password['username'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password (leave blank to keep current)</label>
                <div class="password-input-group">
                    <input type="password" name="password" id="password" class="form-input" placeholder="Leave blank to keep current password">
                    <button type="button" class="btn btn-icon toggle-password" data-target="password">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                    <button type="button" class="btn btn-icon generate-password-btn" id="generatePasswordBtn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>
                    </button>
                </div>
                <!-- Password Generator -->
                <div class="password-generator" id="passwordGenerator" style="display:none;">
                    <div class="generator-preview" id="generatorPreview"></div>
                    <div class="generator-options">
                        <div class="form-group">
                            <label>Length: <span id="lengthValue">16</span></label>
                            <input type="range" id="passwordLength" min="8" max="64" value="16" class="form-range">
                        </div>
                        <label class="checkbox-label"><input type="checkbox" id="genUppercase" checked> Uppercase (A-Z)</label>
                        <label class="checkbox-label"><input type="checkbox" id="genLowercase" checked> Lowercase (a-z)</label>
                        <label class="checkbox-label"><input type="checkbox" id="genNumbers" checked> Numbers (0-9)</label>
                        <label class="checkbox-label"><input type="checkbox" id="genSymbols" checked> Symbols (!@#$%)</label>
                        <button type="button" class="btn btn-secondary btn-sm" id="regenerateBtn">Regenerate</button>
                        <button type="button" class="btn btn-primary btn-sm" id="usePasswordBtn">Use This Password</button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="url" class="form-label">Website URL</label>
                <input type="url" name="url" id="url" class="form-input" value="<?php echo htmlspecialchars($password['url'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="category_id" class="form-label">Category</label>
                <select name="category_id" id="category_id" class="form-select">
                    <option value="">No Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($password['category_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">Notes</label>
                <textarea name="notes" id="notes" class="form-textarea" rows="3"><?php echo htmlspecialchars($password['notes'] ?? ''); ?></textarea>
            </div>

            <div class="form-actions">
                <a href="<?php echo SITE_URL; ?>/passwords.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Password</button>
            </div>
        </form>
    </div>
</div>

<script>
    var csrfToken = '<?php echo $auth->generateCsrfToken(); ?>';
    var siteUrl = '<?php echo SITE_URL; ?>';
</script>
<script src="<?php echo SITE_URL; ?>/assets/js/passwords.js"></script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
