<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin($auth);

$user_id = $auth->getUserId();
ensureHabitGoalShoppingTablesExist($mysqli);

$habit_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($habit_id <= 0) {
    redirect(SITE_URL . '/habits.php');
}

$stmt = safePrepare($mysqli, 'SELECT * FROM habits WHERE id = ? AND user_id = ? AND is_deleted = 0');
$stmt->bind_param('ii', $habit_id, $user_id);
$stmt->execute();
$habit = $stmt->get_result()->fetch_assoc();

if (!$habit) {
    $_SESSION['flash_error'] = 'Habit not found.';
    redirect(SITE_URL . '/habits.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCsrfToken($csrf)) {
        $_SESSION['flash_error'] = 'Invalid CSRF token. Please try again.';
        redirect(SITE_URL . '/modules/habits/habits-edit.php?id=' . $habit_id);
    }

    $result = saveHabitHandler($mysqli, $user_id, $_POST);
    if ($result['success']) {
        $_SESSION['flash_success'] = $result['message'];
        redirect(SITE_URL . '/habits.php');
    } else {
        $_SESSION['flash_error'] = $result['message'];
        redirect(SITE_URL . '/modules/habits/habits-edit.php?id=' . $habit_id);
    }
}

$page_title = 'Edit Habit';
$additional_css = ['habits.css'];
include __DIR__ . '/../../includes/header.php';

$flash_error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);

$name = $_POST['name'] ?? $habit['name'];
$description = $_POST['description'] ?? $habit['description'];
$frequency = $_POST['frequency'] ?? $habit['frequency'];
$target_count = $_POST['target_count'] ?? $habit['target_count'];
$color = $_POST['color'] ?? $habit['color'];
$icon = $_POST['icon'] ?? ($habit['icon'] ?? '');
?>

<div class="habits-page">
    <div class="habits-toolbar">
        <div>
            <a href="<?php echo SITE_URL; ?>/habits.php" class="btn btn-secondary">&larr; Back to Habits</a>
        </div>
        <div>
            <h1 class="habits-title">Edit Habit</h1>
            <p class="habits-subtitle">Update habit details.</p>
        </div>
    </div>

    <?php if ($flash_error): ?>
        <div class="alert alert-danger" style="padding:1rem;border-radius:var(--radius-lg);background:#fef2f2;color:#dc2626;border:1px solid #fecaca;margin-bottom:1rem;">
            <?php echo htmlspecialchars($flash_error); ?>
        </div>
    <?php endif; ?>

    <div style="background:var(--surface);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:1.25rem;box-shadow:var(--shadow-sm);max-width:720px;">
        <form method="post" action="<?php echo SITE_URL; ?>/modules/habits/habits-edit.php?id=<?php echo $habit_id; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <input type="hidden" name="action" value="save_habit">
            <input type="hidden" name="habit_id" value="<?php echo $habit_id; ?>">

            <div class="form-group">
                <label for="name">Name <span style="color:#dc2626;">*</span></label>
                <input type="text" id="name" name="name" class="form-control" required placeholder="e.g. Read 30 minutes" value="<?php echo htmlspecialchars($name); ?>">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3" placeholder="Describe the habit..."><?php echo htmlspecialchars($description); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="frequency">Frequency</label>
                    <select id="frequency" name="frequency" class="form-control">
                        <option value="daily" <?php echo $frequency === 'daily' ? 'selected' : ''; ?>>Daily</option>
                        <option value="weekly" <?php echo $frequency === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                        <option value="monthly" <?php echo $frequency === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="target_count">Target Count</label>
                    <input type="number" id="target_count" name="target_count" class="form-control" min="1" value="<?php echo htmlspecialchars($target_count); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="color">Color</label>
                    <input type="color" id="color" name="color" class="form-control" value="<?php echo htmlspecialchars($color); ?>" style="height:42px;padding:4px;">
                </div>

                <div class="form-group">
                    <label for="icon">Icon</label>
                    <input type="text" id="icon" name="icon" class="form-control" placeholder="e.g. 📖, 💪, 🧘" value="<?php echo htmlspecialchars($icon); ?>">
                </div>
            </div>

            <div style="display:flex;gap:0.75rem;margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?php echo SITE_URL; ?>/habits.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
