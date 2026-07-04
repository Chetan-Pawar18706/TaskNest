<?php
require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';
requireLogin($auth);

$user_id = $auth->getUserId();
$page_title = 'Shopping';
$additional_css = ['shopping.css'];
$additional_js = ['shopping.js'];

$filters = [
    'search' => trim($_GET['search'] ?? ''),
    'completed' => isset($_GET['completed']) ? 1 : 0,
    'page' => max(1, (int) ($_GET['page'] ?? 1))
];
$shoppingData = getShoppingForList($mysqli, $user_id, $filters);
$shopStats = getShoppingStats($mysqli, $user_id);

include dirname(__DIR__, 2) . '/includes/header.php';
?>
<div class="shopping-page">
    <div class="shopping-toolbar">
        <div>
            <h1 class="shopping-title">Shopping List</h1>
            <p class="shopping-subtitle">Plan purchases and track spending.</p>
        </div>
        <div class="shopping-toolbar-actions">
            <button class="btn btn-secondary" type="button" id="clearCompletedBtn">Clear Completed</button>
        </div>
    </div>

    <div class="shopping-summary">
        <div class="summary-card"><span class="summary-label">Total Items</span><strong><?php echo (int) $shopStats['total']; ?></strong></div>
        <div class="summary-card"><span class="summary-label">Pending</span><strong><?php echo (int) $shopStats['pending']; ?></strong></div>
        <div class="summary-card"><span class="summary-label">Completed</span><strong><?php echo (int) $shopStats['completed']; ?></strong></div>
        <div class="summary-card"><span class="summary-label">Estimated Total</span><strong>$<?php echo number_format($shopStats['estimated_total'], 2); ?></strong></div>
        <div class="summary-card"><span class="summary-label">Actual Spent</span><strong>$<?php echo number_format($shopStats['actual_total'], 2); ?></strong></div>
    </div>

    <form id="shoppingAddForm" class="shopping-add-form">
        <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
        <input type="text" id="shoppingItemName" name="name" class="form-control" placeholder="Item name..." required>
        <input type="number" id="shoppingItemQty" name="quantity" class="form-control" placeholder="Qty" value="1" min="1" style="max-width:80px;">
        <input type="number" id="shoppingItemPrice" name="estimated_price" class="form-control" placeholder="Price ($)" step="0.01" min="0" style="max-width:120px;">
        <input type="text" id="shoppingItemCategory" name="category" class="form-control" placeholder="Category" style="max-width:140px;">
        <button class="btn btn-primary" type="submit">Add</button>
    </form>

    <div class="shopping-controls">
        <form class="shopping-filters" method="get">
            <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($filters['search']); ?>">
            <label class="checkbox-inline"><input type="checkbox" name="completed" value="1" <?php echo $filters['completed'] ? 'checked' : ''; ?>> Show Completed</label>
            <button class="btn btn-secondary" type="submit">Apply</button>
        </form>
    </div>

    <div class="shopping-list" id="shoppingList">
        <?php if (!empty($shoppingData['items'])): ?>
            <?php foreach ($shoppingData['items'] as $item): ?>
                <div class="shopping-item <?php echo $item['is_completed'] ? 'completed' : ''; ?>">
                    <div class="shopping-item-left">
                        <input type="checkbox" class="shopping-checkbox" data-id="<?php echo (int) $item['id']; ?>" <?php echo $item['is_completed'] ? 'checked' : ''; ?>>
                        <div>
                            <div class="shopping-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="shopping-item-meta">
                                <?php if (!empty($item['category'])): ?><?php echo htmlspecialchars($item['category']); ?> &middot; <?php endif; ?>
                                <?php if (!empty($item['notes'])): ?><?php echo htmlspecialchars(mb_strimwidth($item['notes'], 0, 40, '...')); ?><?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($item['quantity'] > 1): ?>
                        <span class="shopping-item-qty">x<?php echo (int) $item['quantity']; ?></span>
                    <?php endif; ?>
                    <div class="shopping-item-price">
                        <?php if ($item['estimated_price'] > 0): ?>
                            <div class="estimated">$<?php echo number_format($item['estimated_price'], 2); ?></div>
                        <?php endif; ?>
                        <?php if ($item['actual_price'] > 0): ?>
                            <div class="actual">$<?php echo number_format($item['actual_price'], 2); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="shopping-item-actions">
                        <button class="btn btn-secondary btn-sm" type="button" data-action="edit" data-id="<?php echo (int) $item['id']; ?>">Edit</button>
                        <button class="btn btn-danger btn-sm" type="button" data-action="delete" data-id="<?php echo (int) $item['id']; ?>">Delete</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">&#x1F6D2;</div>
                <h3>Shopping list is empty</h3>
                <p>Add items above to start your list.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($shoppingData['items'])): ?>
        <div class="shopping-footer">
            <span class="total-label"><?php echo (int) $shopStats['pending']; ?> items remaining</span>
            <span class="total-amount">Est. $<?php echo number_format($shopStats['estimated_total'], 2); ?></span>
        </div>
    <?php endif; ?>
</div>

<!-- Edit Item Modal -->
<div class="modal" id="editItemModal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="editItemModal"></div>
    <div class="modal-dialog">
        <div class="modal-header">
            <h3>Edit Item</h3>
            <button class="modal-close" type="button" data-close-modal="editItemModal">&times;</button>
        </div>
        <form id="editItemForm" class="modal-body">
            <input type="hidden" name="item_id" id="editItemId">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <div class="form-group">
                <label for="editItemName">Name</label>
                <input type="text" id="editItemName" name="name" class="form-control" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="editItemQty">Quantity</label>
                    <input type="number" id="editItemQty" name="quantity" class="form-control" value="1" min="1">
                </div>
                <div class="form-group">
                    <label for="editItemPrice">Estimated Price ($)</label>
                    <input type="number" id="editItemPrice" name="estimated_price" class="form-control" step="0.01" min="0">
                </div>
            </div>
            <div class="form-group">
                <label for="editItemCategory">Category</label>
                <input type="text" id="editItemCategory" name="category" class="form-control">
            </div>
            <div class="form-group">
                <label for="editItemNotes">Notes</label>
                <textarea id="editItemNotes" name="notes" class="form-control" rows="2"></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-close-modal="editItemModal">Cancel</button>
                <button class="btn btn-primary" type="submit">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Confirm Delete Modal -->
<div class="modal" id="shopConfirmModal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="shopConfirmModal"></div>
    <div class="modal-dialog modal-sm">
        <div class="modal-header">
            <h3>Confirm Delete</h3>
            <button class="modal-close" type="button" data-close-modal="shopConfirmModal">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this item?</p>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-close-modal="shopConfirmModal">Cancel</button>
                <button class="btn btn-danger" type="button" id="shopConfirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
