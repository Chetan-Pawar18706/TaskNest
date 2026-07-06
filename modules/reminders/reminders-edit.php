<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin($auth);

$user_id = $auth->getUserId();
$reminder_id = (int)($_GET['id'] ?? 0);

if ($reminder_id <= 0) {
    redirect(SITE_URL . '/reminders.php');
}

ensureReminderTableExists($mysqli);

$reminder = getReminderById($mysqli, $user_id, $reminder_id);
if (!$reminder) {
    redirect(SITE_URL . '/reminders.php');
}

$page_title = 'Edit Reminder - ' . $reminder['title'];
$additional_css = ['reminders.css'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCsrfToken($csrf)) {
        $error = 'Invalid CSRF token. Please try again.';
    } else {
        $_POST['reminder_id'] = $reminder_id;
        $result = saveReminder($mysqli, $user_id, $_POST);
        if ($result['success']) {
            redirect(SITE_URL . '/reminders.php');
        } else {
            $error = $result['message'];
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="reminders-page">
    <div class="reminders-toolbar">
        <div>
            <h1 class="reminders-title">Edit Reminder</h1>
            <p class="reminders-subtitle">Update: <?php echo htmlspecialchars($reminder['title']); ?></p>
        </div>
        <div class="reminders-toolbar-actions">
            <a href="<?php echo SITE_URL; ?>/reminders.php" class="btn btn-secondary">Back to Reminders</a>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="reminder-form-container">
        <form method="POST" class="reminder-form">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">

            <div class="form-group">
                <label for="title" class="form-label">Title *</label>
                <input type="text" name="title" id="title" class="form-input" required value="<?php echo htmlspecialchars($reminder['title']); ?>">
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-textarea" rows="3"><?php echo htmlspecialchars($reminder['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="reminder_date" class="form-label">Date *</label>
                    <input type="date" name="reminder_date" id="reminder_date" class="form-input" required value="<?php echo htmlspecialchars($reminder['reminder_date']); ?>">
                </div>
                <div class="form-group">
                    <label for="reminder_time" class="form-label">Time *</label>
                    <input type="time" name="reminder_time" id="reminder_time" class="form-input" required value="<?php echo htmlspecialchars(substr($reminder['reminder_time'], 0, 5)); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="priority" class="form-label">Priority</label>
                    <select name="priority" id="priority" class="form-select">
                        <option value="low" <?php echo ($reminder['priority'] === 'low') ? 'selected' : ''; ?>>Low</option>
                        <option value="medium" <?php echo ($reminder['priority'] === 'medium') ? 'selected' : ''; ?>>Medium</option>
                        <option value="high" <?php echo ($reminder['priority'] === 'high') ? 'selected' : ''; ?>>High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="repeat_type" class="form-label">Repeat</label>
                    <select name="repeat_type" id="repeat_type" class="form-select">
                        <option value="none" <?php echo ($reminder['repeat_type'] === 'none') ? 'selected' : ''; ?>>No Repeat</option>
                        <option value="daily" <?php echo ($reminder['repeat_type'] === 'daily') ? 'selected' : ''; ?>>Daily</option>
                        <option value="weekly" <?php echo ($reminder['repeat_type'] === 'weekly') ? 'selected' : ''; ?>>Weekly</option>
                        <option value="monthly" <?php echo ($reminder['repeat_type'] === 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                        <option value="yearly" <?php echo ($reminder['repeat_type'] === 'yearly') ? 'selected' : ''; ?>>Yearly</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="category" class="form-label">Category</label>
                <input type="text" name="category" id="category" class="form-input" value="<?php echo htmlspecialchars($reminder['category'] ?? ''); ?>" list="categoryList">
                <datalist id="categoryList">
                    <option value="Health">
                    <option value="Work">
                    <option value="Personal">
                    <option value="Finance">
                    <option value="Education">
                    <option value="Other">
                </datalist>
            </div>

            <div class="form-actions">
                <a href="<?php echo SITE_URL; ?>/reminders.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Reminder</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
