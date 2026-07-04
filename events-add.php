<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin($auth);

$user_id = $auth->getUserId();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCsrfToken($csrf)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $result = saveCalendarEventHandler($mysqli, $user_id, $_POST);
        if ($result['success']) {
            $_SESSION['flash_success'] = $result['message'];
            redirect(SITE_URL . '/dashboard.php');
        } else {
            $errors[] = $result['message'];
        }
    }
}

$page_title = 'Add Event';
include __DIR__ . '/includes/header.php';
?>

<div class="settings-container" style="max-width:600px;margin:0 auto;">
    <div class="card" style="padding:1.5rem;">
        <h2 style="margin:0 0 1rem;">Add Event</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" style="padding:0.75rem;border-radius:var(--radius-lg);background:#fef2f2;color:#dc2626;border:1px solid #fecaca;margin-bottom:1rem;">
                <?php foreach ($errors as $e): ?>
                    <p style="margin:0;"><?php echo htmlspecialchars($e); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?php echo SITE_URL; ?>/events-add.php">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">

            <div class="form-group">
                <label for="title">Event Title <span style="color:#dc2626;">*</span></label>
                <input type="text" id="title" name="title" class="form-control" required placeholder="e.g. Team meeting" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="event_date">Date <span style="color:#dc2626;">*</span></label>
                <input type="date" id="event_date" name="event_date" class="form-control" required value="<?php echo htmlspecialchars($_POST['event_date'] ?? date('Y-m-d')); ?>">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3" placeholder="Optional details..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>

            <div style="display:flex;gap:0.5rem;margin-top:1rem;">
                <button type="submit" class="btn btn-primary" style="height:36px;font-size:0.85rem;">Create Event</button>
                <a href="<?php echo SITE_URL; ?>/dashboard.php" class="btn btn-secondary" style="height:36px;font-size:0.85rem;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
