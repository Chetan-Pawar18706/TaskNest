(function () {
    function getCsrfToken() { return (typeof csrfToken !== 'undefined') ? csrfToken : ''; }
    function showToast(msg, type) {
        if (window.TaskNest && window.TaskNest.Toast) window.TaskNest.Toast.show(msg, type);
        else alert(msg);
    }

    window.deleteBorrowItem = function (id) {
        var p = new FormData();
        p.append('action', 'delete_borrow');
        p.append('borrow_id', id);
        p.append('csrf_token', getCsrfToken());
        fetch(siteUrl + '/modules/borrow/borrow.php', { method: 'POST', body: p })
            .then(function (r) { return r.json(); })
            .then(function (r) {
                if (!r.success) { showToast(r.message || 'Failed.', 'error'); return; }
                showToast(r.message || 'Deleted.', 'success');
                window.location.reload();
            });
    };

    window.markReturned = function (id) {
        var p = new FormData();
        p.append('action', 'mark_returned');
        p.append('borrow_id', id);
        p.append('csrf_token', getCsrfToken());
        fetch(siteUrl + '/modules/borrow/borrow.php', { method: 'POST', body: p })
            .then(function (r) { return r.json(); })
            .then(function (r) {
                if (!r.success) { showToast(r.message || 'Failed.', 'error'); return; }
                showToast(r.message || 'Marked as returned.', 'success');
                window.location.reload();
            });
    };
})();
