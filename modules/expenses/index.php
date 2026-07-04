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
            <button class="btn btn-secondary" type="button" id="openExpenseCategoryModal">Manage Categories</button>
            <button class="btn btn-primary" type="button" id="openExpenseModal">Add Transaction</button>
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
                            <button class="btn btn-secondary btn-sm" type="button" data-action="edit" data-id="<?php echo (int) $exp['id']; ?>">Edit</button>
                            <button class="btn btn-danger btn-sm" type="button" data-action="delete" data-id="<?php echo (int) $exp['id']; ?>">Delete</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">&#x1F4B0;</div>
                    <h3>No transactions yet</h3>
                    <p>Record your first income or expense.</p>
                    <button class="btn btn-primary" type="button" id="emptyStateAddExpense">Add Transaction</button>
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
                <canvas id="incomeExpenseChart"></canvas>
            </div>
            <div class="chart-panel">
                <h3>Expense Breakdown</h3>
                <canvas id="categoryPieChart"></canvas>
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

<!-- Transaction Modal -->
<div class="modal" id="expenseModal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="expenseModal"></div>
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 id="expenseModalTitle">Add Transaction</h3>
            <button class="modal-close" type="button" data-close-modal="expenseModal">&times;</button>
        </div>
        <form id="expenseForm" class="modal-body">
            <input type="hidden" name="expense_id" id="expenseId">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <div class="type-toggle" id="typeToggle">
                <button type="button" class="active" data-type="expense">Expense</button>
                <button type="button" data-type="income">Income</button>
            </div>
            <input type="hidden" name="type" id="expenseType" value="expense">
            <div class="form-group">
                <label for="expenseTitle">Title</label>
                <input type="text" id="expenseTitle" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="expenseAmount">Amount ($)</label>
                <input type="number" id="expenseAmount" name="amount" class="form-control" step="0.01" min="0.01" required>
            </div>
            <div class="form-group">
                <label for="expenseCategory">Category</label>
                <select id="expenseCategory" name="category_id" class="form-control"></select>
            </div>
            <div class="form-group">
                <label for="expenseDate">Date</label>
                <input type="date" id="expenseDate" name="transaction_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="form-group">
                <label for="expenseNotes">Notes</label>
                <textarea id="expenseNotes" name="notes" class="form-control" rows="2"></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-close-modal="expenseModal">Cancel</button>
                <button class="btn btn-primary" type="submit">Save Transaction</button>
            </div>
        </form>
    </div>
</div>

<!-- Category Modal -->
<div class="modal" id="expenseCategoryModal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="expenseCategoryModal"></div>
    <div class="modal-dialog">
        <div class="modal-header">
            <h3>Manage Categories</h3>
            <button class="modal-close" type="button" data-close-modal="expenseCategoryModal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="expenseCategoryForm">
                <input type="hidden" name="category_id" id="expCatId">
                <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
                <div class="form-group">
                    <label for="expCatName">Name</label>
                    <input type="text" id="expCatName" name="name" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="expCatColor">Color</label>
                        <input type="color" id="expCatColor" name="color" class="form-control" value="#6366f1">
                    </div>
                    <div class="form-group">
                        <label for="expCatType">Type</label>
                        <select id="expCatType" name="type" class="form-control">
                            <option value="expense">Expense</option>
                            <option value="income">Income</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-close-modal="expenseCategoryModal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Save Category</button>
                </div>
            </form>
            <div class="expense-category-list" id="expCatList"></div>
        </div>
    </div>
</div>

<!-- Budget Modal -->
<div class="modal" id="budgetModal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="budgetModal"></div>
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 id="budgetModalTitle">Add Budget</h3>
            <button class="modal-close" type="button" data-close-modal="budgetModal">&times;</button>
        </div>
        <form id="budgetForm" class="modal-body">
            <input type="hidden" name="budget_id" id="budgetId">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <div class="form-group">
                <label for="budgetAmount">Budget Amount ($)</label>
                <input type="number" id="budgetAmount" name="amount" class="form-control" step="0.01" min="0.01" required>
            </div>
            <div class="form-group">
                <label for="budgetCategory">Category (leave empty for total budget)</label>
                <select id="budgetCategory" name="category_id" class="form-control"></select>
            </div>
            <div class="form-group">
                <label for="budgetPeriod">Period</label>
                <select id="budgetPeriod" name="period" class="form-control">
                    <option value="monthly">Monthly</option>
                    <option value="weekly">Weekly</option>
                    <option value="yearly">Yearly</option>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="budgetStartDate">Start Date</label>
                    <input type="date" id="budgetStartDate" name="start_date" class="form-control" value="<?php echo date('Y-m-01'); ?>">
                </div>
                <div class="form-group">
                    <label for="budgetEndDate">End Date (optional)</label>
                    <input type="date" id="budgetEndDate" name="end_date" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-close-modal="budgetModal">Cancel</button>
                <button class="btn btn-primary" type="submit">Save Budget</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal" id="expenseConfirmModal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="expenseConfirmModal"></div>
    <div class="modal-dialog modal-sm">
        <div class="modal-header">
            <h3 id="expConfirmTitle">Confirm</h3>
            <button class="modal-close" type="button" data-close-modal="expenseConfirmModal">&times;</button>
        </div>
        <div class="modal-body">
            <p id="expConfirmBody">Are you sure?</p>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-close-modal="expenseConfirmModal">Cancel</button>
                <button class="btn btn-danger" type="button" id="expConfirmActionBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.expenseNestInitialFilters = <?php echo json_encode($filters, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
</script>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
