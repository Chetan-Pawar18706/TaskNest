<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin($auth);

$user_id = $auth->getUserId();
ensureBorrowTablesExist($mysqli);

$borrow_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($borrow_id <= 0) {
    redirect(SITE_URL . '/borrow.php');
}

$stmt = safePrepare($mysqli, 'SELECT * FROM borrow_items WHERE id = ? AND user_id = ? AND is_deleted = 0');
$stmt->bind_param('ii', $borrow_id, $user_id);
$stmt->execute();
$record = $stmt->get_result()->fetch_assoc();

if (!$record) {
    $_SESSION['flash_error'] = 'Record not found.';
    redirect(SITE_URL . '/borrow.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCsrfToken($csrf)) {
        $_SESSION['flash_error'] = 'Invalid CSRF token. Please try again.';
        redirect(SITE_URL . '/modules/borrow/borrow-edit.php?id=' . $borrow_id);
    }

    $result = saveBorrowHandler($mysqli, $user_id, $_POST);
    if ($result['success']) {
        $_SESSION['flash_success'] = $result['message'];
        redirect(SITE_URL . '/borrow.php');
    } else {
        $_SESSION['flash_error'] = $result['message'];
        redirect(SITE_URL . '/modules/borrow/borrow-edit.php?id=' . $borrow_id);
    }
}

$page_title = 'Edit Borrow/Lend';
$additional_css = ['borrow.css'];
include __DIR__ . '/../../includes/header.php';

$flash_error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);

$title = $_POST['title'] ?? $record['title'];
$description = $_POST['description'] ?? $record['description'];
$type = $_POST['type'] ?? $record['type'];
$item_type = $_POST['item_type'] ?? $record['item_type'];
$amount = $_POST['amount'] ?? $record['amount'];
$person_name = $_POST['person_name'] ?? $record['person_name'];
$person_contact = $_POST['person_contact'] ?? $record['person_contact'];
$borrow_date = $_POST['borrow_date'] ?? $record['borrow_date'];
$return_date = $_POST['return_date'] ?? ($record['return_date'] ?? '');
?>

<div class="borrow-page">
    <div class="borrow-toolbar">
        <div>
            <a href="<?php echo SITE_URL; ?>/borrow.php" class="btn btn-secondary">&larr; Back to Borrow & Lend</a>
        </div>
        <div>
            <h1 class="borrow-title">Edit Borrow/Lend Record</h1>
            <p class="borrow-subtitle">Update record details.</p>
        </div>
    </div>

    <?php if ($flash_error): ?>
        <div class="alert alert-danger" style="padding:1rem;border-radius:var(--radius-lg);background:#fef2f2;color:#dc2626;border:1px solid #fecaca;margin-bottom:1rem;">
            <?php echo htmlspecialchars($flash_error); ?>
        </div>
    <?php endif; ?>

    <div style="background:var(--surface);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:1.25rem;box-shadow:var(--shadow-sm);max-width:720px;">
        <form method="post" action="<?php echo SITE_URL; ?>/modules/borrow/borrow-edit.php?id=<?php echo $borrow_id; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <input type="hidden" name="action" value="save_borrow">
            <input type="hidden" name="borrow_id" value="<?php echo $borrow_id; ?>">

            <div class="form-group">
                <label for="title">Title <span style="color:#dc2626;">*</span></label>
                <input type="text" id="title" name="title" class="form-control" required placeholder="e.g. Loan from John" value="<?php echo htmlspecialchars($title); ?>">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3" placeholder="Additional details..."><?php echo htmlspecialchars($description); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Type</label>
                    <div style="display:flex;gap:1rem;margin-top:0.5rem;">
                        <label style="display:flex;align-items:center;gap:0.4rem;cursor:pointer;">
                            <input type="radio" name="type" value="borrowed" <?php echo $type === 'borrowed' ? 'checked' : ''; ?>> Borrowed
                        </label>
                        <label style="display:flex;align-items:center;gap:0.4rem;cursor:pointer;">
                            <input type="radio" name="type" value="lent" <?php echo $type === 'lent' ? 'checked' : ''; ?>> Lent
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Item Type</label>
                    <div style="display:flex;gap:1rem;margin-top:0.5rem;">
                        <label style="display:flex;align-items:center;gap:0.4rem;cursor:pointer;">
                            <input type="radio" name="item_type" value="money" <?php echo $item_type === 'money' ? 'checked' : ''; ?>> Money
                        </label>
                        <label style="display:flex;align-items:center;gap:0.4rem;cursor:pointer;">
                            <input type="radio" name="item_type" value="item" <?php echo $item_type === 'item' ? 'checked' : ''; ?>> Item
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="amount">Amount</label>
                <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0" placeholder="0.00" value="<?php echo htmlspecialchars($amount); ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="person_name">Person Name <span style="color:#dc2626;">*</span></label>
                    <input type="text" id="person_name" name="person_name" class="form-control" required placeholder="e.g. John Doe" value="<?php echo htmlspecialchars($person_name); ?>">
                </div>

                <div class="form-group">
                    <label for="person_contact">Contact</label>
                    <input type="text" id="person_contact" name="person_contact" class="form-control" placeholder="e.g. Phone, Email" value="<?php echo htmlspecialchars($person_contact); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="borrow_date">Borrow Date <span style="color:#dc2626;">*</span></label>
                    <input type="date" id="borrow_date" name="borrow_date" class="form-control" required value="<?php echo htmlspecialchars($borrow_date); ?>">
                </div>

                <div class="form-group">
                    <label for="return_date">Return Date</label>
                    <input type="date" id="return_date" name="return_date" class="form-control" value="<?php echo htmlspecialchars($return_date); ?>">
                </div>
            </div>

            <div style="display:flex;gap:0.75rem;margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="<?php echo SITE_URL; ?>/borrow.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
