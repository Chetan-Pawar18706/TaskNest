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
$page_title = 'Passwords';
$additional_css = ['passwords.css'];

ensurePasswordTablesExist($mysqli);
$categories = getPasswordCategories($mysqli, $user_id);
$counts = getPasswordCounts($mysqli, $user_id);

include __DIR__ . '/../../includes/header.php';
?>

<div class="passwords-page">
    <div class="passwords-toolbar">
        <div>
            <h1 class="passwords-title">Password Vault</h1>
            <p class="passwords-subtitle">Store and manage your passwords securely.</p>
        </div>
        <div class="passwords-toolbar-actions">
            <a href="<?php echo SITE_URL; ?>/modules/passwords/passwords-add.php" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Add Password
            </a>
            <button class="btn btn-secondary" id="lockVaultBtn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                Lock Vault
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="passwords-filters">
        <div class="passwords-search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" id="passwordSearch" placeholder="Search passwords..." class="form-input">
        </div>
        <div class="passwords-filter-group">
            <select id="categoryFilter" class="form-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-secondary" id="favoritesFilter">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                Favorites
            </button>
            <a href="<?php echo SITE_URL; ?>/modules/passwords/password-categories.php" class="btn btn-secondary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                Categories
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="passwords-stats">
        <div class="password-stat">
            <span class="password-stat-value" id="totalCount"><?php echo $counts['total']; ?></span>
            <span class="password-stat-label">Total</span>
        </div>
        <div class="password-stat">
            <span class="password-stat-value" id="favoritesCount"><?php echo $counts['favorites']; ?></span>
            <span class="password-stat-label">Favorites</span>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="passwords-bulk-actions" id="bulkActions" style="display:none;">
        <span id="selectedCount">0 selected</span>
        <button class="btn btn-danger btn-sm" id="bulkDeleteBtn">Delete Selected</button>
    </div>

    <!-- Passwords List -->
    <div class="passwords-grid" id="passwordsGrid">
        <div class="passwords-loading">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spin"><circle cx="12" cy="12" r="10"></circle></svg>
            Loading passwords...
        </div>
    </div>

    <!-- Empty State -->
    <div class="passwords-empty" id="passwordsEmpty" style="display:none;">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="empty-icon">
            <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
        </svg>
        <h3>No passwords yet</h3>
        <p>Start saving your passwords securely.</p>
        <a href="<?php echo SITE_URL; ?>/modules/passwords/passwords-add.php" class="btn btn-primary">Add First Password</a>
    </div>
</div>

<script>
    var csrfToken = '<?php echo $auth->generateCsrfToken(); ?>';
    var siteUrl = '<?php echo SITE_URL; ?>';
</script>
<script src="<?php echo SITE_URL; ?>/assets/js/passwords.js"></script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
