<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin($auth);

$user_id = $auth->getUserId();
$page_title = 'Manage Document Categories';
$additional_css = ['documents.css'];

ensureDocumentTablesExist($mysqli);

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
            echo json_encode(saveDocumentCategoryHandler($mysqli, $user_id, $_POST));
            break;
        case 'delete_category':
            echo json_encode(deleteDocumentCategoryHandler($mysqli, $user_id, $_POST));
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }
    exit;
}

$categories = getDocumentCategories($mysqli, $user_id);
$csrf_token = $auth->generateCsrfToken();

include __DIR__ . '/includes/header.php';
?>

<div class="documents-page">
    <div class="docs-toolbar">
        <div>
            <h1 class="docs-title">Manage Document Categories</h1>
            <p class="docs-subtitle">Organize your documents with custom categories.</p>
        </div>
        <div class="docs-toolbar-actions">
            <a href="<?php echo SITE_URL; ?>/documents.php" class="btn btn-secondary">Back to Documents</a>
        </div>
    </div>

    <div class="notes-categories-layout">
        <div class="note-category-form-card">
            <h2 id="categoryFormTitle">Add Category</h2>
            <form id="categoryForm">
                <input type="hidden" name="action" value="save_category">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="category_id" id="categoryId" value="">

                <div class="form-group">
                    <label for="categoryName">Name <span class="required">*</span></label>
                    <input type="text" id="categoryName" name="name" class="form-control" required maxlength="100" placeholder="Category name">
                </div>

                <div class="form-group">
                    <label for="categoryColor">Color</label>
                    <div class="color-input-group">
                        <input type="color" id="categoryColor" name="color" class="form-control color-picker" value="#6366f1">
                        <input type="text" id="categoryColorHex" class="form-control color-hex" value="#6366f1" maxlength="7" placeholder="#6366f1">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancelEditBtn" style="display:none;">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="categorySubmitBtn">Save Category</button>
                </div>
            </form>
        </div>

        <div class="note-category-list-card">
            <h2>Categories</h2>
            <div id="categoryList">
                <?php if (empty($categories)): ?>
                    <div class="empty-state-sm">
                        <p>No categories yet. Create one to organize your documents.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                        <div class="category-item" data-id="<?php echo (int) $cat['id']; ?>">
                            <div class="category-item-info">
                                <span class="category-color-dot" style="background:<?php echo htmlspecialchars($cat['color'] ?? '#6366f1'); ?>"></span>
                                <span class="category-item-name"><?php echo htmlspecialchars($cat['name']); ?></span>
                            </div>
                            <div class="category-item-actions">
                                <button class="btn btn-secondary btn-sm edit-category-btn" data-id="<?php echo (int) $cat['id']; ?>" data-name="<?php echo htmlspecialchars($cat['name']); ?>" data-color="<?php echo htmlspecialchars($cat['color'] ?? '#6366f1'); ?>">Edit</button>
                                <button class="btn btn-danger btn-sm delete-category-btn" data-id="<?php echo (int) $cat['id']; ?>" data-name="<?php echo htmlspecialchars($cat['name']); ?>">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
            <p id="deleteCategoryMessage">Are you sure you want to delete this category? Documents in this category will become uncategorized.</p>
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
    var formTitle = document.getElementById('categoryFormTitle');
    var categoryIdInput = document.getElementById('categoryId');
    var nameInput = document.getElementById('categoryName');
    var colorInput = document.getElementById('categoryColor');
    var colorHex = document.getElementById('categoryColorHex');
    var cancelBtn = document.getElementById('cancelEditBtn');
    var submitBtn = document.getElementById('categorySubmitBtn');
    var categoryList = document.getElementById('categoryList');
    var deleteModal = document.getElementById('deleteCategoryModal');
    var confirmDeleteBtn = document.getElementById('confirmDeleteCategoryBtn');
    var pendingDeleteId = null;

    colorInput.addEventListener('input', function() {
        colorHex.value = colorInput.value;
    });
    colorHex.addEventListener('input', function() {
        if (/^#[0-9A-Fa-f]{6}$/.test(colorHex.value)) {
            colorInput.value = colorHex.value;
        }
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(form);
        fetch('<?php echo SITE_URL; ?>/documents-categories.php', {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Failed to save category.');
            }
        })
        .catch(function() {
            alert('An error occurred. Please try again.');
        });
    });

    document.querySelectorAll('.edit-category-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            categoryIdInput.value = btn.getAttribute('data-id');
            nameInput.value = btn.getAttribute('data-name');
            colorInput.value = btn.getAttribute('data-color');
            colorHex.value = btn.getAttribute('data-color');
            formTitle.textContent = 'Edit Category';
            submitBtn.textContent = 'Update Category';
            cancelBtn.style.display = '';
            nameInput.focus();
        });
    });

    cancelBtn.addEventListener('click', function() {
        categoryIdInput.value = '';
        nameInput.value = '';
        colorInput.value = '#6366f1';
        colorHex.value = '#6366f1';
        formTitle.textContent = 'Add Category';
        submitBtn.textContent = 'Save Category';
        cancelBtn.style.display = 'none';
    });

    document.querySelectorAll('.delete-category-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            pendingDeleteId = btn.getAttribute('data-id');
            document.getElementById('deleteCategoryMessage').textContent =
                'Are you sure you want to delete "' + btn.getAttribute('data-name') + '"? Documents in this category will become uncategorized.';
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
        fetch('<?php echo SITE_URL; ?>/documents-categories.php', {
            method: 'POST',
            body: fd
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            deleteModal.classList.remove('active');
            deleteModal.setAttribute('aria-hidden', 'true');
            if (data.success) {
                var item = categoryList.querySelector('[data-id="' + pendingDeleteId + '"]');
                if (item) item.remove();
                if (!categoryList.querySelector('.category-item')) {
                    categoryList.innerHTML = '<div class="empty-state-sm"><p>No categories yet. Create one to organize your documents.</p></div>';
                }
                if (categoryIdInput.value === pendingDeleteId) {
                    cancelBtn.click();
                }
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

<?php include __DIR__ . '/includes/footer.php'; ?>
