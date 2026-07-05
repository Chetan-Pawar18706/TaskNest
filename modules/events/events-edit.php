<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin($auth);

$user_id = $auth->getUserId();
$errors = [];
$event_id = (int) ($_GET['id'] ?? 0);

if ($event_id <= 0) { redirect(SITE_URL . '/dashboard.php'); }

$stmt = safePrepare($mysqli, "SELECT * FROM calendar_events WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $event_id, $user_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) { redirect(SITE_URL . '/dashboard.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCsrfToken($csrf)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        if (!empty($_POST['delete_event'])) {
            $result = deleteCalendarEventHandler($mysqli, $user_id, $_POST);
            if ($result['success']) {
                $_SESSION['flash_success'] = $result['message'];
                redirect(SITE_URL . '/dashboard.php');
            } else {
                $errors[] = $result['message'];
            }
        } else {
            $_POST['event_id'] = $event_id;
            $result = saveCalendarEventHandler($mysqli, $user_id, $_POST);
            if ($result['success']) {
                $_SESSION['flash_success'] = $result['message'];
                redirect(SITE_URL . '/dashboard.php');
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

$page_title = 'Edit Event';
include __DIR__ . '/../../includes/header.php';
?>

<div class="settings-container" style="max-width:600px;margin:0 auto;">
    <div class="card" style="padding:1.5rem;">
        <h2 style="margin:0 0 1rem;">Edit Event</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" style="padding:0.75rem;border-radius:var(--radius-lg);background:#fef2f2;color:#dc2626;border:1px solid #fecaca;margin-bottom:1rem;">
                <?php foreach ($errors as $e): ?>
                    <p style="margin:0;"><?php echo htmlspecialchars($e); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?php echo SITE_URL; ?>/modules/events/events-edit.php?id=<?php echo $event_id; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">

            <div class="form-group">
                <label for="title">Event Title <span style="color:#dc2626;">*</span></label>
                <input type="text" id="title" name="title" class="form-control" required placeholder="e.g. Team meeting" value="<?php echo htmlspecialchars($_POST['title'] ?? $event['title']); ?>">
            </div>

            <div class="form-group">
                <label for="event_date">Date <span style="color:#dc2626;">*</span></label>
                <input type="date" id="event_date" name="event_date" class="form-control" required value="<?php echo htmlspecialchars($_POST['event_date'] ?? $event['event_date']); ?>">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3" placeholder="Optional details..."><?php echo htmlspecialchars($_POST['description'] ?? $event['description']); ?></textarea>
            </div>

            <div style="display:flex;gap:0.5rem;margin-top:1rem;">
                <button type="submit" class="btn btn-primary" style="height:36px;font-size:0.85rem;">Save Changes</button>
                <button type="submit" name="delete_event" value="1" class="btn btn-danger" style="height:36px;font-size:0.85rem;" onclick="return confirm('Delete this event?')">Delete Event</button>
                <a href="<?php echo SITE_URL; ?>/dashboard.php" class="btn btn-secondary" style="height:36px;font-size:0.85rem;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
