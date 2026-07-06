(function () {
    function getCsrfToken() { return (typeof csrfToken !== 'undefined') ? csrfToken : ''; }
    function showToast(msg, type) {
        if (window.TaskNest && window.TaskNest.Toast) window.TaskNest.Toast.show(msg, type);
        else alert(msg);
    }

    window.deleteGoal = function (id) {
        var p = new FormData();
        p.append('action', 'delete_goal');
        p.append('goal_id', id);
        p.append('csrf_token', getCsrfToken());
        fetch(siteUrl + '/modules/goals/goals.php', { method: 'POST', body: p })
            .then(function (r) { return r.json(); })
            .then(function (r) {
                if (!r.success) { showToast(r.message || 'Failed.', 'error'); return; }
                showToast(r.message || 'Deleted.', 'success');
                window.location.reload();
            })
            .catch(function () { showToast('Network error.', 'error'); });
    };

    window.updateGoalProgress = function (id) {
        var val = prompt('Enter current progress value:');
        if (val === null) return;
        var p = new FormData();
        p.append('action', 'update_progress');
        p.append('goal_id', id);
        p.append('current_value', val);
        p.append('csrf_token', getCsrfToken());
        fetch(siteUrl + '/modules/goals/goals.php', { method: 'POST', body: p })
            .then(function (r) { return r.json(); })
            .then(function (r) {
                if (!r.success) { showToast(r.message || 'Failed.', 'error'); return; }
                showToast(r.message || 'Updated.', 'success');
                window.location.reload();
            })
            .catch(function () { showToast('Network error.', 'error'); });
    };
})();
