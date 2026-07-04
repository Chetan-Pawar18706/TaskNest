<?php
require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';
requireLogin($auth);

$user_id = $auth->getUserId();
$page_title = 'Goals';
$additional_css = ['goals.css'];
$additional_js = ['goals.js'];

$filters = [
    'search' => trim($_GET['search'] ?? ''),
    'status' => $_GET['status'] ?? '',
    'page' => max(1, (int) ($_GET['page'] ?? 1))
];
$goalsData = getGoalsForList($mysqli, $user_id, $filters);
$goalStats = getGoalStats($mysqli, $user_id);

include dirname(__DIR__, 2) . '/includes/header.php';
?>
<div class="goals-page">
    <div class="goals-toolbar">
        <div>
            <h1 class="goals-title">Goals</h1>
            <p class="goals-subtitle">Set targets and track your progress.</p>
        </div>
        <div class="goals-toolbar-actions">
            <button class="btn btn-primary" type="button" id="openGoalModal">Add Goal</button>
        </div>
    </div>

    <div class="goals-summary">
        <div class="summary-card"><span class="summary-label">Total</span><strong><?php echo (int) $goalStats['total']; ?></strong></div>
        <div class="summary-card"><span class="summary-label">Active</span><strong><?php echo (int) $goalStats['active']; ?></strong></div>
        <div class="summary-card"><span class="summary-label">Completed</span><strong><?php echo (int) $goalStats['completed']; ?></strong></div>
        <div class="summary-card"><span class="summary-label">Abandoned</span><strong><?php echo (int) $goalStats['abandoned']; ?></strong></div>
    </div>

    <div class="goals-controls">
        <form class="goals-filters" method="get">
            <input type="text" name="search" class="form-control" placeholder="Search goals..." value="<?php echo htmlspecialchars($filters['search']); ?>">
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="active" <?php echo $filters['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="completed" <?php echo $filters['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="abandoned" <?php echo $filters['status'] === 'abandoned' ? 'selected' : ''; ?>>Abandoned</option>
            </select>
            <button class="btn btn-secondary" type="submit">Apply</button>
        </form>
    </div>

    <div class="goals-list" id="goalsList">
        <?php if (!empty($goalsData['items'])): ?>
            <?php foreach ($goalsData['items'] as $goal):
                $pct = $goal['target_value'] > 0 ? min(100, round(($goal['current_value'] / $goal['target_value']) * 100)) : 0;
            ?>
                <div class="goal-card" data-title="<?php echo htmlspecialchars($goal['title']); ?>" data-description="<?php echo htmlspecialchars($goal['description']); ?>" data-category="<?php echo htmlspecialchars($goal['category']); ?>" data-target="<?php echo htmlspecialchars($goal['target_value']); ?>" data-current="<?php echo htmlspecialchars($goal['current_value']); ?>" data-unit="<?php echo htmlspecialchars($goal['unit']); ?>" data-start="<?php echo htmlspecialchars($goal['start_date']); ?>" data-due="<?php echo htmlspecialchars($goal['due_date']); ?>" data-status="<?php echo htmlspecialchars($goal['status']); ?>">
                    <div class="goal-card-header">
                        <h3><?php echo htmlspecialchars($goal['title']); ?></h3>
                        <span class="goal-status <?php echo htmlspecialchars($goal['status']); ?>"><?php echo ucfirst(htmlspecialchars($goal['status'])); ?></span>
                    </div>
                    <?php if (!empty($goal['description'])): ?>
                        <p style="font-size:0.85rem;color:var(--text-secondary);margin:0 0 0.5rem;"><?php echo htmlspecialchars($goal['description']); ?></p>
                    <?php endif; ?>
                    <?php if ($goal['target_value'] > 0): ?>
                        <div class="goal-progress">
                            <div class="goal-progress-bar"><div class="goal-progress-fill" style="width:<?php echo $pct; ?>%;background:<?php echo $goal['status'] === 'completed' ? '#22c55e' : '#6366f1'; ?>;"></div></div>
                            <div class="goal-progress-text"><span><?php echo htmlspecialchars($goal['current_value']); ?> / <?php echo htmlspecialchars($goal['target_value']); ?> <?php echo htmlspecialchars($goal['unit']); ?></span><span><?php echo $pct; ?>%</span></div>
                        </div>
                    <?php endif; ?>
                    <div class="goal-meta">
                        Started: <?php echo htmlspecialchars($goal['start_date']); ?>
                        <?php if (!empty($goal['due_date'])): ?> &middot; Due: <?php echo htmlspecialchars($goal['due_date']); ?><?php endif; ?>
                        <?php if (!empty($goal['category'])): ?> &middot; Category: <?php echo htmlspecialchars($goal['category']); ?><?php endif; ?>
                    </div>
                    <div class="goal-actions">
                        <?php if ($goal['status'] === 'active'): ?>
                            <button class="btn btn-secondary btn-sm" type="button" data-action="progress" data-id="<?php echo (int) $goal['id']; ?>" data-current="<?php echo htmlspecialchars($goal['current_value']); ?>">Update Progress</button>
                            <button class="btn btn-secondary btn-sm" type="button" data-action="edit" data-id="<?php echo (int) $goal['id']; ?>">Edit</button>
                        <?php endif; ?>
                        <button class="btn btn-danger btn-sm" type="button" data-action="delete" data-id="<?php echo (int) $goal['id']; ?>">Delete</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">&#x1F3AF;</div>
                <h3>No goals yet</h3>
                <p>Set your first goal and start tracking progress.</p>
                <button class="btn btn-primary" type="button" id="emptyStateAddGoal">Add Goal</button>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="modal" id="goalModal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="goalModal"></div>
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 id="goalModalTitle">Add Goal</h3>
            <button class="modal-close" type="button" data-close-modal="goalModal">&times;</button>
        </div>
        <form id="goalForm" class="modal-body">
            <input type="hidden" name="goal_id" id="goalId">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <div class="form-group"><label for="goalTitle">Title</label><input type="text" id="goalTitle" name="title" class="form-control" required></div>
            <div class="form-group"><label for="goalDescription">Description</label><textarea id="goalDescription" name="description" class="form-control" rows="2"></textarea></div>
            <div class="form-group"><label for="goalCategory">Category</label><input type="text" id="goalCategory" name="category" class="form-control" placeholder="e.g. Fitness, Career, Learning"></div>
            <div class="form-row">
                <div class="form-group"><label for="goalTargetValue">Target Value</label><input type="number" id="goalTargetValue" name="target_value" class="form-control" step="0.01" min="0"></div>
                <div class="form-group"><label for="goalCurrentValue">Current Value</label><input type="number" id="goalCurrentValue" name="current_value" class="form-control" step="0.01" min="0"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label for="goalUnit">Unit</label><input type="text" id="goalUnit" name="unit" class="form-control" placeholder="e.g. kg, hours, km"></div>
                <div class="form-group"><label for="goalStatus">Status</label><select id="goalStatus" name="status" class="form-control"><option value="active">Active</option><option value="completed">Completed</option><option value="abandoned">Abandoned</option></select></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label for="goalStartDate">Start Date</label><input type="date" id="goalStartDate" name="start_date" class="form-control" value="<?php echo date('Y-m-d'); ?>"></div>
                <div class="form-group"><label for="goalDueDate">Due Date</label><input type="date" id="goalDueDate" name="due_date" class="form-control"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-close-modal="goalModal">Cancel</button>
                <button class="btn btn-primary" type="submit">Save Goal</button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="goalProgressModal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="goalProgressModal"></div>
    <div class="modal-dialog modal-sm">
        <div class="modal-header">
            <h3>Update Progress</h3>
            <button class="modal-close" type="button" data-close-modal="goalProgressModal">&times;</button>
        </div>
        <form id="goalProgressForm" class="modal-body">
            <input type="hidden" name="goal_id" id="progressGoalId">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <div class="form-group"><label for="progressCurrentValue">Current Value</label><input type="number" id="progressCurrentValue" name="current_value" class="form-control" step="0.01" min="0"></div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-close-modal="goalProgressModal">Cancel</button>
                <button class="btn btn-primary" type="submit">Update</button>
            </div>
        </form>
    </div>
</div>
<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
