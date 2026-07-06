<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin($auth);

$user_id = $auth->getUserId();
$page_title = 'Add Reminder';
$additional_css = ['reminders.css'];

ensureReminderTableExists($mysqli);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCsrfToken($csrf)) {
        $error = 'Invalid CSRF token. Please try again.';
    } else {
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
            <h1 class="reminders-title">Add Reminder</h1>
            <p class="reminders-subtitle">Set a new reminder for yourself.</p>
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
                <input type="text" name="title" id="title" class="form-input" placeholder="e.g., Take medicine, Meeting call" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-textarea" rows="3" placeholder="Additional details..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="reminder_date" class="form-label">Date *</label>
                    <input type="date" name="reminder_date" id="reminder_date" class="form-input" required value="<?php echo htmlspecialchars($_POST['reminder_date'] ?? date('Y-m-d')); ?>">
                </div>
                <div class="form-group">
                    <label for="reminder_time" class="form-label">Time *</label>
                    <input type="time" name="reminder_time" id="reminder_time" class="form-input" required value="<?php echo htmlspecialchars($_POST['reminder_time'] ?? '09:00'); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="priority" class="form-label">Priority</label>
                    <select name="priority" id="priority" class="form-select">
                        <option value="low" <?php echo (($_POST['priority'] ?? '') === 'low') ? 'selected' : ''; ?>>Low</option>
                        <option value="medium" <?php echo (($_POST['priority'] ?? 'medium') === 'medium') ? 'selected' : ''; ?>>Medium</option>
                        <option value="high" <?php echo (($_POST['priority'] ?? '') === 'high') ? 'selected' : ''; ?>>High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="repeat_type" class="form-label">Repeat</label>
                    <select name="repeat_type" id="repeat_type" class="form-select">
                        <option value="none" <?php echo (($_POST['repeat_type'] ?? '') === 'none') ? 'selected' : ''; ?>>No Repeat</option>
                        <option value="daily" <?php echo (($_POST['repeat_type'] ?? '') === 'daily') ? 'selected' : ''; ?>>Daily</option>
                        <option value="weekly" <?php echo (($_POST['repeat_type'] ?? '') === 'weekly') ? 'selected' : ''; ?>>Weekly</option>
                        <option value="monthly" <?php echo (($_POST['repeat_type'] ?? '') === 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                        <option value="yearly" <?php echo (($_POST['repeat_type'] ?? '') === 'yearly') ? 'selected' : ''; ?>>Yearly</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="category" class="form-label">Category</label>
                <input type="text" name="category" id="category" class="form-input" placeholder="e.g., Health, Work, Personal" value="<?php echo htmlspecialchars($_POST['category'] ?? ''); ?>" list="categoryList">
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
                <button type="submit" class="btn btn-primary">Save Reminder</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
