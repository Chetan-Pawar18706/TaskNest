<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin($auth);

$user_id = $auth->getUserId();
$page_title = 'Edit Note';
$additional_css = ['notes.css'];

ensureNoteTablesExist($mysqli);
$categories = getNoteCategories($mysqli, $user_id);

$note_id = (int) ($_GET['id'] ?? 0);
if ($note_id <= 0) {
    redirect(SITE_URL . '/notes.php');
}

$stmt = safePrepare($mysqli, 'SELECT id, title, content, category_id, is_pinned, file_path FROM notes WHERE id = ? AND user_id = ? AND is_deleted = 0');
$stmt->bind_param('ii', $note_id, $user_id);
$stmt->execute();
$note = $stmt->get_result()->fetch_assoc();

if (!$note) {
    redirect(SITE_URL . '/notes.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCsrfToken($csrf)) {
        $error = 'Invalid CSRF token. Please try again.';
    } else {
        // Handle file upload
        $file_path = $note['file_path'] ?? '';
        if (!empty($_FILES['attachment']['name'])) {
            $allowed = ['pdf','png','jpg','jpeg','doc','docx','xls','xlsx','txt','zip'];
            $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $error = 'File type not allowed.';
            } elseif ($_FILES['attachment']['size'] > 10 * 1024 * 1024) {
                $error = 'File must be less than 10MB.';
            } else {
                $dir = __DIR__ . '/uploads/notes/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $filename = 'note_' . $note_id . '_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['attachment']['name']);
                if (move_uploaded_file($_FILES['attachment']['tmp_name'], $dir . $filename)) {
                    // Delete old file if exists
                    if ($file_path && file_exists(__DIR__ . '/' . $file_path)) {
                        unlink(__DIR__ . '/' . $file_path);
                    }
                    $file_path = 'uploads/notes/' . $filename;
                }
            }
        } elseif (!empty($_POST['remove_file'])) {
            if ($file_path && file_exists(__DIR__ . '/' . $file_path)) {
                unlink(__DIR__ . '/' . $file_path);
            }
            $file_path = '';
        }

        if (empty($error)) {
            $_POST['note_id'] = $note_id;
            $_POST['file_path'] = $file_path;
            $result = saveNoteHandler($mysqli, $user_id, $_POST);
            if ($result['success']) {
                redirect(SITE_URL . '/notes.php');
            } else {
                $error = $result['message'];
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="notes-page">
    <div class="notes-toolbar">
        <div>
            <h1 class="notes-title">Edit Note</h1>
            <p class="notes-subtitle">Update your note details.</p>
        </div>
        <div class="notes-toolbar-actions">
            <a href="<?php echo SITE_URL; ?>/notes.php" class="btn btn-secondary">Back to Notes</a>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="note-form-card" style="padding:1.25rem;">
        <form method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <input type="hidden" name="action" value="save_note">
            <input type="hidden" name="note_id" value="<?php echo (int) $note['id']; ?>">

            <div class="form-group">
                <label for="title">Title <span class="required">*</span></label>
                <input type="text" id="title" name="title" class="form-control" required maxlength="255" placeholder="Enter note title" value="<?php echo htmlspecialchars($_POST['title'] ?? $note['title']); ?>">
            </div>

            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" class="form-control">
                    <option value="">No Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo (int) $cat['id']; ?>" <?php echo ((int) ($_POST['category_id'] ?? $note['category_id']) === (int) $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="content">Content</label>
                <div class="rich-text-toolbar">
                    <button type="button" data-cmd="bold" title="Bold"><strong>B</strong></button>
                    <button type="button" data-cmd="italic" title="Italic"><em>I</em></button>
                    <button type="button" data-cmd="underline" title="Underline"><u>U</u></button>
                    <button type="button" data-cmd="insertUnorderedList" title="Bullet List">&#8226; List</button>
                    <button type="button" data-cmd="insertOrderedList" title="Numbered List">1. List</button>
                    <button type="button" data-cmd="formatBlock" data-val="h2" title="Heading">H2</button>
                    <button type="button" data-cmd="formatBlock" data-val="h3" title="Subheading">H3</button>
                    <button type="button" data-cmd="formatBlock" data-val="p" title="Paragraph">P</button>
                </div>
                <div class="note-editor" id="noteEditor" contenteditable="true"></div>
                <input type="hidden" name="content" id="noteContent" value="<?php echo htmlspecialchars($_POST['content'] ?? $note['content']); ?>">
            </div>

            <div class="form-group">
                <label class="checkbox-inline">
                    <input type="checkbox" name="is_pinned" value="1" <?php echo !empty($_POST['is_pinned'] ?? $note['is_pinned']) ? 'checked' : ''; ?>>
                    Pin this note
                </label>
            </div>

            <?php if (!empty($note['file_path'])): ?>
            <div class="form-group">
                <label>Current Attachment</label>
                <div style="display:flex;align-items:center;gap:0.5rem;padding:0.75rem;background:var(--bg-secondary);border-radius:var(--radius-md);margin-bottom:0.5rem;">
                    <span style="font-size:0.875rem;"><?php echo basename($note['file_path']); ?></span>
                    <a href="<?php echo SITE_URL; ?>/<?php echo htmlspecialchars($note['file_path']); ?>" target="_blank" class="btn btn-secondary btn-sm" style="margin-left:auto;">View</a>
                    <label style="display:flex;align-items:center;gap:0.25rem;font-size:0.8rem;cursor:pointer;">
                        <input type="checkbox" name="remove_file" value="1"> Remove
                    </label>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="attachment"><?php echo !empty($note['file_path']) ? 'Replace Attachment' : 'Attachment (optional)'; ?></label>
                <input type="file" id="attachment" name="attachment" class="form-control" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx,.xls,.xlsx,.txt,.zip">
                <small style="color:var(--text-muted);margin-top:0.25rem;display:block;">Max 10MB. PDF, Images, Docs, Excel, Text, ZIP allowed.</small>
            </div>

            <div class="form-actions">
                <a href="<?php echo SITE_URL; ?>/notes.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Note</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var editor = document.getElementById('noteEditor');
    var contentInput = document.getElementById('noteContent');
    var toolbar = document.querySelector('.rich-text-toolbar');

    if (editor && contentInput) {
        if (contentInput.value) {
            editor.innerHTML = contentInput.value;
        }
        editor.addEventListener('input', function() {
            contentInput.value = editor.innerHTML;
        });
    }

    if (toolbar) {
        toolbar.addEventListener('click', function(e) {
            var btn = e.target.closest('button[data-cmd]');
            if (!btn) return;
            e.preventDefault();
            var cmd = btn.getAttribute('data-cmd');
            var val = btn.getAttribute('data-val') || null;
            document.execCommand(cmd, false, val);
            if (editor) editor.focus();
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
