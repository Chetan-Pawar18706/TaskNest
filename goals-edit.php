<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin($auth);

$user_id = $auth->getUserId();
ensureHabitGoalShoppingTablesExist($mysqli);

$goal_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($goal_id <= 0) {
    redirect(SITE_URL . '/goals.php');
}

$stmt = safePrepare($mysqli, 'SELECT * FROM goals WHERE id = ? AND user_id = ? AND is_deleted = 0');
$stmt->bind_param('ii', $goal_id, $user_id);
$stmt->execute();
$goal = $stmt->get_result()->fetch_assoc();

if (!$goal) {
    $_SESSION['flash_error'] = 'Goal not found.';
    redirect(SITE_URL . '/goals.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCsrfToken($csrf)) {
        $_SESSION['flash_error'] = 'Invalid CSRF token. Please try again.';
        redirect(SITE_URL . '/goals-edit.php?id=' . $goal_id);
    }

    $result = saveGoalHandler($mysqli, $user_id, $_POST);
    if ($result['success']) {
        $_SESSION['flash_success'] = $result['message'];
        redirect(SITE_URL . '/goals.php');
    } else {
        $_SESSION['flash_error'] = $result['message'];
        redirect(SITE_URL . '/goals-edit.php?id=' . $goal_id);
    }
}

$page_title = 'Edit Goal';
$additional_css = ['goals.css'];
include __DIR__ . '/includes/header.php';

$flash_error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);

$title = $_POST['title'] ?? $goal['title'];
$description = $_POST['description'] ?? $goal['description'];
$categoryId = $_POST['category_id'] ?? $goal['category'];
$target_value = $_POST['target_value'] ?? $goal['target_value'];
$current_value = $_POST['current_value'] ?? $goal['current_value'];
$unit = $_POST['unit'] ?? $goal['unit'];
$start_date = $_POST['start_date'] ?? $goal['start_date'];
$due_date = $_POST['due_date'] ?? $goal['due_date'];

$goalCategories = getGoalCategories($mysqli, $user_id);
?>

<div class="goals-page">
    <div class="goals-toolbar">
        <div>
            <a href="<?php echo SITE_URL; ?>/goals.php" class="btn btn-secondary">&larr; Back to Goals</a>
        </div>
        <div>
            <h1 class="goals-title">Edit Goal</h1>
            <p class="goals-subtitle">Update goal details.</p>
        </div>
    </div>

    <?php if ($flash_error): ?>
        <div class="alert alert-danger" style="padding:1rem;border-radius:var(--radius-lg);background:#fef2f2;color:#dc2626;border:1px solid #fecaca;margin-bottom:1rem;">
            <?php echo htmlspecialchars($flash_error); ?>
        </div>
    <?php endif; ?>

    <div style="background:var(--surface);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:1.25rem;box-shadow:var(--shadow-sm);max-width:720px;">
        <form method="post" action="<?php echo SITE_URL; ?>/goals-edit.php?id=<?php echo $goal_id; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <input type="hidden" name="action" value="save_goal">
            <input type="hidden" name="goal_id" value="<?php echo $goal_id; ?>">

            <div class="form-group">
                <label for="title">Title <span style="color:#dc2626;">*</span></label>
                <input type="text" id="title" name="title" class="form-control" required placeholder="e.g. Run a marathon" value="<?php echo htmlspecialchars($title); ?>">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3" placeholder="Describe the goal..."><?php echo htmlspecialchars($description); ?></textarea>
            </div>

            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" class="form-control">
                    <option value="">No Category</option>
                    <?php foreach ($goalCategories as $cat): ?>
                        <option value="<?php echo (int) $cat['id']; ?>" <?php echo $categoryId == $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="target_value">Target Value</label>
                    <input type="number" id="target_value" name="target_value" class="form-control" step="0.01" min="0" placeholder="0" value="<?php echo htmlspecialchars($target_value); ?>">
                </div>

                <div class="form-group">
                    <label for="current_value">Current Value</label>
                    <input type="number" id="current_value" name="current_value" class="form-control" step="0.01" min="0" placeholder="0" value="<?php echo htmlspecialchars($current_value); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="unit">Unit</label>
                <input type="text" id="unit" name="unit" class="form-control" placeholder="e.g. km, hours, dollars" value="<?php echo htmlspecialchars($unit); ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="start_date">Start Date <span style="color:#dc2626;">*</span></label>
                    <input type="date" id="start_date" name="start_date" class="form-control" required value="<?php echo htmlspecialchars($start_date); ?>">
                </div>

                <div class="form-group">
                    <label for="due_date">Due Date</label>
                    <input type="date" id="due_date" name="due_date" class="form-control" value="<?php echo htmlspecialchars($due_date); ?>">
                </div>
            </div>

            <div style="display:flex;gap:0.75rem;margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?php echo SITE_URL; ?>/goals.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
