(function () {
    const taskModal = document.getElementById('taskModal');
    const categoryModal = document.getElementById('categoryModal');
    const confirmModal = document.getElementById('confirmModal');
    const taskForm = document.getElementById('taskForm');
    const categoryForm = document.getElementById('categoryForm');
    const taskModalTitle = document.getElementById('taskModalTitle');
    const taskCategorySelect = document.getElementById('taskCategory');
    const categoryList = document.getElementById('categoryList');
    const confirmActionBtn = document.getElementById('confirmActionBtn');
    const confirmModalTitle = document.getElementById('confirmModalTitle');
    const confirmModalBody = document.getElementById('confirmModalBody');
    const tasksContainer = document.getElementById('tasksContainer');
    const filterForm = document.getElementById('tasksFilterForm');
    const selectAllTasks = document.getElementById('selectAllTasks');
    const bulkCompleteBtn = document.getElementById('bulkCompleteBtn');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkCategoryBtn = document.getElementById('bulkCategoryBtn');
    const openTaskModalBtn = document.getElementById('openTaskModal');
    const openCategoryModalBtn = document.getElementById('openCategoryModal');
    const emptyStateAddTask = document.getElementById('emptyStateAddTask');

    let pendingAction = null;

    function getCsrfToken() {
        const tokenInput = document.querySelector('input[name="csrf_token"]');
        return tokenInput ? tokenInput.value : '';
    }

    function openModal(modal) {
        if (!modal) return;
        modal.setAttribute('aria-hidden', 'false');
        modal.classList.add('is-open');
        document.body.classList.add('modal-open');
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.setAttribute('aria-hidden', 'true');
        modal.classList.remove('is-open');
        document.body.classList.remove('modal-open');
    }

    document.querySelectorAll('[data-close-modal]').forEach((btn) => {
        btn.addEventListener('click', function () {
            const modalId = this.getAttribute('data-close-modal');
            const modal = document.getElementById(modalId);
            closeModal(modal);
        });
    });

    function serializeForm(form) {
        const data = new FormData(form);
        data.append('csrf_token', getCsrfToken());
        return data;
    }

    function showToast(message, type) {
        if (window.showToast) {
            window.showToast(message, type);
        } else {
            alert(message);
        }
    }

    function loadCategories() {
        const payload = new FormData();
        payload.append('action', 'get_categories');
        payload.append('csrf_token', getCsrfToken());

        fetch('tasks.php', {
            method: 'POST',
            body: payload
        }).then((response) => response.json()).then((result) => {
            if (!result.success) {
                return;
            }
            taskCategorySelect.innerHTML = '<option value="">Uncategorized</option>';
            if (result.categories) {
                result.categories.forEach((category) => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    taskCategorySelect.appendChild(option);
                });
            }
            renderCategories(result.categories || []);
        }).catch(() => {
            showToast('Unable to load categories.', 'error');
        });
    }

    function renderCategories(categories) {
        if (!categoryList) return;
        categoryList.innerHTML = '';
        categories.forEach((category) => {
            const item = document.createElement('div');
            item.className = 'category-item';
            item.innerHTML = '<span>' + category.name + '</span><div><button class="link-btn" type="button" data-edit-category="' + category.id + '">Edit</button> <button class="link-btn" type="button" data-delete-category="' + category.id + '">Delete</button></div>';
            categoryList.appendChild(item);
        });
    }

    function resetTaskForm() {
        taskForm.reset();
        document.getElementById('taskId').value = '';
        taskModalTitle.textContent = 'Add Task';
        const firstStatus = taskForm.querySelector('[name="status"]');
        if (firstStatus) firstStatus.value = 'Pending';
    }

    function fillTaskForm(task) {
        resetTaskForm();
        document.getElementById('taskId').value = task.id;
        document.getElementById('taskTitle').value = task.title || '';
        document.getElementById('taskDescription').value = task.description || '';
        document.getElementById('taskPriority').value = task.priority || 'Medium';
        document.getElementById('taskStatus').value = task.status || 'Pending';
        document.getElementById('taskCategory').value = task.category_id || '';
        document.getElementById('taskDueDate').value = task.due_date || '';
        document.getElementById('taskReminder').value = task.reminder_datetime || '';
        taskModalTitle.textContent = 'Edit Task';
        openModal(taskModal);
    }

    function fetchTask(taskId) {
        const payload = new FormData();
        payload.append('action', 'get_task');
        payload.append('task_id', taskId);
        payload.append('csrf_token', getCsrfToken());

        fetch('tasks.php', {
            method: 'POST',
            body: payload
        }).then((response) => response.json()).then((result) => {
            if (!result.success) {
                showToast(result.message || 'Unable to load task.', 'error');
                return;
            }
            fillTaskForm(result.task);
        }).catch(() => {
            showToast('Unable to load task.', 'error');
        });
    }

    function submitTaskForm(event) {
        event.preventDefault();
        const payload = serializeForm(taskForm);
        payload.set('action', 'save_task');

        fetch('tasks.php', {
            method: 'POST',
            body: payload
        }).then((response) => response.json()).then((result) => {
            if (!result.success) {
                showToast(result.message || 'Unable to save task.', 'error');
                return;
            }
            closeModal(taskModal);
            showToast(result.message || 'Task saved.', 'success');
            window.location.reload();
        }).catch(() => {
            showToast('Unable to save task.', 'error');
        });
    }

    function submitCategoryForm(event) {
        event.preventDefault();
        const payload = serializeForm(categoryForm);
        payload.set('action', 'save_category');

        fetch('tasks.php', {
            method: 'POST',
            body: payload
        }).then((response) => response.json()).then((result) => {
            if (!result.success) {
                showToast(result.message || 'Unable to save category.', 'error');
                return;
            }
            categoryForm.reset();
            document.getElementById('categoryId').value = '';
            showToast(result.message || 'Category saved.', 'success');
            loadCategories();
        }).catch(() => {
            showToast('Unable to save category.', 'error');
        });
    }

    function confirmAction(title, body, action, taskId) {
        pendingAction = { action, taskId };
        confirmModalTitle.textContent = title;
        confirmModalBody.textContent = body;
        openModal(confirmModal);
    }

    function performPendingAction() {
        if (!pendingAction) return;
        const payload = new FormData();
        payload.append('action', pendingAction.action);
        payload.append('task_id', pendingAction.taskId);
        payload.append('csrf_token', getCsrfToken());
        fetch('tasks.php', {
            method: 'POST',
            body: payload
        }).then((response) => response.json()).then((result) => {
            closeModal(confirmModal);
            if (!result.success) {
                showToast(result.message || 'Action failed.', 'error');
                return;
            }
            showToast(result.message || 'Action completed.', 'success');
            window.location.reload();
        }).catch(() => {
            showToast('Action failed.', 'error');
        });
    }

    function getSelectedTaskIds() {
        return Array.from(document.querySelectorAll('.task-checkbox:checked')).map((checkbox) => checkbox.value);
    }

    function bulkAction(action) {
        const taskIds = getSelectedTaskIds();
        if (!taskIds.length) {
            showToast('Select at least one task first.', 'error');
            return;
        }
        const payload = new FormData();
        payload.append('action', 'bulk_action');
        payload.append('bulk_action', action);
        payload.append('task_ids', taskIds.join(','));
        payload.append('csrf_token', getCsrfToken());
        fetch('tasks.php', {
            method: 'POST',
            body: payload
        }).then((response) => response.json()).then((result) => {
            if (!result.success) {
                showToast(result.message || 'Bulk action failed.', 'error');
                return;
            }
            showToast(result.message || 'Bulk action completed.', 'success');
            window.location.reload();
        }).catch(() => {
            showToast('Bulk action failed.', 'error');
        });
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest('button');
        if (!button) return;

        const action = button.getAttribute('data-action');
        const taskId = button.getAttribute('data-id');
        const categoryId = button.getAttribute('data-edit-category');
        const deleteCategoryId = button.getAttribute('data-delete-category');

        if (action === 'edit' && taskId) {
            fetchTask(taskId);
            return;
        }
        if (action === 'view' && taskId) {
            fetchTask(taskId);
            return;
        }
        if (action === 'complete' && taskId) {
            confirmAction('Complete Task', 'Mark this task as completed?', 'update_status', taskId);
            return;
        }

        if (categoryId) {
            const payload = new FormData();
            payload.append('action', 'get_category');
            payload.append('category_id', categoryId);
            payload.append('csrf_token', getCsrfToken());
            fetch('tasks.php', {
                method: 'POST',
                body: payload
            }).then((response) => response.json()).then((result) => {
                if (!result.success) return;
                document.getElementById('categoryId').value = result.category.id || '';
                document.getElementById('categoryName').value = result.category.name || '';
                document.getElementById('categoryColor').value = result.category.color || '#6366f1';
                document.getElementById('categoryIcon').value = result.category.icon || 'task';
                openModal(categoryModal);
            });
            return;
        }

        if (deleteCategoryId) {
            confirmAction('Delete Category', 'Delete this category?', 'delete_category', deleteCategoryId);
            return;
        }
    });

    openTaskModalBtn && openTaskModalBtn.addEventListener('click', function () {
        resetTaskForm();
        openModal(taskModal);
    });

    openCategoryModalBtn && openCategoryModalBtn.addEventListener('click', function () {
        loadCategories();
        openModal(categoryModal);
    });

    emptyStateAddTask && emptyStateAddTask.addEventListener('click', function () {
        resetTaskForm();
        openModal(taskModal);
    });

    taskForm && taskForm.addEventListener('submit', submitTaskForm);
    categoryForm && categoryForm.addEventListener('submit', submitCategoryForm);
    confirmActionBtn && confirmActionBtn.addEventListener('click', performPendingAction);

    bulkCompleteBtn && bulkCompleteBtn.addEventListener('click', function () { bulkAction('complete'); });
    bulkDeleteBtn && bulkDeleteBtn.addEventListener('click', function () { bulkAction('delete'); });
    bulkCategoryBtn && bulkCategoryBtn.addEventListener('click', function () { bulkAction('category'); });

    if (selectAllTasks) {
        selectAllTasks.addEventListener('change', function () {
            document.querySelectorAll('.task-checkbox').forEach((checkbox) => {
                checkbox.checked = selectAllTasks.checked;
            });
        });
    }

    if (filterForm) {
        filterForm.addEventListener('submit', function () {
            const viewSelect = filterForm.querySelector('select[name="view"]');
            if (viewSelect) {
                const view = viewSelect.value;
                tasksContainer.setAttribute('data-view', view);
            }
        });
    }

    loadCategories();
    window.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeModal(taskModal);
            closeModal(categoryModal);
            closeModal(confirmModal);
        }
    });
})();
