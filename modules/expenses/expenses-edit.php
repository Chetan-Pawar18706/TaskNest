<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin($auth);

$user_id = $auth->getUserId();
$expense_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($expense_id <= 0) {
    header('Location: ' . SITE_URL . '/expenses.php');
    exit;
}

$stmt = safePrepare($mysqli, "SELECT e.*, ec.name AS category_name FROM expenses e LEFT JOIN expense_categories ec ON ec.id = e.category_id WHERE e.id = ? AND e.user_id = ? AND e.is_deleted = 0");
$stmt->bind_param('ii', $expense_id, $user_id);
$stmt->execute();
$expense = $stmt->get_result()->fetch_assoc();

if (!$expense) {
    header('Location: ' . SITE_URL . '/expenses.php');
    exit;
}

$categories = getExpenseCategories($mysqli, $user_id);
$csrf_token = $auth->generateCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $csrf = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCsrfToken($csrf)) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
        exit;
    }

    $result = saveExpenseHandler($mysqli, $user_id, $_POST);
    echo json_encode($result);
    exit;
}

$page_title = 'Edit Transaction';
$additional_css = ['expenses.css'];
include __DIR__ . '/../../includes/header.php';
?>

<div class="expenses-page">
    <div class="expenses-toolbar">
        <div>
            <a href="<?php echo SITE_URL; ?>/expenses.php" class="btn btn-secondary btn-sm">&larr; Back to Expenses</a>
        </div>
    </div>

    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:1.25rem;max-width:640px;">
        <h1 style="font-size:1.4rem;font-weight:700;color:var(--text-primary);margin:0 0 1.25rem;">Edit Transaction</h1>

        <form id="editExpenseForm" method="post" action="<?php echo SITE_URL; ?>/expenses.php">
            <input type="hidden" name="action" value="save_expense">
            <input type="hidden" name="expense_id" value="<?php echo (int) $expense['id']; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="type-toggle" id="typeToggle" style="margin-bottom:1rem;">
                <button type="button" class="active" data-type="expense">Expense</button>
                <button type="button" data-type="income">Income</button>
            </div>
            <input type="hidden" name="type" id="expenseType" value="<?php echo htmlspecialchars($expense['type']); ?>">

            <div class="form-group">
                <label for="title">Title <span style="color:#ef4444;">*</span></label>
                <input type="text" id="title" name="title" class="form-control" required value="<?php echo htmlspecialchars($expense['title']); ?>">
            </div>

            <div class="form-group">
                <label for="amount">Amount ($) <span style="color:#ef4444;">*</span></label>
                <input type="number" id="amount" name="amount" class="form-control" step="0.01" min="0.01" required value="<?php echo htmlspecialchars($expense['amount']); ?>">
            </div>

            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" class="form-control">
                    <option value="">-- No Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo (int) $cat['id']; ?>" data-type="<?php echo htmlspecialchars($cat['type']); ?>" <?php echo ((int) ($expense['category_id'] ?? 0) === (int) $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="transaction_date">Date <span style="color:#ef4444;">*</span></label>
                <input type="date" id="transaction_date" name="transaction_date" class="form-control" required value="<?php echo htmlspecialchars($expense['transaction_date']); ?>">
            </div>

            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($expense['notes'] ?? ''); ?></textarea>
            </div>

            <div class="form-group checkbox">
                <input type="checkbox" id="is_recurring" name="is_recurring" value="1" <?php echo !empty($expense['is_recurring']) ? 'checked' : ''; ?>>
                <label for="is_recurring">Recurring Transaction</label>
            </div>

            <div class="form-group" id="recurringPeriodGroup" style="display:<?php echo !empty($expense['is_recurring']) ? '' : 'none'; ?>;">
                <label for="recurring_period">Recurring Period</label>
                <select id="recurring_period" name="recurring_period" class="form-control">
                    <option value="daily" <?php echo ($expense['recurring_period'] ?? '') === 'daily' ? 'selected' : ''; ?>>Daily</option>
                    <option value="weekly" <?php echo ($expense['recurring_period'] ?? '') === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                    <option value="monthly" <?php echo ($expense['recurring_period'] ?? '') === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                </select>
            </div>

            <div style="display:flex;gap:0.75rem;margin-top:1.5rem;">
                <button type="submit" class="btn btn-primary">Update Transaction</button>
                <a href="<?php echo SITE_URL; ?>/expenses.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var typeToggle = document.getElementById('typeToggle');
    var typeInput = document.getElementById('expenseType');
    var catSelect = document.getElementById('category_id');
    var recurringCheck = document.getElementById('is_recurring');
    var recurringGroup = document.getElementById('recurringPeriodGroup');
    var form = document.getElementById('editExpenseForm');

    var currentType = typeInput.value;
    typeToggle.querySelectorAll('button').forEach(function(btn) {
        if (btn.getAttribute('data-type') === currentType) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    function filterCategories(type) {
        catSelect.querySelectorAll('option[data-type]').forEach(function(opt) {
            opt.style.display = (opt.getAttribute('data-type') === type || opt.value === '') ? '' : 'none';
        });
    }
    filterCategories(currentType);

    typeToggle.querySelectorAll('button').forEach(function(btn) {
        btn.addEventListener('click', function() {
            typeToggle.querySelectorAll('button').forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');
            var type = btn.getAttribute('data-type');
            typeInput.value = type;
            filterCategories(type);
        });
    });

    recurringCheck.addEventListener('change', function() {
        recurringGroup.style.display = recurringCheck.checked ? '' : 'none';
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(form);

        fetch('<?php echo SITE_URL; ?>/expenses.php', {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                window.location.href = '<?php echo SITE_URL; ?>/expenses.php';
            } else {
                alert(data.message || 'An error occurred.');
            }
        })
        .catch(function() {
            alert('Network error. Please try again.');
        });
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
