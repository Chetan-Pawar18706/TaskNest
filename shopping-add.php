<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin($auth);

$user_id = $auth->getUserId();
ensureHabitGoalShoppingTablesExist($mysqli);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCsrfToken($csrf)) {
        $_SESSION['flash_error'] = 'Invalid CSRF token. Please try again.';
        redirect(SITE_URL . '/shopping-add.php');
    }

    $result = saveShoppingHandler($mysqli, $user_id, $_POST);
    if ($result['success']) {
        $_SESSION['flash_success'] = $result['message'];
        redirect(SITE_URL . '/shopping.php');
    } else {
        $_SESSION['flash_error'] = $result['message'];
        redirect(SITE_URL . '/shopping-add.php');
    }
}

$page_title = 'Add Shopping Item';
$additional_css = ['shopping.css'];
include __DIR__ . '/includes/header.php';

$flash_error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);

$name = $_POST['name'] ?? '';
$quantity = $_POST['quantity'] ?? '1';
$estimated_price = $_POST['estimated_price'] ?? '';
$actual_price = $_POST['actual_price'] ?? '';
$categoryId = $_POST['category_id'] ?? '';
$notes = $_POST['notes'] ?? '';

$shoppingCategories = getShoppingCategories($mysqli, $user_id);
?>

<div class="shopping-page">
    <div class="shopping-toolbar">
        <div>
            <a href="<?php echo SITE_URL; ?>/shopping.php" class="btn btn-secondary">&larr; Back to Shopping</a>
        </div>
        <div>
            <h1 class="shopping-title">Add Shopping Item</h1>
            <p class="shopping-subtitle">Add a new item to your shopping list.</p>
        </div>
    </div>

    <?php if ($flash_error): ?>
        <div class="alert alert-danger" style="padding:1rem;border-radius:var(--radius-lg);background:#fef2f2;color:#dc2626;border:1px solid #fecaca;margin-bottom:1rem;">
            <?php echo htmlspecialchars($flash_error); ?>
        </div>
    <?php endif; ?>

    <div style="background:var(--surface);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:1.25rem;box-shadow:var(--shadow-sm);max-width:720px;">
        <form method="post" action="<?php echo SITE_URL; ?>/shopping-add.php">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <input type="hidden" name="action" value="save_item">

            <div class="form-group">
                <label for="name">Name <span style="color:#dc2626;">*</span></label>
                <input type="text" id="name" name="name" class="form-control" required placeholder="e.g. Milk, Bread, Eggs" value="<?php echo htmlspecialchars($name); ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" id="quantity" name="quantity" class="form-control" min="1" value="<?php echo htmlspecialchars($quantity); ?>">
                </div>

                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="form-control">
                        <option value="">No Category</option>
                        <?php foreach ($shoppingCategories as $cat): ?>
                            <option value="<?php echo (int) $cat['id']; ?>" <?php echo $categoryId == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="estimated_price">Estimated Price</label>
                    <input type="number" id="estimated_price" name="estimated_price" class="form-control" step="0.01" min="0" placeholder="0.00" value="<?php echo htmlspecialchars($estimated_price); ?>">
                </div>

                <div class="form-group">
                    <label for="actual_price">Actual Price</label>
                    <input type="number" id="actual_price" name="actual_price" class="form-control" step="0.01" min="0" placeholder="0.00" value="<?php echo htmlspecialchars($actual_price); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Additional notes..."><?php echo htmlspecialchars($notes); ?></textarea>
            </div>

            <div style="display:flex;gap:0.75rem;margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">Add Item</button>
                <a href="<?php echo SITE_URL; ?>/shopping.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
