<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin($auth);

$user_id = $auth->getUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? '';
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCsrfToken($csrf)) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
        exit;
    }

    if ($action === 'save_category') {
        echo json_encode(saveExpenseCategoryHandler($mysqli, $user_id, $_POST));
    } elseif ($action === 'delete_category') {
        echo json_encode(deleteExpenseCategoryHandler($mysqli, $user_id, $_POST));
    } else {
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }
    exit;
}

$categories = getExpenseCategories($mysqli, $user_id);
$csrf_token = $auth->generateCsrfToken();

$page_title = 'Manage Expense Categories';
$additional_css = ['expenses.css'];
include __DIR__ . '/../../includes/header.php';
?>

<div class="expenses-page">
    <div class="expenses-toolbar">
        <div>
            <a href="<?php echo SITE_URL; ?>/expenses.php" class="btn btn-secondary btn-sm">&larr; Back to Expenses</a>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;max-width:900px;">
        <!-- Add / Edit Category Form -->
        <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:1.5rem;">
            <h2 style="font-size:1.2rem;font-weight:700;color:var(--text-primary);margin:0 0 1.25rem;" id="catFormTitle">Add Category</h2>

            <form id="categoryForm">
                <input type="hidden" name="action" value="save_category">
                <input type="hidden" name="category_id" id="catId" value="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="form-group">
                    <label for="catName">Name <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="catName" name="name" class="form-control" required placeholder="e.g. Food, Salary">
                </div>

                <div class="form-row" style="display:flex;gap:1rem;">
                    <div class="form-group" style="flex:1;">
                        <label for="catColor">Color</label>
                        <input type="color" id="catColor" name="color" class="form-control" value="#6366f1">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label for="catType">Type</label>
                        <select id="catType" name="type" class="form-control">
                            <option value="expense">Expense</option>
                            <option value="income">Income</option>
                        </select>
                    </div>
                </div>

                <div style="display:flex;gap:0.75rem;margin-top:1rem;">
                    <button type="submit" class="btn btn-primary" id="catFormBtn">Save Category</button>
                    <button type="button" class="btn btn-secondary" id="catFormCancel" style="display:none;">Cancel</button>
                </div>
            </form>
        </div>

        <!-- Category List -->
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
                                    <div style="font-size:0.75rem;color:var(--text-secondary);text-transform:capitalize;"><?php echo htmlspecialchars($cat['type']); ?></div>
                                </div>
                            </div>
                            <div style="display:flex;gap:0.25rem;">
                                <button class="btn btn-secondary btn-sm edit-cat-btn" type="button"
                                    data-id="<?php echo (int) $cat['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($cat['name']); ?>"
                                    data-color="<?php echo htmlspecialchars($cat['color']); ?>"
                                    data-type="<?php echo htmlspecialchars($cat['type']); ?>">Edit</button>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('categoryForm');
    var formTitle = document.getElementById('catFormTitle');
    var formBtn = document.getElementById('catFormBtn');
    var cancelBtn = document.getElementById('catFormCancel');
    var catIdInput = document.getElementById('catId');
    var catNameInput = document.getElementById('catName');
    var catColorInput = document.getElementById('catColor');
    var catTypeInput = document.getElementById('catType');
    var listContainer = document.getElementById('categoryList');

    function resetForm() {
        form.reset();
        catIdInput.value = '';
        catColorInput.value = '#6366f1';
        formTitle.textContent = 'Add Category';
        formBtn.textContent = 'Save Category';
        cancelBtn.style.display = 'none';
    }

    cancelBtn.addEventListener('click', resetForm);

    document.querySelectorAll('.edit-cat-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            catIdInput.value = btn.getAttribute('data-id');
            catNameInput.value = btn.getAttribute('data-name');
            catColorInput.value = btn.getAttribute('data-color');
            catTypeInput.value = btn.getAttribute('data-type');
            formTitle.textContent = 'Edit Category';
            formBtn.textContent = 'Update Category';
            cancelBtn.style.display = '';
        });
    });

    document.querySelectorAll('.delete-cat-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var catId = btn.getAttribute('data-id');
            var catName = btn.getAttribute('data-name');
            ConfirmModal.show('Delete Category', 'Are you sure you want to delete "' + catName + '"? This cannot be undone.', function() {
                var formData = new FormData();
                formData.append('action', 'delete_category');
                formData.append('category_id', catId);
                formData.append('csrf_token', '<?php echo htmlspecialchars($csrf_token); ?>');

                fetch('<?php echo SITE_URL; ?>/modules/expenses/expenses-categories.php', { method: 'POST', body: formData })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            var item = listContainer.querySelector('[data-id="' + catId + '"]');
                            if (item) item.remove();
                            resetForm();
                            if (listContainer.querySelectorAll('.expense-category-item').length === 0) {
                                listContainer.innerHTML = '<div class="empty-state" style="padding:2rem 1rem;text-align:center;"><p style="color:var(--text-secondary);">No categories yet. Create one to get started.</p></div>';
                            }
                        } else {
                            alert(data.message || 'Failed to delete category.');
                        }
                    })
                    .catch(function() { alert('Network error. Please try again.'); });
            }, 'Delete');
        });
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(form);

        fetch('<?php echo SITE_URL; ?>/modules/expenses/expenses-categories.php', { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'An error occurred.');
                }
            })
            .catch(function() { alert('Network error. Please try again.'); });
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
