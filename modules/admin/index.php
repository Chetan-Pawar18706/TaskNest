<?php
/**
 * TaskNest - Admin Panel
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

requireLogin($auth);

$user = $auth->getUser();
if (!isset($user['role']) || strtolower($user['role']) !== 'admin') {
    header('Location: ' . SITE_URL . '/dashboard.php');
    exit;
}

$page_title = 'Administration';
$additional_css = ['admin.css'];
$additional_js = ['admin.js'];

$tab = $_GET['tab'] ?? 'dashboard';
$adminStats = getAdminStats($mysqli);

$usersData = $activityData = $feedbackData = $settingsData = null;

if ($tab === 'users') {
    $usersData = getAdminUsers($mysqli, ['search' => trim($_GET['search'] ?? ''), 'page' => max(1, (int) ($_GET['page'] ?? 1))]);
}
if ($tab === 'activity') {
    $activityData = getAdminActivityLog($mysqli, ['page' => max(1, (int) ($_GET['page'] ?? 1))]);
}
if ($tab === 'feedback') {
    $feedbackData = getFeedbackList($mysqli, ['status' => $_GET['status'] ?? '', 'page' => max(1, (int) ($_GET['page'] ?? 1))]);
}
if ($tab === 'settings') {
    $settingsData = getSiteSettings($mysqli);
}

include dirname(__DIR__, 2) . '/includes/header.php';
?>
<input type="hidden" id="admin-csrf-token" value="<?php echo $auth->generateCsrfToken(); ?>">
<div class="admin-page">
    <div class="admin-toolbar">
        <div>
            <h1 class="admin-title">Administration</h1>
            <p class="admin-subtitle">Manage users, monitor activity, and configure settings.</p>
        </div>
    </div>

    <div class="admin-stats">
        <div class="summary-card"><span class="summary-label">Total Users</span><strong><?php echo (int) $adminStats['total_users']; ?></strong></div>
        <div class="summary-card"><span class="summary-label">Active Users</span><strong><?php echo (int) $adminStats['active_users']; ?></strong></div>
        <div class="summary-card"><span class="summary-label">Total Tasks</span><strong><?php echo (int) $adminStats['total_tasks']; ?></strong></div>
        <div class="summary-card"><span class="summary-label">Total Notes</span><strong><?php echo (int) $adminStats['total_notes']; ?></strong></div>
        <div class="summary-card"><span class="summary-label">Expenses</span><strong><?php echo (int) $adminStats['total_expenses']; ?></strong></div>
        <div class="summary-card"><span class="summary-label">Documents</span><strong><?php echo (int) $adminStats['total_documents']; ?></strong></div>
        <div class="summary-card"><span class="summary-label">Open Feedback</span><strong><?php echo (int) $adminStats['open_feedback']; ?></strong></div>
    </div>

    <div class="admin-tabs">
        <button class="admin-tab <?php echo $tab === 'dashboard' ? 'active' : ''; ?>" data-tab="dashboard">Dashboard</button>
        <button class="admin-tab <?php echo $tab === 'users' ? 'active' : ''; ?>" data-tab="users">Users</button>
        <button class="admin-tab <?php echo $tab === 'activity' ? 'active' : ''; ?>" data-tab="activity">Activity Log</button>
        <button class="admin-tab <?php echo $tab === 'feedback' ? 'active' : ''; ?>" data-tab="feedback">Feedback</button>
        <button class="admin-tab <?php echo $tab === 'settings' ? 'active' : ''; ?>" data-tab="settings">Settings</button>
    </div>

    <!-- Dashboard Tab -->
    <div class="admin-tab-content <?php echo $tab === 'dashboard' ? 'active' : ''; ?>" id="tab-dashboard">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
            <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:1.25rem;">
                <h3 style="margin:0 0 1rem;font-size:1rem;">User Registrations</h3>
                <canvas id="adminUserChart" style="max-height:200px;"></canvas>
            </div>
            <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:1.25rem;">
                <h3 style="margin:0 0 1rem;font-size:1rem;">Module Usage</h3>
                <canvas id="adminModuleChart" style="max-height:200px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Users Tab -->
    <div class="admin-tab-content <?php echo $tab === 'users' ? 'active' : ''; ?>" id="tab-users">
        <form method="get" style="margin-bottom:1rem;display:flex;gap:0.5rem;flex-wrap:wrap;">
            <input type="hidden" name="tab" value="users">
            <input type="text" name="search" class="form-control" placeholder="Search users..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" style="max-width:300px;">
            <button class="btn btn-secondary" type="submit">Search</button>
        </form>
        <?php if ($usersData && !empty($usersData['users'])): ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Name</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($usersData['users'] as $u): ?>
                            <tr>
                                <td><?php echo (int) $u['id']; ?></td>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><?php echo htmlspecialchars(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')); ?></td>
                                <td><span class="feedback-badge <?php echo $u['is_active'] ? 'resolved' : 'closed'; ?>"><?php echo $u['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                                <td><?php echo htmlspecialchars($u['created_at']); ?></td>
                                <td class="actions">
                                    <button class="btn btn-secondary btn-sm" type="button" data-action="toggle_user" data-id="<?php echo (int) $u['id']; ?>"><?php echo $u['is_active'] ? 'Deactivate' : 'Activate'; ?></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state"><p>No users found.</p></div>
        <?php endif; ?>
    </div>

    <!-- Activity Tab -->
    <div class="admin-tab-content <?php echo $tab === 'activity' ? 'active' : ''; ?>" id="tab-activity">
        <?php if ($activityData && !empty($activityData['logs'])): ?>
            <div class="activity-log-list">
                <?php foreach ($activityData['logs'] as $log): ?>
                    <div class="activity-log-item">
                        <span class="log-action"><?php echo htmlspecialchars($log['action']); ?></span>
                        <span class="log-desc"><?php echo htmlspecialchars($log['description'] ?? ''); ?> &mdash; <em><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></em></span>
                        <span class="log-time"><?php echo timeAgo($log['created_at']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state"><p>No activity logs yet.</p></div>
        <?php endif; ?>
    </div>

    <!-- Feedback Tab -->
    <div class="admin-tab-content <?php echo $tab === 'feedback' ? 'active' : ''; ?>" id="tab-feedback">
        <form method="get" style="margin-bottom:1rem;display:flex;gap:0.5rem;flex-wrap:wrap;">
            <input type="hidden" name="tab" value="feedback">
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="open" <?php echo ($_GET['status'] ?? '') === 'open' ? 'selected' : ''; ?>>Open</option>
                <option value="in_progress" <?php echo ($_GET['status'] ?? '') === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                <option value="resolved" <?php echo ($_GET['status'] ?? '') === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                <option value="closed" <?php echo ($_GET['status'] ?? '') === 'closed' ? 'selected' : ''; ?>>Closed</option>
            </select>
            <button class="btn btn-secondary" type="submit">Filter</button>
        </form>
        <?php if ($feedbackData && !empty($feedbackData['feedback'])): ?>
            <?php foreach ($feedbackData['feedback'] as $fb): ?>
                <div class="feedback-card" data-feedback-id="<?php echo (int) $fb['id']; ?>">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:0.5rem;">
                        <h3><?php echo htmlspecialchars($fb['subject']); ?></h3>
                        <span class="feedback-badge <?php echo htmlspecialchars($fb['status']); ?>"><?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($fb['status']))); ?></span>
                    </div>
                    <p><?php echo htmlspecialchars($fb['message']); ?></p>
                    <div class="feedback-meta">
                        <span>By: <?php echo htmlspecialchars($fb['username'] ?? 'Unknown'); ?></span>
                        <span>Category: <?php echo ucfirst(htmlspecialchars($fb['category'])); ?></span>
                        <span><?php echo timeAgo($fb['created_at']); ?></span>
                    </div>
                    <?php if (!empty($fb['admin_reply'])): ?>
                        <div class="feedback-reply">
                            <strong>Admin Reply:</strong>
                            <p><?php echo htmlspecialchars($fb['admin_reply']); ?></p>
                        </div>
                    <?php endif; ?>
                    <div style="margin-top:0.75rem;display:flex;gap:0.5rem;flex-wrap:wrap;">
                        <input type="text" class="form-control feedback-reply-input" placeholder="Type reply..." style="flex:1;min-width:200px;">
                        <select class="form-control feedback-status-select" style="max-width:140px;">
                            <option value="in_progress" <?php echo $fb['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="resolved" <?php echo $fb['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                            <option value="closed" <?php echo $fb['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                        </select>
                        <button class="btn btn-primary btn-sm" type="button" data-action="reply_feedback" data-id="<?php echo (int) $fb['id']; ?>">Reply</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state"><p>No feedback items.</p></div>
        <?php endif; ?>
    </div>

    <!-- Settings Tab -->
    <div class="admin-tab-content <?php echo $tab === 'settings' ? 'active' : ''; ?>" id="tab-settings">
        <?php if ($settingsData): ?>
            <form id="adminSettingsForm" class="settings-form">
                <input type="hidden" name="action" value="save_settings">
                <?php foreach ($settingsData as $s): ?>
                    <div class="form-group">
                        <label for="setting_<?php echo htmlspecialchars($s['setting_key']); ?>"><?php echo htmlspecialchars($s['description'] ?: $s['setting_key']); ?></label>
                        <?php if ($s['setting_type'] === 'boolean'): ?>
                            <select id="setting_<?php echo htmlspecialchars($s['setting_key']); ?>" name="settings[<?php echo htmlspecialchars($s['setting_key']); ?>]" class="form-control">
                                <option value="1" <?php echo $s['setting_value'] === '1' ? 'selected' : ''; ?>>Enabled</option>
                                <option value="0" <?php echo $s['setting_value'] === '0' ? 'selected' : ''; ?>>Disabled</option>
                            </select>
                        <?php elseif ($s['setting_type'] === 'number'): ?>
                            <input type="number" id="setting_<?php echo htmlspecialchars($s['setting_key']); ?>" name="settings[<?php echo htmlspecialchars($s['setting_key']); ?>]" class="form-control" value="<?php echo htmlspecialchars($s['setting_value']); ?>">
                        <?php else: ?>
                            <input type="text" id="setting_<?php echo htmlspecialchars($s['setting_key']); ?>" name="settings[<?php echo htmlspecialchars($s['setting_key']); ?>]" class="form-control" value="<?php echo htmlspecialchars($s['setting_value']); ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <button class="btn btn-primary" type="submit">Save Settings</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
(function() {
    var tabs = document.querySelectorAll('.admin-tab');
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            var tabName = tab.getAttribute('data-tab');
            window.location.href = 'modules/admin/admin.php?tab=' + tabName;
        });
    });

    function getCsrfToken() { var t = document.getElementById('admin-csrf-token'); return t ? t.value : ''; }
    function showToast(msg, type) { if (window.TaskNest && window.TaskNest.Toast) window.TaskNest.Toast.show(msg, type); else alert(msg); }

    document.querySelectorAll('[data-action="toggle_user"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var p = new FormData(); p.append('action', 'toggle_user'); p.append('user_id', btn.getAttribute('data-id')); p.append('csrf_token', getCsrfToken());
            fetch('modules/admin/admin.php', { method: 'POST', body: p }).then(function(r){return r.json();}).then(function(r){
                if (r.success) { showToast(r.message, 'success'); window.location.reload(); } else showToast(r.message || 'Failed.', 'error');
            });
        });
    });

    document.querySelectorAll('[data-action="reply_feedback"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var card = btn.closest('.feedback-card');
            var replyInput = card.querySelector('.feedback-reply-input');
            var statusSelect = card.querySelector('.feedback-status-select');
            var p = new FormData();
            p.append('action', 'reply_feedback');
            p.append('feedback_id', btn.getAttribute('data-id'));
            p.append('reply', replyInput.value);
            p.append('status', statusSelect.value);
            p.append('csrf_token', getCsrfToken());
            fetch('modules/admin/admin.php', { method: 'POST', body: p }).then(function(r){return r.json();}).then(function(r){
                if (r.success) { showToast(r.message, 'success'); window.location.reload(); } else showToast(r.message || 'Failed.', 'error');
            });
        });
    });

    var settingsForm = document.getElementById('adminSettingsForm');
    settingsForm && settingsForm.addEventListener('submit', function(e) {
        e.preventDefault();
        var p = new FormData(settingsForm);
        p.append('csrf_token', getCsrfToken());
        fetch('modules/admin/admin.php', { method: 'POST', body: p }).then(function(r){return r.json();}).then(function(r){
            if (r.success) showToast(r.message, 'success'); else showToast(r.message || 'Failed.', 'error');
        });
    });
})();
</script>
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
