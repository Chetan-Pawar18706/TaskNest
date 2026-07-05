<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin($auth);

$user_id = $auth->getUserId();
$page_title = 'Upload Document';
$additional_css = ['documents.css'];

ensureDocumentTablesExist($mysqli);
$categories = getDocumentCategories($mysqli, $user_id);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCsrfToken($csrf)) {
        $error = 'Invalid CSRF token. Please try again.';
    } else {
        $result = uploadDocumentHandler($mysqli, $user_id, $_POST);
        if ($result['success']) {
            redirect(SITE_URL . '/documents.php');
        } else {
            $error = $result['message'];
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="documents-page">
    <div class="docs-toolbar">
        <div>
            <h1 class="docs-title">Upload Document</h1>
            <p class="docs-subtitle">Add a new document to your vault.</p>
        </div>
        <div class="docs-toolbar-actions">
            <a href="<?php echo SITE_URL; ?>/documents.php" class="btn btn-secondary">Back to Documents</a>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="note-form-card">
        <form method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <input type="hidden" name="action" value="upload_document">

            <div class="form-group">
                <label for="title">Title <span class="required">*</span></label>
                <input type="text" id="title" name="title" class="form-control" required maxlength="255" placeholder="Enter document title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3" placeholder="Optional description"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="document">File <span class="required">*</span></label>
                <input type="file" id="document" name="document" class="form-control" required accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx,.txt">
                <small style="color: var(--text-muted);">PDF, Images, Word, Excel, TXT &middot; Max 10MB</small>
            </div>

            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" class="form-control">
                    <option value="">No Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo (int) $cat['id']; ?>" <?php echo (isset($_POST['category_id']) && (int) $_POST['category_id'] === (int) $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="expiry_date">Expiry Date</label>
                <input type="date" id="expiry_date" name="expiry_date" class="form-control" value="<?php echo htmlspecialchars($_POST['expiry_date'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="reminder_date">Reminder Date</label>
                <input type="date" id="reminder_date" name="reminder_date" class="form-control" value="<?php echo htmlspecialchars($_POST['reminder_date'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="checkbox-inline">
                    <input type="checkbox" name="is_important" value="1" <?php echo !empty($_POST['is_important']) ? 'checked' : ''; ?>>
                    Mark as Important
                </label>
            </div>

            <div class="form-actions">
                <a href="<?php echo SITE_URL; ?>/documents.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Upload Document</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
