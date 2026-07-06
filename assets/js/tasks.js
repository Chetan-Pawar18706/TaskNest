(function () {
    function getCsrfToken() { return (typeof csrfToken !== 'undefined') ? csrfToken : ''; }
    function showToast(msg, type) {
        if (window.TaskNest && window.TaskNest.Toast) window.TaskNest.Toast.show(msg, type);
        else alert(msg);
    }

    // Delete task
    window.deleteTask = function (id) {
        var p = new FormData();
        p.append('action', 'delete_task');
        p.append('task_id', id);
        p.append('csrf_token', getCsrfToken());
        fetch(siteUrl + '/modules/tasks/tasks.php', { method: 'POST', body: p })
            .then(function (r) { return r.json(); })
            .then(function (r) {
                if (!r.success) { showToast(r.message || 'Failed.', 'error'); return; }
                showToast(r.message || 'Deleted.', 'success');
                window.location.reload();
            })
            .catch(function () { showToast('Network error.', 'error'); });
    };

    // Complete task
    window.completeTask = function (id) {
        var p = new FormData();
        p.append('action', 'update_status');
        p.append('task_id', id);
        p.append('status', 'Completed');
        p.append('csrf_token', getCsrfToken());
        fetch(siteUrl + '/modules/tasks/tasks.php', { method: 'POST', body: p })
            .then(function (r) { return r.json(); })
            .then(function (r) {
                if (!r.success) { showToast(r.message || 'Failed.', 'error'); return; }
                showToast(r.message || 'Done.', 'success');
                window.location.reload();
            })
            .catch(function () { showToast('Network error.', 'error'); });
    };

    // Bulk actions
    function getSelectedIds() {
        var checks = document.querySelectorAll('.task-checkbox:checked');
        var ids = [];
        checks.forEach(function (c) { ids.push(c.value); });
        return ids;
    }

    var bulkCompleteBtn = document.getElementById('bulkCompleteBtn');
    var bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

    if (bulkCompleteBtn) bulkCompleteBtn.addEventListener('click', function () {
        var ids = getSelectedIds();
        if (!ids.length) { showToast('Select tasks first.', 'error'); return; }
        var p = new FormData();
        p.append('action', 'bulk_action');
        p.append('bulk_action', 'complete');
        p.append('task_ids', ids.join(','));
        p.append('csrf_token', getCsrfToken());
        fetch(siteUrl + '/modules/tasks/tasks.php', { method: 'POST', body: p })
            .then(function (r) { return r.json(); })
            .then(function (r) { if (r.success) window.location.reload(); else showToast(r.message || 'Failed.', 'error'); })
            .catch(function () { showToast('Network error.', 'error'); });
    });

    if (bulkDeleteBtn) bulkDeleteBtn.addEventListener('click', function () {
        var ids = getSelectedIds();
        if (!ids.length) { showToast('Select tasks first.', 'error'); return; }
        ConfirmModal.show('Delete Tasks', 'Delete ' + ids.length + ' selected tasks?', function () {
            var p = new FormData();
            p.append('action', 'bulk_action');
            p.append('bulk_action', 'delete');
            p.append('task_ids', ids.join(','));
            p.append('csrf_token', getCsrfToken());
            fetch(siteUrl + '/modules/tasks/tasks.php', { method: 'POST', body: p })
                .then(function (r) { return r.json(); })
                .then(function (r) { if (r.success) window.location.reload(); else showToast(r.message || 'Failed.', 'error'); })
                .catch(function () { showToast('Network error.', 'error'); });
        });
    });

    // Select all
    var selectAll = document.getElementById('selectAllTasks');
    if (selectAll) selectAll.addEventListener('change', function () {
        document.querySelectorAll('.task-checkbox').forEach(function (c) { c.checked = selectAll.checked; });
    });
})();
