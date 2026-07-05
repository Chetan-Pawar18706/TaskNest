<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin($auth);

$user_id = $auth->getUserId();
$page_title = 'Manage Shopping Categories';
$additional_css = ['shopping.css'];

ensureHabitGoalShoppingTablesExist($mysqli);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';

    if (!$auth->verifyCsrfToken($csrf)) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
        exit;
    }

    switch ($action) {
        case 'save_category':
            echo json_encode(saveShoppingCategoryHandler($mysqli, $user_id, $_POST));
            break;
        case 'delete_category':
            echo json_encode(deleteShoppingCategoryHandler($mysqli, $user_id, $_POST));
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }
    exit;
}

$categories = getShoppingCategories($mysqli, $user_id);
$csrf_token = $auth->generateCsrfToken();

include __DIR__ . '/../../includes/header.php';
?>

<div class="shopping-page">
    <div class="shopping-toolbar">
        <div>
            <h1 class="shopping-title">Manage Shopping Categories</h1>
            <p class="shopping-subtitle">Organize your shopping items with custom categories.</p>
        </div>
        <div class="shopping-toolbar-actions">
            <a href="<?php echo SITE_URL; ?>/shopping.php" class="btn btn-secondary">Back to Shopping</a>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;max-width:900px;">
        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:1.5rem;">
            <h2 style="font-size:1.2rem;font-weight:700;color:var(--text-primary);margin:0 0 1.25rem;" id="catFormTitle">Add Category</h2>

            <form id="categoryForm">
                <input type="hidden" name="action" value="save_category">
                <input type="hidden" name="category_id" id="catId" value="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="form-group">
                    <label for="catName">Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="catName" name="name" class="form-control" required placeholder="e.g. Groceries, Electronics">
                </div>

                <div class="form-group">
                    <label for="catColor">Color</label>
                    <input type="color" id="catColor" name="color" class="form-control" value="#6366f1">
                </div>

                <div style="display:flex;gap:0.75rem;margin-top:1rem;">
                    <button type="submit" class="btn btn-primary" id="catFormBtn">Save Category</button>
                    <button type="button" class="btn btn-secondary" id="catFormCancel" style="display:none;">Cancel</button>
                </div>
            </form>
        </div>

        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:1.5rem;">
            <h2 style="font-size:1.2rem;font-weight:700;color:var(--text-primary);margin:0 0 1.25rem;">Categories</h2>

            <div id="categoryList">
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $cat): ?>
                        <div class="expense-category-item" data-id="<?php echo (int) $cat['id']; ?>">
                            <div style="display:flex;align-items:center;gap:0.6rem;">
                                <span style="display:inline-block;width:14px;height:14px;border-radius:50%;background:<?php echo htmlspecialchars($cat['color']); ?>;flex-shrink:0;"></span>
                                <div>
                                    <div style="font-size:0.9rem;font-weight:600;color:var(--text-primary);"><?php echo htmlspecialchars($cat['name']); ?></div>
                                </div>
                            </div>
                            <div style="display:flex;gap:0.25rem;">
                                <button class="btn btn-secondary btn-sm edit-cat-btn" type="button"
                                    data-id="<?php echo (int) $cat['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($cat['name']); ?>"
                                    data-color="<?php echo htmlspecialchars($cat['color']); ?>">Edit</button>
                                <button class="btn btn-danger btn-sm delete-cat-btn" type="button"
                                    data-id="<?php echo (int) $cat['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($cat['name']); ?>">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state" style="padding:2rem 1rem;text-align:center;">
                        <p style="color:var(--text-secondary);">No categories yet. Create one to get started.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="deleteCategoryModal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="deleteCategoryModal"></div>
    <div class="modal-dialog modal-sm">
        <div class="modal-header">
            <h3>Delete Category</h3>
            <button class="modal-close" type="button" data-close-modal="deleteCategoryModal">&times;</button>
        </div>
        <div class="modal-body">
            <p id="deleteCategoryMessage">Are you sure you want to delete this category? Shopping items in this category will become uncategorized.</p>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-close-modal="deleteCategoryModal">Cancel</button>
                <button class="btn btn-danger" type="button" id="confirmDeleteCategoryBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var csrfToken = '<?php echo $csrf_token; ?>';
    var form = document.getElementById('categoryForm');
    var formTitle = document.getElementById('catFormTitle');
    var catIdInput = document.getElementById('catId');
    var nameInput = document.getElementById('catName');
    var colorInput = document.getElementById('catColor');
    var cancelBtn = document.getElementById('catFormCancel');
    var submitBtn = document.getElementById('catFormBtn');
    var categoryList = document.getElementById('categoryList');
    var deleteModal = document.getElementById('deleteCategoryModal');
    var confirmDeleteBtn = document.getElementById('confirmDeleteCategoryBtn');
    var pendingDeleteId = null;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(form);
        fetch('<?php echo SITE_URL; ?>/modules/shopping/shopping-categories.php', { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to save category.');
                }
            })
            .catch(function() { alert('An error occurred. Please try again.'); });
    });

    document.querySelectorAll('.edit-cat-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            catIdInput.value = btn.getAttribute('data-id');
            nameInput.value = btn.getAttribute('data-name');
            colorInput.value = btn.getAttribute('data-color');
            formTitle.textContent = 'Edit Category';
            submitBtn.textContent = 'Update Category';
            cancelBtn.style.display = '';
            nameInput.focus();
        });
    });

    cancelBtn.addEventListener('click', function() {
        catIdInput.value = '';
        nameInput.value = '';
        colorInput.value = '#6366f1';
        formTitle.textContent = 'Add Category';
        submitBtn.textContent = 'Save Category';
        cancelBtn.style.display = 'none';
    });

    document.querySelectorAll('.delete-cat-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            pendingDeleteId = btn.getAttribute('data-id');
            document.getElementById('deleteCategoryMessage').textContent =
                'Are you sure you want to delete "' + btn.getAttribute('data-name') + '"? Shopping items in this category will become uncategorized.';
            deleteModal.classList.add('active');
            deleteModal.setAttribute('aria-hidden', 'false');
        });
    });

    confirmDeleteBtn.addEventListener('click', function() {
        if (!pendingDeleteId) return;
        var fd = new FormData();
        fd.append('action', 'delete_category');
        fd.append('category_id', pendingDeleteId);
        fd.append('csrf_token', csrfToken);
        fetch('<?php echo SITE_URL; ?>/modules/shopping/shopping-categories.php', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                deleteModal.classList.remove('active');
                deleteModal.setAttribute('aria-hidden', 'true');
                if (data.success) {
                    var item = categoryList.querySelector('[data-id="' + pendingDeleteId + '"]');
                    if (item) item.remove();
                    if (!categoryList.querySelector('.expense-category-item')) {
                        categoryList.innerHTML = '<div class="empty-state" style="padding:2rem 1rem;text-align:center;"><p style="color:var(--text-secondary);">No categories yet. Create one to get started.</p></div>';
                    }
                    if (catIdInput.value === pendingDeleteId) { cancelBtn.click(); }
                    pendingDeleteId = null;
                } else {
                    alert(data.message || 'Failed to delete category.');
                }
            })
            .catch(function() {
                deleteModal.classList.remove('active');
                deleteModal.setAttribute('aria-hidden', 'true');
                alert('An error occurred. Please try again.');
            });
    });

    document.querySelectorAll('#deleteCategoryModal [data-close-modal]').forEach(function(el) {
        el.addEventListener('click', function() {
            deleteModal.classList.remove('active');
            deleteModal.setAttribute('aria-hidden', 'true');
            pendingDeleteId = null;
        });
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
