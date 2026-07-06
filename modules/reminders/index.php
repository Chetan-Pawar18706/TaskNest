<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin($auth);

$user_id = $auth->getUserId();
$page_title = 'Reminders';
$additional_css = ['reminders.css'];

ensureReminderTableExists($mysqli);
$counts = getReminderCounts($mysqli, $user_id);
$categories = getReminderCategories($mysqli, $user_id);

include __DIR__ . '/../../includes/header.php';
?>

<div class="reminders-page">
    <div class="reminders-toolbar">
        <div>
            <h1 class="reminders-title">Reminders</h1>
            <p class="reminders-subtitle">Never miss an important moment.</p>
        </div>
        <div class="reminders-toolbar-actions">
            <a href="<?php echo SITE_URL; ?>/modules/reminders/reminders-add.php" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Add Reminder
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="reminders-stats">
        <div class="reminder-stat">
            <span class="reminder-stat-value"><?php echo $counts['total']; ?></span>
            <span class="reminder-stat-label">Total</span>
        </div>
        <div class="reminder-stat reminder-stat--today">
            <span class="reminder-stat-value"><?php echo $counts['today']; ?></span>
            <span class="reminder-stat-label">Today</span>
        </div>
        <div class="reminder-stat reminder-stat--upcoming">
            <span class="reminder-stat-value"><?php echo $counts['upcoming']; ?></span>
            <span class="reminder-stat-label">Upcoming</span>
        </div>
        <div class="reminder-stat reminder-stat--overdue">
            <span class="reminder-stat-value"><?php echo $counts['overdue']; ?></span>
            <span class="reminder-stat-label">Overdue</span>
        </div>
    </div>

    <!-- Filters -->
    <div class="reminders-filters">
        <div class="reminders-search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" id="reminderSearch" placeholder="Search reminders..." class="form-input">
        </div>
        <div class="reminders-filter-group">
            <select id="priorityFilter" class="form-select">
                <option value="">All Priorities</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
            </select>
            <select id="categoryFilter" class="form-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                <?php endforeach; ?>
            </select>
            <select id="statusFilter" class="form-select">
                <option value="">All Status</option>
                <option value="overdue">Overdue</option>
                <option value="today">Today</option>
                <option value="upcoming">Upcoming</option>
            </select>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="reminders-bulk-actions" id="bulkActions" style="display:none;">
        <span id="selectedCount">0 selected</span>
        <button class="btn btn-danger btn-sm" id="bulkDeleteBtn">Delete Selected</button>
    </div>

    <!-- Reminders List -->
    <div class="reminders-grid" id="remindersGrid">
        <div class="reminders-loading">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spin"><circle cx="12" cy="12" r="10"></circle></svg>
            Loading reminders...
        </div>
    </div>

    <!-- Empty State -->
    <div class="reminders-empty" id="remindersEmpty" style="display:none;">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="empty-icon">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        <h3>No reminders yet</h3>
        <p>Create your first reminder to stay on top of things.</p>
        <a href="<?php echo SITE_URL; ?>/modules/reminders/reminders-add.php" class="btn btn-primary">Add First Reminder</a>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
