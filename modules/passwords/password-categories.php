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
$page_title = 'Password Categories';
$additional_css = ['passwords.css'];

ensurePasswordTablesExist($mysqli);
$categories = getPasswordCategories($mysqli, $user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCsrfToken($csrf)) {
        $error = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'save_category') {
            $result = savePasswordCategoryHandler($mysqli, $user_id, $_POST);
            if ($result['success']) {
                redirect(SITE_URL . '/modules/passwords/password-categories.php');
            } else {
                $error = $result['message'];
            }
        } elseif ($action === 'delete_category') {
            $result = deletePasswordCategoryHandler($mysqli, $user_id, $_POST);
            if ($result['success']) {
                redirect(SITE_URL . '/modules/passwords/password-categories.php');
            } else {
                $error = $result['message'];
            }
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="passwords-page">
    <div class="passwords-toolbar">
        <div>
            <h1 class="passwords-title">Categories</h1>
            <p class="passwords-subtitle">Organize your passwords into categories.</p>
        </div>
        <div class="passwords-toolbar-actions">
            <a href="<?php echo SITE_URL; ?>/passwords.php" class="btn btn-secondary">Back to Passwords</a>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Add Category Form -->
    <div class="category-form-container">
        <form method="POST" class="category-form">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <input type="hidden" name="action" value="save_category">
            <div class="category-add-row">
                <input type="text" name="name" class="form-input" placeholder="New category name..." required>
                <input type="text" name="icon" class="form-input category-icon-input" placeholder="Emoji icon (optional)">
                <input type="color" name="color" value="#6c5ce7" class="category-color-input" title="Category color">
                <button type="submit" class="btn btn-primary">Add Category</button>
            </div>
        </form>
    </div>

    <!-- Categories List -->
    <div class="categories-list">
        <?php if (empty($categories)): ?>
            <div class="passwords-empty">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="empty-icon">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                </svg>
                <h3>No categories yet</h3>
                <p>Create a category to organize your passwords.</p>
            </div>
        <?php else: ?>
            <?php foreach ($categories as $cat): ?>
                <div class="category-item">
                    <div class="category-info">
                        <span class="category-icon" style="background-color: <?php echo htmlspecialchars($cat['color']); ?>20; color: <?php echo htmlspecialchars($cat['color']); ?>;">
                            <?php echo !empty($cat['icon']) ? $cat['icon'] : '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>'; ?>
                        </span>
                        <span class="category-name"><?php echo htmlspecialchars($cat['name']); ?></span>
                    </div>
                    <form method="POST" class="inline-form" onsubmit="return confirm('Delete this category? Passwords in it will become uncategorized.');">
                        <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
                        <input type="hidden" name="action" value="delete_category">
                        <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                        <button type="submit" class="btn btn-icon btn-danger-icon" title="Delete">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
