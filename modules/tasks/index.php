<?php
/**
 * TaskNest - Tasks Module
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

requireLogin($auth);

$user_id = $auth->getUserId();
$page_title = 'Tasks';
$additional_css = ['tasks.css'];
$additional_js = ['tasks.js'];

$filters = [
    'search' => trim($_GET['search'] ?? ''),
    'status' => $_GET['status'] ?? '',
    'priority' => $_GET['priority'] ?? '',
    'category' => $_GET['category'] ?? '',
    'date_range' => $_GET['date_range'] ?? '',
    'overdue' => isset($_GET['overdue']) ? 1 : 0,
    'due_today' => isset($_GET['due_today']) ? 1 : 0,
    'due_week' => isset($_GET['due_week']) ? 1 : 0,
    'sort' => $_GET['sort'] ?? 'due_date',
    'view' => $_GET['view'] ?? 'list',
    'page' => max(1, (int) ($_GET['page'] ?? 1))
];

$categories = getTaskCategories($mysqli, $user_id);
$tasksData = getTasksForList($mysqli, $user_id, $filters);
$taskStats = getTaskStats($mysqli, $user_id);

include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="tasks-page">
    <div class="tasks-toolbar">
        <div>
            <h1 class="tasks-title">Tasks</h1>
            <p class="tasks-subtitle">Manage your work, priorities, and deadlines.</p>
        </div>
        <div class="tasks-toolbar-actions">
            <a href="<?php echo SITE_URL; ?>/modules/tasks/tasks-categories.php" class="btn btn-secondary">Manage Categories</a>
            <a href="<?php echo SITE_URL; ?>/modules/tasks/tasks-add.php" class="btn btn-primary">Add Task</a>
        </div>
    </div>

    <div class="tasks-summary">
        <div class="summary-card">
            <span class="summary-label">Total</span>
            <strong><?php echo (int) $taskStats['total']; ?></strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Pending</span>
            <strong><?php echo (int) $taskStats['pending']; ?></strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Completed</span>
            <strong><?php echo (int) $taskStats['completed']; ?></strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Overdue</span>
            <strong><?php echo (int) $taskStats['overdue']; ?></strong>
        </div>
    </div>

    <div class="tasks-controls">
        <button class="filter-toggle-btn" id="filterToggleBtn" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            <span>Filters</span>
        </button>
        <form class="tasks-filters" id="tasksFilterForm" method="get">
            <input type="text" name="search" class="form-control" placeholder="Search tasks" value="<?php echo htmlspecialchars($filters['search']); ?>">
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="Pending" <?php echo $filters['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="In Progress" <?php echo $filters['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                <option value="Completed" <?php echo $filters['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
            </select>
            <select name="priority" class="form-control">
                <option value="">All Priority</option>
                <option value="Low" <?php echo $filters['priority'] === 'Low' ? 'selected' : ''; ?>>Low</option>
                <option value="Medium" <?php echo $filters['priority'] === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                <option value="High" <?php echo $filters['priority'] === 'High' ? 'selected' : ''; ?>>High</option>
            </select>
            <select name="category" class="form-control">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo (int) $category['id']; ?>" <?php echo (string) $filters['category'] === (string) $category['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <select name="date_range" class="form-control">
                <option value="">Any Date</option>
                <option value="today" <?php echo $filters['date_range'] === 'today' ? 'selected' : ''; ?>>Today</option>
                <option value="week" <?php echo $filters['date_range'] === 'week' ? 'selected' : ''; ?>>This Week</option>
                <option value="month" <?php echo $filters['date_range'] === 'month' ? 'selected' : ''; ?>>This Month</option>
            </select>
            <label class="checkbox-inline"><input type="checkbox" name="overdue" value="1" <?php echo $filters['overdue'] ? 'checked' : ''; ?>> Overdue</label>
            <label class="checkbox-inline"><input type="checkbox" name="due_today" value="1" <?php echo $filters['due_today'] ? 'checked' : ''; ?>> Due Today</label>
            <label class="checkbox-inline"><input type="checkbox" name="due_week" value="1" <?php echo $filters['due_week'] ? 'checked' : ''; ?>> Due Week</label>
            <select name="sort" class="form-control">
                <option value="due_date" <?php echo $filters['sort'] === 'due_date' ? 'selected' : ''; ?>>Due Date</option>
                <option value="priority" <?php echo $filters['sort'] === 'priority' ? 'selected' : ''; ?>>Priority</option>
                <option value="status" <?php echo $filters['sort'] === 'status' ? 'selected' : ''; ?>>Status</option>
                <option value="created_at" <?php echo $filters['sort'] === 'created_at' ? 'selected' : ''; ?>>Created</option>
                <option value="title" <?php echo $filters['sort'] === 'title' ? 'selected' : ''; ?>>Alphabetical</option>
            </select>
            <select name="view" class="form-control">
                <option value="list" <?php echo $filters['view'] === 'list' ? 'selected' : ''; ?>>List</option>
                <option value="grid" <?php echo $filters['view'] === 'grid' ? 'selected' : ''; ?>>Grid</option>
                <option value="kanban" <?php echo $filters['view'] === 'kanban' ? 'selected' : ''; ?>>Kanban</option>
                <option value="table" <?php echo $filters['view'] === 'table' ? 'selected' : ''; ?>>Table</option>
            </select>
            <button class="btn btn-secondary" type="submit">Apply</button>
        </form>
    </div>

    <div class="tasks-actions-row">
        <div class="bulk-actions">
            <button class="btn btn-secondary" type="button" id="bulkCompleteBtn">Bulk Complete</button>
            <button class="btn btn-secondary" type="button" id="bulkDeleteBtn">Bulk Delete</button>
            <button class="btn btn-secondary" type="button" id="bulkCategoryBtn">Change Category</button>
        </div>
        <div class="page-info">
            <span><?php echo (int) $tasksData['total']; ?> tasks</span>
        </div>
    </div>

    <div class="tasks-list-view" id="tasksContainer" data-view="<?php echo htmlspecialchars($filters['view']); ?>">
        <?php if (!empty($tasksData['tasks'])): ?>
            <?php if ($filters['view'] === 'kanban'): ?>
                <div class="kanban-board">
                    <?php foreach (['Pending' => 'pending', 'In Progress' => 'in-progress', 'Completed' => 'completed'] as $label => $statusKey): ?>
                        <div class="kanban-column">
                            <h3><?php echo htmlspecialchars($label); ?></h3>
                            <?php foreach ($tasksData['tasks'] as $task): if (($task['status'] ?? '') !== $label) continue; ?>
                                <div class="task-card task-card--compact" data-task-id="<?php echo (int) $task['id']; ?>">
                                    <div class="task-row">
                                        <input type="checkbox" class="task-checkbox" value="<?php echo (int) $task['id']; ?>">
                                        <strong><?php echo htmlspecialchars($task['title']); ?></strong>
                                    </div>
                                    <div class="task-meta"><?php echo htmlspecialchars($task['category_name'] ?? 'Uncategorized'); ?></div>
                                    <div class="task-actions-inline">
                                        <a class="link-btn" href="<?php echo SITE_URL; ?>/modules/tasks/tasks-edit.php?id=<?php echo (int) $task['id']; ?>">Edit</a>
                                        <button class="link-btn" type="button" onclick="ConfirmModal.show('Delete Task', 'Are you sure?', function(){ deleteTask(<?php echo (int) $task['id']; ?>); })">Delete</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php elseif ($filters['view'] === 'table'): ?>
                <table class="tasks-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllTasks"></th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Due</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasksData['tasks'] as $task): ?>
                            <tr>
                                <td><input type="checkbox" class="task-checkbox" value="<?php echo (int) $task['id']; ?>"></td>
                                <td><?php echo htmlspecialchars($task['title']); ?></td>
                                <td><?php echo htmlspecialchars($task['category_name'] ?? 'Uncategorized'); ?></td>
                                <td><span class="priority-badge priority-<?php echo strtolower($task['priority'] ?? 'medium'); ?>"><?php echo htmlspecialchars($task['priority'] ?? 'Medium'); ?></span></td>
                                <td><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $task['status'] ?? 'pending')); ?>"><?php echo htmlspecialchars($task['status'] ?? 'Pending'); ?></span></td>
                                <td><?php echo htmlspecialchars($task['due_date'] ?? '—'); ?></td>
                                <td>
                                    <a class="link-btn" href="<?php echo SITE_URL; ?>/modules/tasks/tasks-edit.php?id=<?php echo (int) $task['id']; ?>">Edit</a>
                                    <button class="link-btn" type="button" onclick="ConfirmModal.show('Delete Task', 'Are you sure?', function(){ deleteTask(<?php echo (int) $task['id']; ?>); })">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php elseif ($filters['view'] === 'grid'): ?>
                <div class="task-grid">
                    <?php foreach ($tasksData['tasks'] as $task): ?>
                        <article class="task-card" data-task-id="<?php echo (int) $task['id']; ?>">
                            <div class="task-card-top">
                                <input type="checkbox" class="task-checkbox" value="<?php echo (int) $task['id']; ?>">
                                <span class="priority-badge priority-<?php echo strtolower($task['priority'] ?? 'medium'); ?>"><?php echo htmlspecialchars($task['priority'] ?? 'Medium'); ?></span>
                            </div>
                            <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                            <p><?php echo htmlspecialchars($task['description'] ?: 'No description'); ?></p>
                            <div class="task-meta">Category: <?php echo htmlspecialchars($task['category_name'] ?? 'Uncategorized'); ?></div>
                            <div class="task-meta">Due: <?php echo htmlspecialchars($task['due_date'] ?? '—'); ?></div>
                            <?php if (!empty($task['file_path'])): ?>
                            <div class="task-meta"><a href="<?php echo SITE_URL; ?>/<?php echo htmlspecialchars($task['file_path']); ?>" target="_blank" style="color:var(--primary);text-decoration:underline;">📎 Attachment</a></div>
                            <?php endif; ?>
                            <div class="task-actions">
                                <a class="btn btn-secondary btn-sm" href="<?php echo SITE_URL; ?>/modules/tasks/tasks-edit.php?id=<?php echo (int) $task['id']; ?>">Edit</a>
                                <button class="btn btn-secondary btn-sm" type="button" onclick="completeTask(<?php echo (int) $task['id']; ?>)">Complete</button>
                                <button class="btn btn-danger btn-sm" type="button" onclick="ConfirmModal.show('Delete Task', 'Are you sure?', function(){ deleteTask(<?php echo (int) $task['id']; ?>); })">Delete</button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="task-list">
                    <?php foreach ($tasksData['tasks'] as $task): ?>
                        <article class="task-card" data-task-id="<?php echo (int) $task['id']; ?>">
                            <div class="task-card-top">
                                <input type="checkbox" class="task-checkbox" value="<?php echo (int) $task['id']; ?>">
                                <span class="priority-badge priority-<?php echo strtolower($task['priority'] ?? 'medium'); ?>"><?php echo htmlspecialchars($task['priority'] ?? 'Medium'); ?></span>
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $task['status'] ?? 'pending')); ?>"><?php echo htmlspecialchars($task['status'] ?? 'Pending'); ?></span>
                            </div>
                            <h3><?php echo htmlspecialchars($task['title']); ?></h3>
                            <p><?php echo htmlspecialchars($task['description'] ?: 'No description'); ?></p>
                            <div class="task-meta">Category: <?php echo htmlspecialchars($task['category_name'] ?? 'Uncategorized'); ?></div>
                            <div class="task-meta">Due: <?php echo htmlspecialchars($task['due_date'] ?? '—'); ?></div>
                            <?php if (!empty($task['file_path'])): ?>
                            <div class="task-meta"><a href="<?php echo SITE_URL; ?>/<?php echo htmlspecialchars($task['file_path']); ?>" target="_blank" style="color:var(--primary);text-decoration:underline;">📎 Attachment</a></div>
                            <?php endif; ?>
                            <div class="task-actions">
                                <a class="btn btn-secondary btn-sm" href="<?php echo SITE_URL; ?>/modules/tasks/tasks-edit.php?id=<?php echo (int) $task['id']; ?>">Edit</a>
                                <button class="btn btn-secondary btn-sm" type="button" onclick="completeTask(<?php echo (int) $task['id']; ?>)">Complete</button>
                                <button class="btn btn-danger btn-sm" type="button" onclick="ConfirmModal.show('Delete Task', 'Are you sure?', function(){ deleteTask(<?php echo (int) $task['id']; ?>); })">Delete</button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">✓</div>
                <h3>No tasks yet</h3>
                <p>Create your first task to start organizing work.</p>
                <a href="<?php echo SITE_URL; ?>/modules/tasks/tasks-add.php" class="btn btn-primary">Add Task</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($tasksData['total_pages'] > 1): ?>
        <div class="pagination">
            <?php for ($page = 1; $page <= $tasksData['total_pages']; $page++): ?>
                <a class="page-link <?php echo $page === $filters['page'] ? 'active' : ''; ?>" href="<?php echo buildTaskPaginationUrl($filters, $page); ?>"><?php echo (int) $page; ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var toggleBtn = document.getElementById('filterToggleBtn');
    var filterForm = document.getElementById('tasksFilterForm');
    if (toggleBtn && filterForm) {
        toggleBtn.addEventListener('click', function() {
            filterForm.classList.toggle('open');
            toggleBtn.classList.toggle('active');
        });
    }
});
</script>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
