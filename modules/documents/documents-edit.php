<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin($auth);

$user_id = $auth->getUserId();
$page_title = 'Edit Document';
$additional_css = ['documents.css'];

ensureDocumentTablesExist($mysqli);
$categories = getDocumentCategories($mysqli, $user_id);

$document_id = (int) ($_GET['id'] ?? 0);
if ($document_id <= 0) {
    redirect(SITE_URL . '/documents.php');
}

$stmt = safePrepare($mysqli, 'SELECT id, title, description, category_id, expiry_date, reminder_date, is_important FROM documents WHERE id = ? AND user_id = ? AND is_deleted = 0');
$stmt->bind_param('ii', $document_id, $user_id);
$stmt->execute();
$document = $stmt->get_result()->fetch_assoc();

if (!$document) {
    redirect(SITE_URL . '/documents.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCsrfToken($csrf)) {
        $error = 'Invalid CSRF token. Please try again.';
    } else {
        $_POST['document_id'] = $document_id;
        $result = updateDocumentHandler($mysqli, $user_id, $_POST);
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
            <h1 class="docs-title">Edit Document</h1>
            <p class="docs-subtitle">Update document details.</p>
        </div>
        <div class="docs-toolbar-actions">
            <a href="<?php echo SITE_URL; ?>/documents.php" class="btn btn-secondary">Back to Documents</a>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="note-form-card">
        <form method="post" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <input type="hidden" name="action" value="update_document">
            <input type="hidden" name="document_id" value="<?php echo (int) $document['id']; ?>">

            <div class="form-group">
                <label for="title">Title <span class="required">*</span></label>
                <input type="text" id="title" name="title" class="form-control" required maxlength="255" placeholder="Enter document title" value="<?php echo htmlspecialchars($_POST['title'] ?? $document['title']); ?>">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3" placeholder="Optional description"><?php echo htmlspecialchars($_POST['description'] ?? $document['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" class="form-control">
                    <option value="">No Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo (int) $cat['id']; ?>" <?php echo ((int) ($_POST['category_id'] ?? $document['category_id']) === (int) $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="expiry_date">Expiry Date</label>
                <input type="date" id="expiry_date" name="expiry_date" class="form-control" value="<?php echo htmlspecialchars($_POST['expiry_date'] ?? $document['expiry_date']); ?>">
            </div>

            <div class="form-group">
                <label for="reminder_date">Reminder Date</label>
                <input type="date" id="reminder_date" name="reminder_date" class="form-control" value="<?php echo htmlspecialchars($_POST['reminder_date'] ?? $document['reminder_date']); ?>">
            </div>

            <div class="form-group">
                <label class="checkbox-inline">
                    <input type="checkbox" name="is_important" value="1" <?php echo !empty($_POST['is_important'] ?? $document['is_important']) ? 'checked' : ''; ?>>
                    Mark as Important
                </label>
            </div>

            <div class="form-actions">
                <a href="<?php echo SITE_URL; ?>/documents.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Document</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
