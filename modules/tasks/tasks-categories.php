<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin($auth);

$user_id = $auth->getUserId();
ensureTaskTablesExist($mysqli);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCsrfToken($csrf)) {
        redirect(SITE_URL . '/modules/tasks/tasks-categories.php?error=csrf');
    }

    $action = $_POST['action'] ?? '';
    $result = ['success' => false, 'message' => 'Unknown action.'];

    if ($action === 'save_category') {
        $result = saveCategoryHandler($mysqli, $user_id, $_POST);
    } elseif ($action === 'delete_category') {
        $result = deleteCategoryHandler($mysqli, $user_id, $_POST);
    }

    if ($result['success']) {
        $_SESSION['flash_success'] = $result['message'];
    } else {
        $_SESSION['flash_error'] = $result['message'];
    }
    redirect(SITE_URL . '/modules/tasks/tasks-categories.php');
}

$categories = getTaskCategories($mysqli, $user_id);

$page_title = 'Manage Task Categories';
$additional_css = ['tasks.css'];
include __DIR__ . '/../../includes/header.php';

$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<div class="tasks-page">
    <div class="tasks-toolbar">
        <div>
            <a href="<?php echo SITE_URL; ?>/tasks.php" class="btn btn-secondary">&larr; Back to Tasks</a>
        </div>
        <div>
            <h1 class="tasks-title">Manage Categories</h1>
            <p class="tasks-subtitle">Organize your tasks with custom categories.</p>
        </div>
    </div>

    <?php if ($flash_success): ?>
        <div style="padding:1rem;border-radius:var(--radius-lg);background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;margin-bottom:1rem;">
            <?php echo htmlspecialchars($flash_success); ?>
        </div>
    <?php endif; ?>
    <?php if ($flash_error): ?>
        <div style="padding:1rem;border-radius:var(--radius-lg);background:#fef2f2;color:#dc2626;border:1px solid #fecaca;margin-bottom:1rem;">
            <?php echo htmlspecialchars($flash_error); ?>
        </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start;">
        <div style="background:var(--surface);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:2rem;box-shadow:var(--shadow-sm);">
            <h2 style="margin:0 0 1.5rem;font-size:1.2rem;font-weight:600;">Add New Category</h2>
            <form method="post" action="<?php echo SITE_URL; ?>/modules/tasks/tasks-categories.php">
                <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
                <input type="hidden" name="action" value="save_category">

                <div class="form-group">
                    <label for="cat-name">Name <span style="color:#dc2626;">*</span></label>
                    <input type="text" id="cat-name" name="name" class="form-control" required placeholder="Category name" maxlength="100">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="cat-color">Color</label>
                        <input type="color" id="cat-color" name="color" class="form-control" value="#6366f1">
                    </div>
                    <div class="form-group">
                        <label for="cat-icon">Icon</label>
                        <input type="text" id="cat-icon" name="icon" class="form-control" placeholder="e.g. task, star, work" value="task" maxlength="50">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Create Category</button>
            </form>
        </div>

        <div style="background:var(--surface);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:2rem;box-shadow:var(--shadow-sm);">
            <h2 style="margin:0 0 1.5rem;font-size:1.2rem;font-weight:600;">Existing Categories</h2>

            <?php if (empty($categories)): ?>
                <p style="color:var(--text-muted);margin:0;">No categories yet. Create one to get started.</p>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:0.75rem;">
                    <?php foreach ($categories as $cat): ?>
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:0.85rem 1rem;border:1px solid var(--border-color);border-radius:var(--radius-lg);background:var(--bg-secondary);">
                            <div style="display:flex;align-items:center;gap:0.75rem;">
                                <span style="width:28px;height:28px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:700;background:<?php echo htmlspecialchars($cat['color']); ?>;">
                                    <?php echo strtoupper(htmlspecialchars(substr($cat['name'], 0, 1))); ?>
                                </span>
                                <div>
                                    <strong style="font-size:.95rem;"><?php echo htmlspecialchars($cat['name']); ?></strong>
                                    <span style="display:block;font-size:.78rem;color:var(--text-muted);"><?php echo htmlspecialchars($cat['icon']); ?></span>
                                </div>
                            </div>
                            <div style="display:flex;gap:0.5rem;">
                                <button type="button" class="btn btn-secondary edit-cat-btn"
                                    style="padding:0.4rem 0.75rem;font-size:.82rem;"
                                    data-id="<?php echo (int) $cat['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($cat['name']); ?>"
                                    data-color="<?php echo htmlspecialchars($cat['color']); ?>"
                                    data-icon="<?php echo htmlspecialchars($cat['icon']); ?>"
                                    onclick="editCategory(this)">Edit</button>
                                <button type="button" class="btn btn-secondary delete-cat-btn"
                                    style="padding:0.4rem 0.75rem;font-size:.82rem;color:#dc2626;"
                                    data-id="<?php echo (int) $cat['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($cat['name']); ?>"
                                    onclick="confirmDeleteCategory(this)">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="editCategoryModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;z-index:1000;background:rgba(0,0,0,.5);">
        <div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;">
            <div style="background:var(--surface);border-radius:var(--radius-lg);box-shadow:0 20px 60px rgba(0,0,0,.3);width:90%;max-width:480px;">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:1.25rem 1.5rem;border-bottom:1px solid var(--border-color);">
                    <h3 style="margin:0;font-size:1.2rem;font-weight:600;">Edit Category</h3>
                    <button type="button" onclick="closeEditModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--text-muted);">&times;</button>
                </div>
                <form method="post" action="<?php echo SITE_URL; ?>/modules/tasks/tasks-categories.php" style="padding:1.5rem;">
                    <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
                    <input type="hidden" name="action" value="save_category">
                    <input type="hidden" name="category_id" id="edit-category-id">

                    <div class="form-group">
                        <label for="edit-cat-name">Name <span style="color:#dc2626;">*</span></label>
                        <input type="text" id="edit-cat-name" name="name" class="form-control" required maxlength="100">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-cat-color">Color</label>
                            <input type="color" id="edit-cat-color" name="color" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="edit-cat-icon">Icon</label>
                            <input type="text" id="edit-cat-icon" name="icon" class="form-control" maxlength="50">
                        </div>
                    </div>

                    <div style="display:flex;gap:0.75rem;margin-top:1rem;">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="deleteCategoryModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;z-index:1000;background:rgba(0,0,0,.5);">
        <div style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;">
            <div style="background:var(--surface);border-radius:var(--radius-lg);box-shadow:0 20px 60px rgba(0,0,0,.3);width:90%;max-width:400px;">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:1.25rem 1.5rem;border-bottom:1px solid var(--border-color);">
                    <h3 style="margin:0;font-size:1.2rem;font-weight:600;">Confirm Delete</h3>
                    <button type="button" onclick="closeDeleteModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--text-muted);">&times;</button>
                </div>
                <div style="padding:1.5rem;">
                    <p style="margin:0 0 1rem;color:var(--text-secondary);">Are you sure you want to delete the category <strong id="delete-cat-name"></strong>? Tasks in this category will become uncategorized.</p>
                    <form method="post" action="<?php echo SITE_URL; ?>/modules/tasks/tasks-categories.php">
                        <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
                        <input type="hidden" name="action" value="delete_category">
                        <input type="hidden" name="category_id" id="delete-category-id">
                        <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                            <button type="submit" class="btn btn-primary" style="background:#dc2626;">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editCategory(btn) {
    document.getElementById('edit-category-id').value = btn.dataset.id;
    document.getElementById('edit-cat-name').value = btn.dataset.name;
    document.getElementById('edit-cat-color').value = btn.dataset.color;
    document.getElementById('edit-cat-icon').value = btn.dataset.icon;
    document.getElementById('editCategoryModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editCategoryModal').style.display = 'none';
}

function confirmDeleteCategory(btn) {
    document.getElementById('delete-category-id').value = btn.dataset.id;
    document.getElementById('delete-cat-name').textContent = btn.dataset.name;
    document.getElementById('deleteCategoryModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteCategoryModal').style.display = 'none';
}

document.getElementById('editCategoryModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

document.getElementById('deleteCategoryModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
