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
            <a class="btn btn-secondary" href="<?php echo SITE_URL; ?>/shopping-categories.php">Manage Categories</a>
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

    <a class="btn btn-primary" href="<?php echo SITE_URL; ?>/shopping-add.php" style="margin-bottom:1rem;display:inline-block;">Add Item</a>

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
                        <button class="btn btn-secondary btn-sm" type="button" onclick="window.location.href = siteUrl + '/shopping-edit.php?id=<?php echo (int) $item['id']; ?>'">Edit</button>
                        <button class="btn btn-danger btn-sm" type="button" onclick="ConfirmModal.show('Delete Item', 'Are you sure you want to delete this item?', function() { deleteShoppingItem(<?php echo (int) $item['id']; ?>); })">Delete</button>
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



<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
