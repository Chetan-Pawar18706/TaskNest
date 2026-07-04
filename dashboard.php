<?php
/**
 * TaskNest - Dashboard
 */

require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin($auth);

$user_id = $auth->getUserId();
$user = $auth->getUser();

$counts = getDashboardCounts($mysqli, $user_id);
$recent_activities = getRecentActivity($mysqli, $user_id, 10);
$reminders = getUpcomingReminders($mysqli, $user_id, 7);
$expenseChart = getIncomeExpenseChartData($mysqli, $user_id, 6);
$completionChart = getTaskCompletionData($mysqli, $user_id);
$habitChart = getHabitProgressData($mysqli, $user_id);
$calendarEvents = getCalendarEvents($mysqli, $user_id, 60);
$allCalendarEvents = getAllCalendarEvents($mysqli, $user_id);

$page_title = 'Dashboard';
$additional_css = ['dashboard.css', 'components.css', 'theme.css'];
$additional_js = ['dashboard.js'];
include 'includes/header.php';
?>

<div class="dashboard-shell">
    <section class="dashboard-hero">
        <div>
            <h1>Welcome back, <?php echo htmlspecialchars($user['first_name'] ?? $user['username']); ?>.</h1>
            <p>Your workspace is ready. Review progress, stay ahead of deadlines, and keep your plans moving.</p>
        </div>
        <div class="dashboard-hero-actions">
            <a href="<?php echo SITE_URL; ?>/tasks.php" class="btn btn-primary">New Task</a>
            <a href="<?php echo SITE_URL; ?>/notes.php" class="btn btn-secondary">Add Note</a>
        </div>
    </section>

    <section class="dashboard-grid">
        <div class="dashboard-panel dashboard-panel--wide">
            <h2 class="dashboard-panel-title">Overview</h2>
            <p class="dashboard-panel-subtitle">A quick pulse on your plans and progress.</p>
            <div class="stats-grid" style="margin-top: 1rem;">
                <?php
                $stats = [
                    ['label' => 'Total Tasks', 'value' => $counts['tasks'], 'icon' => 'tasks', 'trend' => $counts['tasks'] > 0 ? 'Active' : 'No tasks', 'tone' => 'neutral'],
                    ['label' => 'Completed Tasks', 'value' => $counts['completed_tasks'], 'icon' => 'tasks', 'trend' => 'On track', 'tone' => 'success'],
                    ['label' => 'Pending Tasks', 'value' => $counts['pending_tasks'], 'icon' => 'notes', 'trend' => $counts['pending_tasks'] > 0 ? 'Needs attention' : 'Clear', 'tone' => $counts['pending_tasks'] > 0 ? 'warning' : 'neutral'],
                    ['label' => 'Total Notes', 'value' => $counts['notes'], 'icon' => 'notes', 'trend' => 'Captured', 'tone' => 'neutral'],
                    ['label' => 'Monthly Expense', 'value' => '$' . number_format($counts['monthly_expense'], 2), 'icon' => 'expenses', 'trend' => 'This month', 'tone' => 'warning'],
                    ['label' => 'Documents Stored', 'value' => $counts['documents'], 'icon' => 'documents', 'trend' => 'Secure', 'tone' => 'neutral'],
                    ['label' => 'Active Habits', 'value' => $counts['habits'], 'icon' => 'habits', 'trend' => 'Healthy', 'tone' => 'success'],
                    ['label' => 'Active Goals', 'value' => $counts['goals'], 'icon' => 'goals', 'trend' => 'Focused', 'tone' => 'neutral'],
                    ['label' => 'Shopping Items', 'value' => $counts['shopping'], 'icon' => 'shopping', 'trend' => 'Planned', 'tone' => 'neutral'],
                    ['label' => 'Borrow Items', 'value' => $counts['borrow'], 'icon' => 'borrow', 'trend' => 'Tracked', 'tone' => 'neutral']
                ];
                foreach ($stats as $stat):
                    $iconClass = 'stat-icon ' . $stat['icon'] . '-icon';
                    echo '<div class="stat-card">';
                    echo '<div class="' . $iconClass . '">';
                    echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">';
                    if ($stat['icon'] === 'tasks') {
                        echo '<polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>';
                    } elseif ($stat['icon'] === 'notes') {
                        echo '<path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"></path><line x1="6" y1="8" x2="18" y2="8"></line><line x1="6" y1="12" x2="18" y2="12"></line><line x1="6" y1="16" x2="18" y2="16"></line>';
                    } elseif ($stat['icon'] === 'expenses') {
                        echo '<circle cx="12" cy="12" r="1"></circle><path d="M12 1v6m0 6v6"></path><path d="M4.22 4.22l4.24 4.24m2.98 2.98l4.24 4.24"></path><path d="M1 12h6m6 0h6"></path><path d="M4.22 19.78l4.24-4.24m2.98-2.98l4.24-4.24"></path><path d="M19.78 19.78l-4.24-4.24m-2.98-2.98l-4.24-4.24"></path><path d="M19.78 4.22l-4.24 4.24m-2.98 2.98l-4.24 4.24"></path>';
                    } elseif ($stat['icon'] === 'documents') {
                        echo '<path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline>';
                    } elseif ($stat['icon'] === 'habits') {
                        echo '<circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle><circle cx="17.66" cy="6.34" r="1"></circle><circle cx="6.34" cy="17.66" r="1"></circle><circle cx="17.66" cy="17.66" r="1"></circle><circle cx="6.34" cy="6.34" r="1"></circle>';
                    } elseif ($stat['icon'] === 'goals') {
                        echo '<circle cx="12" cy="12" r="10"></circle><polyline points="8 12 12 16 16 12"></polyline><line x1="12" y1="8" x2="12" y2="16"></line>';
                    } elseif ($stat['icon'] === 'shopping') {
                        echo '<circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>';
                    } else {
                        echo '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>';
                    }
                    echo '</svg></div>';
                    echo '<div class="stat-content"><p class="stat-label">' . htmlspecialchars($stat['label']) . '</p><p class="stat-value">' . htmlspecialchars((string)$stat['value']) . '</p><span class="stat-trend ' . htmlspecialchars($stat['tone']) . '">' . htmlspecialchars($stat['trend']) . '</span></div>';
                    echo '</div>';
                endforeach;
                ?>
            </div>
        </div>

        <div class="dashboard-panel dashboard-panel--double">
            <div class="dashboard-panel-header">
                <div>
                    <h2 class="dashboard-panel-title">Income vs Expenses</h2>
                    <p class="dashboard-panel-subtitle">Track your cash flow over time.</p>
                </div>
                <div style="display:flex;gap:0.5rem;">
                    <button class="btn btn-secondary btn-sm chart-range-btn" data-months="3" style="font-size:0.8rem;height:30px;">3M</button>
                    <button class="btn btn-secondary btn-sm chart-range-btn active" data-months="6" style="font-size:0.8rem;height:30px;">6M</button>
                    <button class="btn btn-secondary btn-sm chart-range-btn" data-months="12" style="font-size:0.8rem;height:30px;">12M</button>
                </div>
            </div>
            <div class="chart-area">
                <canvas id="expenseChart" data-labels="<?php echo htmlspecialchars(json_encode($expenseChart['labels'])); ?>" data-income="<?php echo htmlspecialchars(json_encode($expenseChart['income'])); ?>" data-expenses="<?php echo htmlspecialchars(json_encode($expenseChart['expenses'])); ?>"></canvas>
            </div>
        </div>

        <div class="dashboard-panel dashboard-panel--single">
            <div class="dashboard-panel-header">
                <div>
                    <h2 class="dashboard-panel-title">Task Completion</h2>
                    <p class="dashboard-panel-subtitle">Completed vs pending work.</p>
                </div>
            </div>
            <div class="chart-area">
                <canvas id="completionChart" data-values="<?php echo htmlspecialchars(json_encode([$completionChart['completed'], $completionChart['pending']])); ?>"></canvas>
            </div>
        </div>

        <div class="dashboard-panel dashboard-panel--single">
            <div class="dashboard-panel-header">
                <div>
                    <h2 class="dashboard-panel-title">Habit Progress</h2>
                    <p class="dashboard-panel-subtitle">Weekly consistency trend.</p>
                </div>
            </div>
            <div class="chart-area">
                <canvas id="habitChart" data-labels="<?php echo htmlspecialchars(json_encode($habitChart['labels'])); ?>" data-values="<?php echo htmlspecialchars(json_encode($habitChart['values'])); ?>"></canvas>
            </div>
        </div>

        <div class="dashboard-panel dashboard-panel--single">
            <div class="dashboard-panel-header">
                <div>
                    <h2 class="dashboard-panel-title">Calendar</h2>
                    <p class="dashboard-panel-subtitle">Upcoming plans and events.</p>
                </div>
                <a href="<?php echo SITE_URL; ?>/events-add.php" class="btn btn-secondary btn-sm" style="font-size:0.8rem;height:30px;">+ Add Event</a>
            </div>
            <div id="dashboardCalendar" data-month-label="<?php echo date('F Y'); ?>" data-events="<?php echo htmlspecialchars(json_encode($allCalendarEvents)); ?>"></div>
            <div class="timeline-meta" style="margin-top: 0.7rem;"><?php echo count($calendarEvents) > 0 ? count($calendarEvents) . ' scheduled event(s) this month' : 'No events yet'; ?></div>
        </div>

        <div class="dashboard-panel dashboard-panel--double">
            <div class="dashboard-panel-header">
                <div>
                    <h2 class="dashboard-panel-title">Recent Activity</h2>
                    <p class="dashboard-panel-subtitle">The latest actions from your workspace.</p>
                </div>
            </div>
            <?php if (!empty($recent_activities)): ?>
                <div class="timeline-list">
                    <?php foreach ($recent_activities as $activity): ?>
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <p><?php echo htmlspecialchars($activity['description']); ?></p>
                                <div class="timeline-meta"><?php echo htmlspecialchars($activity['action']); ?> • <?php echo timeAgo($activity['created_at']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20"></path><path d="M2 12h20"></path></svg>
                    </div>
                    <strong>No activity yet</strong>
                    <span>New actions will appear here as you use TaskNest.</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="dashboard-panel dashboard-panel--single">
            <div class="dashboard-panel-header">
                <div>
                    <h2 class="dashboard-panel-title">Upcoming Reminders</h2>
                    <p class="dashboard-panel-subtitle">Items due soon.</p>
                </div>
            </div>
            <?php if (!empty($reminders)): ?>
                <div class="reminder-list">
                    <?php foreach ($reminders as $item): ?>
                        <div class="reminder-item">
                            <div class="reminder-content">
                                <p><?php echo htmlspecialchars($item['title']); ?></p>
                                <div class="reminder-meta"><?php echo htmlspecialchars($item['label']); ?> • <?php echo htmlspecialchars($item['due_date']); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3"></path><circle cx="12" cy="12" r="10"></circle></svg>
                    </div>
                    <strong>No reminders</strong>
                    <span>We will surface important deadlines here.</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="dashboard-panel dashboard-panel--single">
            <div class="dashboard-panel-header">
                <div>
                    <h2 class="dashboard-panel-title">Quick Actions</h2>
                    <p class="dashboard-panel-subtitle">Jump straight into your next step.</p>
                </div>
            </div>
            <div class="quick-action-grid">
                <button class="quick-action-btn" type="button" data-modal-action="task" data-modal-title="New Task" data-modal-message="Task creation will be available in the next module step."><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg><strong>New Task</strong><span>Create a task</span></button>
                <button class="quick-action-btn" type="button" data-modal-action="note" data-modal-title="New Note" data-modal-message="Note creation will be available in the next module step."><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"></path><line x1="6" y1="8" x2="18" y2="8"></line><line x1="6" y1="12" x2="18" y2="12"></line><line x1="6" y1="16" x2="18" y2="16"></line></svg><strong>New Note</strong><span>Capture an idea</span></button>
                <button class="quick-action-btn" type="button" data-modal-action="expense" data-modal-title="Add Expense" data-modal-message="Expense entry will be available in the next module step."><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"></circle><path d="M12 1v6m0 6v6"></path><path d="M4.22 4.22l4.24 4.24m2.98 2.98l4.24 4.24"></path><path d="M1 12h6m6 0h6"></path><path d="M4.22 19.78l4.24-4.24m2.98-2.98l4.24-4.24"></path><path d="M19.78 19.78l-4.24-4.24m-2.98-2.98l-4.24-4.24"></path><path d="M19.78 4.22l-4.24 4.24m-2.98 2.98l-4.24 4.24"></path></svg><strong>Add Expense</strong><span>Track spending</span></button>
                <button class="quick-action-btn" type="button" data-modal-action="document" data-modal-title="Upload Document" data-modal-message="Document upload will be available in the next module step."><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg><strong>Upload Document</strong><span>Store a file</span></button>
                <button class="quick-action-btn" type="button" data-modal-action="habit" data-modal-title="Add Habit" data-modal-message="Habit creation will be available in the next module step."><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg><strong>Add Habit</strong><span>Build a streak</span></button>
                <button class="quick-action-btn" type="button" data-modal-action="goal" data-modal-title="Add Goal" data-modal-message="Goal creation will be available in the next module step."><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="8 12 12 16 16 12"></polyline><line x1="12" y1="8" x2="12" y2="16"></line></svg><strong>Add Goal</strong><span>Set a target</span></button>
            </div>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
