(function () {
    function getCsrfToken() { return (typeof csrfToken !== 'undefined') ? csrfToken : ''; }
    function showToast(msg, type) {
        if (window.TaskNest && window.TaskNest.Toast) window.TaskNest.Toast.show(msg, type);
        else alert(msg);
    }

    window.deleteShoppingItem = function (id) {
        var p = new FormData();
        p.append('action', 'delete_item');
        p.append('item_id', id);
        p.append('csrf_token', getCsrfToken());
        fetch(siteUrl + '/modules/shopping/shopping.php', { method: 'POST', body: p })
            .then(function (r) { return r.json(); })
            .then(function (r) {
                if (!r.success) { showToast(r.message || 'Failed.', 'error'); return; }
                showToast(r.message || 'Deleted.', 'success');
                window.location.reload();
            });
    };

    window.toggleShoppingItem = function (id) {
        var p = new FormData();
        p.append('action', 'toggle_complete');
        p.append('item_id', id);
        p.append('csrf_token', getCsrfToken());
        fetch(siteUrl + '/modules/shopping/shopping.php', { method: 'POST', body: p })
            .then(function (r) { return r.json(); })
            .then(function (r) { if (r.success) window.location.reload(); else showToast(r.message || 'Failed.', 'error'); });
    };

    window.clearCompletedItems = function () {
        ConfirmModal.show('Clear Completed', 'Remove all completed items?', function () {
            var p = new FormData();
            p.append('action', 'clear_completed');
            p.append('csrf_token', getCsrfToken());
            fetch(siteUrl + '/modules/shopping/shopping.php', { method: 'POST', body: p })
                .then(function (r) { return r.json(); })
                .then(function (r) { if (r.success) window.location.reload(); else showToast(r.message || 'Failed.', 'error'); });
        });
    };
})();
