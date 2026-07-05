<?php
require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';
requireLogin($auth);

$user_id = $auth->getUserId();
$page_title = 'Habits';
$additional_css = ['habits.css'];
$additional_js = ['habits.js'];

$filters = ['search' => trim($_GET['search'] ?? ''), 'page' => max(1, (int) ($_GET['page'] ?? 1))];
$habitsData = getHabitsForList($mysqli, $user_id, $filters);
$habitStats = getHabitStats($mysqli, $user_id);

include dirname(__DIR__, 2) . '/includes/header.php';
?>
<div class="habits-page">
    <div class="habits-toolbar">
        <div>
            <h1 class="habits-title">Habits</h1>
            <p class="habits-subtitle">Build streaks and track your daily consistency.</p>
        </div>
        <div class="habits-toolbar-actions">
            <a class="btn btn-primary" href="<?php echo SITE_URL; ?>/modules/habits/habits-add.php">Add Habit</a>
        </div>
    </div>

    <div class="habits-summary">
        <div class="summary-card"><span class="summary-label">Total Habits</span><strong><?php echo (int) $habitStats['total']; ?></strong></div>
        <div class="summary-card"><span class="summary-label">Active</span><strong><?php echo (int) $habitStats['active']; ?></strong></div>
        <div class="summary-card"><span class="summary-label">Logged Today</span><strong><?php echo (int) $habitStats['logged_today']; ?></strong></div>
        <div class="summary-card"><span class="summary-label">Best Streak</span><strong><?php echo (int) $habitStats['best_streak']; ?> days</strong></div>
    </div>

    <div id="habitsGrid" class="habits-grid">
        <?php if (!empty($habitsData['items'])): ?>
            <?php foreach ($habitsData['items'] as $habit):
                $weekDays = [];
                for ($i = 6; $i >= 0; $i--) {
                    $day = date('Y-m-d', strtotime("-$i days"));
                    $dayLabel = date('D', strtotime($day));
                    $logged = false;
                    $checkLog = safePrepare($mysqli, "SELECT id FROM habit_logs WHERE habit_id = ? AND log_date = ?");
                    $checkLog->bind_param('is', $habit['id'], $day);
                    $checkLog->execute();
                    $logged = $checkLog->get_result()->num_rows > 0;
                    $isToday = ($day === date('Y-m-d'));
                    $weekDays[] = ['date' => $day, 'label' => $dayLabel, 'logged' => $logged, 'today' => $isToday];
                }
            ?>
                <div class="habit-card" data-habit-id="<?php echo (int) $habit['id']; ?>">
                    <div class="habit-card-header">
                        <h3><?php echo htmlspecialchars($habit['name']); ?></h3>
                        <span class="habit-frequency"><?php echo ucfirst(htmlspecialchars($habit['frequency'])); ?></span>
                    </div>
                    <?php if (!empty($habit['description'])): ?>
                        <p style="font-size:0.8rem;color:var(--text-secondary);margin:0 0 0.5rem;"><?php echo htmlspecialchars($habit['description']); ?></p>
                    <?php endif; ?>
                    <div class="habit-streak">
                        <?php foreach ($weekDays as $day): ?>
                            <div class="habit-day <?php echo $day['logged'] ? 'logged' : ''; ?> <?php echo $day['today'] ? 'today' : ''; ?>" data-habit-id="<?php echo (int) $habit['id']; ?>" data-date="<?php echo htmlspecialchars($day['date']); ?>" title="<?php echo htmlspecialchars($day['label'] . ' - ' . $day['date']); ?>">
                                <?php echo htmlspecialchars($day['label']); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="habit-meta">Logged <?php echo (int) $habit['logged_today']; ?>x today &middot; <?php echo (int) $habit['logged_week']; ?>x this week</div>
                    <div class="habit-actions">
                        <button class="btn btn-secondary btn-sm" type="button" onclick="logHabit(<?php echo (int) $habit['id']; ?>)">Log Today</button>
                        <button class="btn btn-secondary btn-sm" type="button" onclick="window.location.href='<?php echo SITE_URL; ?>/modules/habits/habits-edit.php?id=<?php echo (int) $habit['id']; ?>'">Edit</button>
                        <button class="btn btn-danger btn-sm" type="button" onclick="ConfirmModal.show('Delete Habit', 'Are you sure?', function(){ deleteHabit(<?php echo (int) $habit['id']; ?>); })">Delete</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">&#x1F3AF;</div>
                <h3>No habits yet</h3>
                <p>Create your first habit to start building streaks.</p>
                <a class="btn btn-primary" href="<?php echo SITE_URL; ?>/modules/habits/habits-add.php">Add Habit</a>
            </div>
        <?php endif; ?>
    </div>

    <div class="habit-chart-panel">
        <h3>Weekly Activity</h3>
        <canvas id="habitWeeklyChart" style="max-height:200px;"></canvas>
    </div>
</div>

<script>
window.addEventListener('load', function() {
    if (typeof Chart === 'undefined') return;
    var payload = new FormData();
    payload.append('action', 'chart_data');
    var tokenEl = document.querySelector('#habitForm input[name="csrf_token"]');
    if (tokenEl) payload.append('csrf_token', tokenEl.value);
    fetch('habits.php', { method: 'POST', body: payload }).then(function(r){return r.json();}).then(function(r){
        if (!r.success) return;
        var ctx = document.getElementById('habitWeeklyChart');
        if (!ctx) return;
        new Chart(ctx, {
            type: 'bar',
            data: { labels: r.chart.labels, datasets: [{ label: 'Logs', data: r.chart.values, backgroundColor: '#6366f1' }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
        });
    }).catch(function(){});
});
</script>
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
