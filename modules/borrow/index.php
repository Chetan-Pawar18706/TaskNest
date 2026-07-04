<?php
/**
 * TaskNest - Borrow & Lend Module
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

requireLogin($auth);

$user_id = $auth->getUserId();
$page_title = 'Borrow & Lend';
$additional_css = ['borrow.css'];
$additional_js = ['borrow.js'];

$filters = [
    'search' => trim($_GET['search'] ?? ''),
    'type' => $_GET['type'] ?? '',
    'status' => $_GET['status'] ?? '',
    'page' => max(1, (int) ($_GET['page'] ?? 1))
];

$borrowData = getBorrowItemsForList($mysqli, $user_id, $filters);
$borrowStats = getBorrowStats($mysqli, $user_id);

include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="borrow-page">
    <div class="borrow-toolbar">
        <div>
            <h1 class="borrow-title">Borrow & Lend</h1>
            <p class="borrow-subtitle">Track items and money you've borrowed or lent to others.</p>
        </div>
        <div class="borrow-toolbar-actions">
            <button class="btn btn-primary" type="button" id="openBorrowModal">Add Record</button>
        </div>
    </div>

    <div class="borrow-summary">
        <div class="summary-card">
            <span class="summary-label">Total Records</span>
            <strong><?php echo (int) $borrowStats['total']; ?></strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Pending Borrowed</span>
            <strong><?php echo (int) $borrowStats['pending_borrowed']; ?></strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Pending Lent</span>
            <strong><?php echo (int) $borrowStats['pending_lent']; ?></strong>
        </div>
        <div class="summary-card overdue">
            <span class="summary-label">Overdue</span>
            <strong><?php echo (int) $borrowStats['overdue']; ?></strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Total Lent</span>
            <strong>$<?php echo number_format($borrowStats['total_lent'], 2); ?></strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Total Borrowed</span>
            <strong>$<?php echo number_format($borrowStats['total_borrowed'], 2); ?></strong>
        </div>
    </div>

    <div class="borrow-controls">
        <form class="borrow-filters" id="borrowFilterForm" method="get">
            <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($filters['search']); ?>">
            <select name="type" class="form-control">
                <option value="">All Types</option>
                <option value="borrowed" <?php echo $filters['type'] === 'borrowed' ? 'selected' : ''; ?>>Borrowed</option>
                <option value="lent" <?php echo $filters['type'] === 'lent' ? 'selected' : ''; ?>>Lent</option>
            </select>
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="returned" <?php echo $filters['status'] === 'returned' ? 'selected' : ''; ?>>Returned</option>
                <option value="overdue" <?php echo $filters['status'] === 'overdue' ? 'selected' : ''; ?>>Overdue</option>
            </select>
            <button class="btn btn-secondary" type="submit">Apply</button>
        </form>
    </div>

    <div class="borrow-list" id="borrowList">
        <?php if (!empty($borrowData['items'])): ?>
            <?php foreach ($borrowData['items'] as $item): ?>
                <div class="borrow-card" data-borrow-id="<?php echo (int) $item['id']; ?>">
                    <div class="borrow-card-header">
                        <div>
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <span class="borrow-type-badge <?php echo htmlspecialchars($item['type']); ?>"><?php echo $item['type'] === 'lent' ? 'Lent' : 'Borrowed'; ?></span>
                            <span class="borrow-status-badge <?php echo htmlspecialchars($item['status']); ?>"><?php echo ucfirst(htmlspecialchars($item['status'])); ?></span>
                        </div>
                        <?php if ($item['item_type'] === 'money' && $item['amount'] > 0): ?>
                            <div class="borrow-amount <?php echo htmlspecialchars($item['type']); ?>">
                                $<?php echo number_format($item['amount'], 2); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="borrow-card-details">
                        <div class="borrow-detail-item">
                            <div class="detail-label">Person</div>
                            <div class="detail-value"><?php echo htmlspecialchars($item['person_name']); ?></div>
                        </div>
                        <?php if (!empty($item['person_contact'])): ?>
                            <div class="borrow-detail-item">
                                <div class="detail-label">Contact</div>
                                <div class="detail-value"><?php echo htmlspecialchars($item['person_contact']); ?></div>
                            </div>
                        <?php endif; ?>
                        <div class="borrow-detail-item">
                            <div class="detail-label">Borrow Date</div>
                            <div class="detail-value"><?php echo htmlspecialchars($item['borrow_date']); ?></div>
                        </div>
                        <?php if (!empty($item['return_date'])): ?>
                            <div class="borrow-detail-item">
                                <div class="detail-label">Expected Return</div>
                                <div class="detail-value"><?php echo htmlspecialchars($item['return_date']); ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($item['actual_return_date'])): ?>
                            <div class="borrow-detail-item">
                                <div class="detail-label">Actual Return</div>
                                <div class="detail-value"><?php echo htmlspecialchars($item['actual_return_date']); ?></div>
                            </div>
                        <?php endif; ?>
                        <div class="borrow-detail-item">
                            <div class="detail-label">Item Type</div>
                            <div class="detail-value"><?php echo $item['item_type'] === 'money' ? 'Money' : 'Item'; ?></div>
                        </div>
                    </div>
                    <?php if (!empty($item['description'])): ?>
                        <p style="font-size:0.85rem;color:var(--text-secondary);margin:0.5rem 0;"><?php echo htmlspecialchars($item['description']); ?></p>
                    <?php endif; ?>
                    <?php if ($item['status'] === 'overdue'): ?>
                        <div class="borrow-overdue-warning">&#x26A0; Overdue - return date was <?php echo htmlspecialchars($item['return_date']); ?></div>
                    <?php endif; ?>
                    <div class="borrow-card-actions">
                        <button class="btn btn-secondary btn-sm" type="button" data-action="edit" data-id="<?php echo (int) $item['id']; ?>">Edit</button>
                        <?php if ($item['status'] !== 'returned'): ?>
                            <button class="btn btn-secondary btn-sm" type="button" data-action="mark_returned" data-id="<?php echo (int) $item['id']; ?>">Mark Returned</button>
                        <?php endif; ?>
                        <button class="btn btn-danger btn-sm" type="button" data-action="delete" data-id="<?php echo (int) $item['id']; ?>">Delete</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">&#x1F4B5;</div>
                <h3>No records yet</h3>
                <p>Start tracking borrowed or lent items.</p>
                <button class="btn btn-primary" type="button" id="emptyStateAddBorrow">Add Record</button>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($borrowData['total_pages'] > 1): ?>
        <div class="pagination">
            <?php for ($p = 1; $p <= $borrowData['total_pages']; $p++): ?>
                <a class="page-link <?php echo $p === $borrowData['page'] ? 'active' : ''; ?>" href="<?php echo buildBorrowPaginationUrl($filters, $p); ?>"><?php echo (int) $p; ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Borrow Modal -->
<div class="modal" id="borrowModal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="borrowModal"></div>
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 id="borrowModalTitle">Add Record</h3>
            <button class="modal-close" type="button" data-close-modal="borrowModal">&times;</button>
        </div>
        <form id="borrowForm" class="modal-body">
            <input type="hidden" name="borrow_id" id="borrowId">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <div class="form-group">
                <label for="borrowType">Type</label>
                <select id="borrowType" name="type" class="form-control">
                    <option value="borrowed">I Borrowed</option>
                    <option value="lent">I Lent</option>
                </select>
            </div>
            <div class="form-group">
                <label for="borrowItemType">Item Type</label>
                <select id="borrowItemType" name="item_type" class="form-control">
                    <option value="item">Physical Item</option>
                    <option value="money">Money</option>
                </select>
            </div>
            <div class="form-group">
                <label for="borrowTitle">Title / Item Name</label>
                <input type="text" id="borrowTitle" name="title" class="form-control" required>
            </div>
            <div class="form-group" id="borrowAmountGroup">
                <label for="borrowAmount">Amount ($)</label>
                <input type="number" id="borrowAmount" name="amount" class="form-control" step="0.01" min="0">
            </div>
            <div class="form-group">
                <label for="borrowPerson">Person Name</label>
                <input type="text" id="borrowPerson" name="person_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="borrowContact">Contact (optional)</label>
                <input type="text" id="borrowContact" name="person_contact" class="form-control">
            </div>
            <div class="form-group">
                <label for="borrowDescription">Description</label>
                <textarea id="borrowDescription" name="description" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="borrowDate">Borrow Date</label>
                    <input type="date" id="borrowDate" name="borrow_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label for="borrowReturnDate">Return Date</label>
                    <input type="date" id="borrowReturnDate" name="return_date" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-close-modal="borrowModal">Cancel</button>
                <button class="btn btn-primary" type="submit">Save Record</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal" id="borrowConfirmModal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="borrowConfirmModal"></div>
    <div class="modal-dialog modal-sm">
        <div class="modal-header">
            <h3 id="borrowConfirmTitle">Confirm</h3>
            <button class="modal-close" type="button" data-close-modal="borrowConfirmModal">&times;</button>
        </div>
        <div class="modal-body">
            <p id="borrowConfirmBody">Are you sure?</p>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-close-modal="borrowConfirmModal">Cancel</button>
                <button class="btn btn-danger" type="button" id="borrowConfirmActionBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
