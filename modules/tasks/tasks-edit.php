<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin($auth);

$user_id = $auth->getUserId();
ensureTaskTablesExist($mysqli);

$task_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($task_id <= 0) {
    redirect(SITE_URL . '/tasks.php');
}

$stmt = safePrepare($mysqli, 'SELECT * FROM tasks WHERE id = ? AND user_id = ? AND is_deleted = 0');
$stmt->bind_param('ii', $task_id, $user_id);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();

if (!$task) {
    $_SESSION['flash_error'] = 'Task not found.';
    redirect(SITE_URL . '/tasks.php');
}

$categories = getTaskCategories($mysqli, $user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCsrfToken($csrf)) {
        redirect(SITE_URL . '/modules/tasks/tasks-edit.php?id=' . $task_id . '&error=csrf');
    }

    // Handle file upload
    $file_path = $task['file_path'] ?? '';
    if (!empty($_FILES['attachment']['name'])) {
        $allowed = ['pdf','png','jpg','jpeg','doc','docx','xls','xlsx','txt','zip'];
        $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $_SESSION['flash_error'] = 'File type not allowed.';
            redirect(SITE_URL . '/modules/tasks/tasks-edit.php?id=' . $task_id);
        }
        if ($_FILES['attachment']['size'] > 10 * 1024 * 1024) {
            $_SESSION['flash_error'] = 'File must be less than 10MB.';
            redirect(SITE_URL . '/modules/tasks/tasks-edit.php?id=' . $task_id);
        }
        $dir = dirname(__DIR__, 2) . '/uploads/tasks/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $filename = 'task_' . $task_id . '_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['attachment']['name']);
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $dir . $filename)) {
            if ($file_path && file_exists(dirname(__DIR__, 2) . '/' . $file_path)) {
                unlink(dirname(__DIR__, 2) . '/' . $file_path);
            }
            $file_path = 'uploads/tasks/' . $filename;
        }
    } elseif (!empty($_POST['remove_file'])) {
        if ($file_path && file_exists(dirname(__DIR__, 2) . '/' . $file_path)) {
            unlink(dirname(__DIR__, 2) . '/' . $file_path);
        }
        $file_path = '';
    }

    $_POST['file_path'] = $file_path;
    $result = saveTaskHandler($mysqli, $user_id, $_POST);
    if ($result['success']) {
        $_SESSION['flash_success'] = $result['message'];
        redirect(SITE_URL . '/tasks.php');
    } else {
        $_SESSION['flash_error'] = $result['message'];
        redirect(SITE_URL . '/modules/tasks/tasks-edit.php?id=' . $task_id);
    }
}

$page_title = 'Edit Task';
$additional_css = ['tasks.css'];
include __DIR__ . '/../../includes/header.php';

$flash_error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);

$title = $_POST['title'] ?? $task['title'];
$description = $_POST['description'] ?? $task['description'];
$priority = $_POST['priority'] ?? $task['priority'];
$status = $_POST['status'] ?? $task['status'];
$category_id = $_POST['category_id'] ?? $task['category_id'];
$due_date = $_POST['due_date'] ?? $task['due_date'];
$reminder_datetime = $_POST['reminder_datetime'] ?? $task['reminder_datetime'];
?>

<div class="tasks-page">
    <div class="tasks-toolbar">
        <div>
            <a href="<?php echo SITE_URL; ?>/tasks.php" class="btn btn-secondary">&larr; Back to Tasks</a>
        </div>
        <div>
            <h1 class="tasks-title">Edit Task</h1>
            <p class="tasks-subtitle">Update task details.</p>
        </div>
    </div>

    <?php if ($flash_error): ?>
        <div class="alert alert-danger" style="padding:1rem;border-radius:var(--radius-lg);background:#fef2f2;color:#dc2626;border:1px solid #fecaca;margin-bottom:1rem;">
            <?php echo htmlspecialchars($flash_error); ?>
        </div>
    <?php endif; ?>

    <div style="background:var(--surface);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:1.25rem;box-shadow:var(--shadow-sm);max-width:720px;">
        <form method="post" action="<?php echo SITE_URL; ?>/modules/tasks/tasks-edit.php?id=<?php echo $task_id; ?>" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">

            <div class="form-group">
                <label for="title">Title <span style="color:#dc2626;">*</span></label>
                <input type="text" id="title" name="title" class="form-control" required placeholder="Enter task title" value="<?php echo htmlspecialchars($title); ?>">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4" placeholder="Describe the task..."><?php echo htmlspecialchars($description); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority" class="form-control">
                        <option value="Low" <?php echo $priority === 'Low' ? 'selected' : ''; ?>>Low</option>
                        <option value="Medium" <?php echo $priority === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="High" <?php echo $priority === 'High' ? 'selected' : ''; ?>>High</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="Pending" <?php echo $status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="In Progress" <?php echo $status === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Completed" <?php echo $status === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" class="form-control">
                        <option value="">No Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo (int) $cat['id']; ?>" <?php echo (string) $category_id === (string) $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="due_date">Due Date</label>
                    <input type="date" id="due_date" name="due_date" class="form-control" value="<?php echo htmlspecialchars($due_date); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="reminder_datetime">Reminder</label>
                <input type="datetime-local" id="reminder_datetime" name="reminder_datetime" class="form-control" value="<?php echo htmlspecialchars($reminder_datetime); ?>">
            </div>

            <?php if (!empty($task['file_path'])): ?>
            <div class="form-group">
                <label>Current Attachment</label>
                <div style="display:flex;align-items:center;gap:0.5rem;padding:0.75rem;background:var(--bg-secondary);border-radius:var(--radius-md);margin-bottom:0.5rem;">
                    <span style="font-size:0.875rem;"><?php echo basename($task['file_path']); ?></span>
                    <a href="<?php echo SITE_URL; ?>/<?php echo htmlspecialchars($task['file_path']); ?>" target="_blank" class="btn btn-secondary btn-sm" style="margin-left:auto;">View</a>
                    <label style="display:flex;align-items:center;gap:0.25rem;font-size:0.8rem;cursor:pointer;">
                        <input type="checkbox" name="remove_file" value="1"> Remove
                    </label>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="attachment"><?php echo !empty($task['file_path']) ? 'Replace Attachment' : 'Attachment (optional)'; ?></label>
                <input type="file" id="attachment" name="attachment" class="form-control" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx,.xls,.xlsx,.txt,.zip">
                <small style="color:var(--text-muted);margin-top:0.25rem;display:block;">Max 10MB. PDF, Images, Docs, Excel, Text, ZIP allowed.</small>
            </div>

            <div style="display:flex;gap:0.5rem;margin-top:1rem;">
                <button type="submit" class="btn btn-primary" style="height:36px;font-size:0.85rem;">Save Changes</button>
                <a href="<?php echo SITE_URL; ?>/tasks.php" class="btn btn-secondary" style="height:36px;font-size:0.85rem;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
