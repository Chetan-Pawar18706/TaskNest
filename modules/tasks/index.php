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
            <button class="btn btn-secondary" type="button" id="openCategoryModal">Manage Categories</button>
            <button class="btn btn-primary" type="button" id="openTaskModal">Add Task</button>
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
                                        <button class="link-btn" type="button" data-action="view" data-id="<?php echo (int) $task['id']; ?>">View</button>
                                        <button class="link-btn" type="button" data-action="edit" data-id="<?php echo (int) $task['id']; ?>">Edit</button>
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
                                <td><button class="link-btn" type="button" data-action="view" data-id="<?php echo (int) $task['id']; ?>">View</button></td>
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
                            <div class="task-actions">
                                <button class="btn btn-secondary btn-sm" type="button" data-action="view" data-id="<?php echo (int) $task['id']; ?>">View</button>
                                <button class="btn btn-secondary btn-sm" type="button" data-action="edit" data-id="<?php echo (int) $task['id']; ?>">Edit</button>
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
                            <div class="task-actions">
                                <button class="btn btn-secondary btn-sm" type="button" data-action="view" data-id="<?php echo (int) $task['id']; ?>">View</button>
                                <button class="btn btn-secondary btn-sm" type="button" data-action="edit" data-id="<?php echo (int) $task['id']; ?>">Edit</button>
                                <button class="btn btn-secondary btn-sm" type="button" data-action="complete" data-id="<?php echo (int) $task['id']; ?>">Complete</button>
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
                <button class="btn btn-primary" type="button" id="emptyStateAddTask">Add Task</button>
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

<div class="modal" id="taskModal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="taskModal"></div>
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 id="taskModalTitle">Add Task</h3>
            <button class="modal-close" type="button" data-close-modal="taskModal">×</button>
        </div>
        <form id="taskForm" class="modal-body">
            <input type="hidden" name="task_id" id="taskId">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <div class="form-group">
                <label for="taskTitle">Title</label>
                <input type="text" id="taskTitle" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="taskDescription">Description</label>
                <textarea id="taskDescription" name="description" class="form-control"></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="taskPriority">Priority</label>
                    <select id="taskPriority" name="priority" class="form-control">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="taskStatus">Status</label>
                    <select id="taskStatus" name="status" class="form-control">
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="taskCategory">Category</label>
                    <select id="taskCategory" name="category_id" class="form-control"></select>
                </div>
                <div class="form-group">
                    <label for="taskDueDate">Due Date</label>
                    <input type="date" id="taskDueDate" name="due_date" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label for="taskReminder">Reminder</label>
                <input type="datetime-local" id="taskReminder" name="reminder_datetime" class="form-control">
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-close-modal="taskModal">Cancel</button>
                <button class="btn btn-primary" type="submit">Save Task</button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="categoryModal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="categoryModal"></div>
    <div class="modal-dialog">
        <div class="modal-header">
            <h3>Manage Categories</h3>
            <button class="modal-close" type="button" data-close-modal="categoryModal">×</button>
        </div>
        <div class="modal-body">
            <form id="categoryForm" class="category-form">
                <input type="hidden" name="category_id" id="categoryId">
                <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
                <div class="form-group">
                    <label for="categoryName">Name</label>
                    <input type="text" id="categoryName" name="name" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="categoryColor">Color</label>
                        <input type="color" id="categoryColor" name="color" class="form-control" value="#6366f1">
                    </div>
                    <div class="form-group">
                        <label for="categoryIcon">Icon</label>
                        <input type="text" id="categoryIcon" name="icon" class="form-control" placeholder="e.g. task">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-close-modal="categoryModal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Save Category</button>
                </div>
            </form>
            <div class="category-list" id="categoryList"></div>
        </div>
    </div>
</div>

<div class="modal" id="confirmModal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="confirmModal"></div>
    <div class="modal-dialog modal-sm">
        <div class="modal-header">
            <h3 id="confirmModalTitle">Confirm</h3>
            <button class="modal-close" type="button" data-close-modal="confirmModal">×</button>
        </div>
        <div class="modal-body">
            <p id="confirmModalBody">Are you sure?</p>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-close-modal="confirmModal">Cancel</button>
                <button class="btn btn-danger" type="button" id="confirmActionBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.tasknestInitialFilters = <?php echo json_encode($filters, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
</script>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
