<?php
/**
 * TaskNest - Expense Manager Module
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

requireLogin($auth);

$user_id = $auth->getUserId();
$page_title = 'Expenses';
$additional_css = ['expenses.css'];
$additional_js = ['expenses.js'];

$filters = [
    'search' => trim($_GET['search'] ?? ''),
    'type' => $_GET['type'] ?? '',
    'category' => $_GET['category'] ?? '',
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? '',
    'page' => max(1, (int) ($_GET['page'] ?? 1))
];

$categories = getExpenseCategories($mysqli, $user_id);
$expensesData = getExpensesForList($mysqli, $user_id, $filters);
$expenseStats = getExpenseStats($mysqli, $user_id);
$incomeExpenseChart = getIncomeExpenseChartData($mysqli, $user_id, 6);
$categoryBreakdownChart = getCategoryBreakdownData($mysqli, $user_id);

include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="expenses-page">
    <div class="expenses-toolbar">
        <div>
            <h1 class="expenses-title">Expense Manager</h1>
            <p class="expenses-subtitle">Track income, expenses, and budgets at a glance.</p>
        </div>
        <div class="expenses-toolbar-actions">
            <div class="export-actions">
                <a href="expenses.php?action=export_csv&date_from=<?php echo htmlspecialchars($filters['date_from']); ?>&date_to=<?php echo htmlspecialchars($filters['date_to']); ?>" class="btn btn-secondary">Export CSV</a>
            </div>
            <a href="<?php echo SITE_URL; ?>/modules/expenses/expenses-categories.php" class="btn btn-secondary">Manage Categories</a>
            <a href="<?php echo SITE_URL; ?>/modules/expenses/expenses-add.php" class="btn btn-primary">Add Transaction</a>
        </div>
    </div>

    <div class="expenses-summary">
        <div class="summary-card income">
            <span class="summary-label">Income (This Month)</span>
            <strong>$<?php echo number_format($expenseStats['total_income'], 2); ?></strong>
        </div>
        <div class="summary-card expense">
            <span class="summary-label">Expenses (This Month)</span>
            <strong>$<?php echo number_format($expenseStats['total_expense'], 2); ?></strong>
        </div>
        <div class="summary-card balance">
            <span class="summary-label">Balance</span>
            <strong>$<?php echo number_format($expenseStats['balance'], 2); ?></strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Transactions</span>
            <strong><?php echo (int) $expensesData['total']; ?></strong>
        </div>
    </div>

    <div class="expenses-tabs">
        <button class="expenses-tab active" data-tab="transactions">Transactions</button>
        <button class="expenses-tab" data-tab="charts">Charts</button>
        <button class="expenses-tab" data-tab="budgets">Budgets</button>
    </div>

    <!-- Transactions Tab -->
    <div class="tab-content active" id="tab-transactions">
        <div class="expenses-controls">
            <button class="filter-toggle-btn" id="filterToggleBtn" type="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
                <span>Filters</span>
            </button>
            <form class="expenses-filters" id="expensesFilterForm" method="get">
                <input type="hidden" name="tab" value="transactions">
                <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($filters['search']); ?>">
                <select name="type" class="form-control">
                    <option value="">All Types</option>
                    <option value="income" <?php echo $filters['type'] === 'income' ? 'selected' : ''; ?>>Income</option>
                    <option value="expense" <?php echo $filters['type'] === 'expense' ? 'selected' : ''; ?>>Expense</option>
                </select>
                <select name="category" class="form-control">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo (int) $cat['id']; ?>" <?php echo (string) $filters['category'] === (string) $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($filters['date_from']); ?>" placeholder="From">
                <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($filters['date_to']); ?>" placeholder="To">
                <button class="btn btn-secondary" type="submit">Apply</button>
            </form>
        </div>

        <div class="transaction-list" id="transactionList">
            <?php if (!empty($expensesData['expenses'])): ?>
                <?php foreach ($expensesData['expenses'] as $exp): ?>
                    <div class="transaction-item" data-expense-id="<?php echo (int) $exp['id']; ?>">
                        <div class="transaction-info">
                            <div class="transaction-icon <?php echo htmlspecialchars($exp['type']); ?>">
                                <?php echo $exp['type'] === 'income' ? '&#x2191;' : '&#x2193;'; ?>
                            </div>
                            <div class="transaction-details">
                                <h4><?php echo htmlspecialchars($exp['title']); ?></h4>
                                <div class="transaction-meta">
                                    <?php echo htmlspecialchars($exp['category_name'] ?? 'Uncategorized'); ?>
                                    <?php if (!empty($exp['notes'])): ?> &middot; <?php echo htmlspecialchars(mb_strimwidth($exp['notes'], 0, 50, '...')); ?><?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="transaction-amount">
                            <div class="amount <?php echo htmlspecialchars($exp['type']); ?>"><?php echo $exp['type'] === 'income' ? '+' : '-'; ?>$<?php echo number_format($exp['amount'], 2); ?></div>
                            <div class="transaction-date"><?php echo htmlspecialchars($exp['transaction_date']); ?></div>
                        </div>
                        <div class="transaction-actions">
                            <a class="btn btn-secondary btn-sm" href="<?php echo SITE_URL; ?>/modules/expenses/expenses-edit.php?id=<?php echo (int) $exp['id']; ?>">Edit</a>
                            <button class="btn btn-danger btn-sm" type="button" onclick="ConfirmModal.show('Delete Transaction', 'Are you sure?', function(){ deleteExpense(<?php echo (int) $exp['id']; ?>); })">Delete</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">&#x1F4B0;</div>
                    <h3>No transactions yet</h3>
                    <p>Record your first income or expense.</p>
                    <a href="<?php echo SITE_URL; ?>/modules/expenses/expenses-add.php" class="btn btn-primary">Add Transaction</a>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($expensesData['total_pages'] > 1): ?>
            <div class="pagination">
                <?php for ($p = 1; $p <= $expensesData['total_pages']; $p++): ?>
                    <a class="page-link <?php echo $p === $expensesData['page'] ? 'active' : ''; ?>" href="<?php echo buildExpensePaginationUrl($filters, $p); ?>"><?php echo (int) $p; ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Charts Tab -->
    <div class="tab-content" id="tab-charts">
        <div class="expenses-charts">
            <div class="chart-panel">
                <h3>Income vs Expenses</h3>
                <div style="height:280px;position:relative;">
                    <canvas id="incomeExpenseChart" data-labels="<?php echo htmlspecialchars(json_encode($incomeExpenseChart['labels'])); ?>" data-income="<?php echo htmlspecialchars(json_encode($incomeExpenseChart['income'])); ?>" data-expenses="<?php echo htmlspecialchars(json_encode($incomeExpenseChart['expenses'])); ?>"></canvas>
                </div>
            </div>
            <div class="chart-panel">
                <h3>Expense Breakdown</h3>
                <div style="height:280px;position:relative;">
                    <canvas id="categoryPieChart" data-labels="<?php echo htmlspecialchars(json_encode($categoryBreakdownChart['labels'])); ?>" data-values="<?php echo htmlspecialchars(json_encode($categoryBreakdownChart['values'])); ?>"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Budgets Tab -->
    <div class="tab-content" id="tab-budgets">
        <div style="margin-bottom:1rem;">
            <button class="btn btn-primary" type="button" id="openBudgetModal">Add Budget</button>
        </div>
        <div class="budgets-grid" id="budgetsContainer"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var toggleBtn = document.getElementById('filterToggleBtn');
    var filterForm = document.getElementById('expensesFilterForm');
    if (toggleBtn && filterForm) {
        toggleBtn.addEventListener('click', function() {
            filterForm.classList.toggle('open');
            toggleBtn.classList.toggle('active');
        });
    }
});
</script>
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
