(function () {
    function getCsrfToken() { return (typeof csrfToken !== 'undefined') ? csrfToken : ''; }
    function showToast(msg, type) {
        if (window.TaskNest && window.TaskNest.Toast) window.TaskNest.Toast.show(msg, type);
        else alert(msg);
    }

    window.deleteNote = function (id) {
        var p = new FormData();
        p.append('action', 'delete_note');
        p.append('note_id', id);
        p.append('csrf_token', getCsrfToken());
        fetch(siteUrl + '/modules/notes/notes.php', { method: 'POST', body: p })
            .then(function (r) { return r.json(); })
            .then(function (r) {
                if (!r.success) { showToast(r.message || 'Failed.', 'error'); return; }
                showToast(r.message || 'Deleted.', 'success');
                window.location.reload();
            })
            .catch(function () { showToast('Network error.', 'error'); });
    };

    window.permanentDeleteNote = function (id) {
        var p = new FormData();
        p.append('action', 'permanent_delete_note');
        p.append('note_id', id);
        p.append('csrf_token', getCsrfToken());
        fetch(siteUrl + '/modules/notes/notes.php', { method: 'POST', body: p })
            .then(function (r) { return r.json(); })
            .then(function (r) {
                if (!r.success) { showToast(r.message || 'Failed.', 'error'); return; }
                showToast(r.message || 'Deleted.', 'success');
                window.location.reload();
            })
            .catch(function () { showToast('Network error.', 'error'); });
    };

    window.togglePinNote = function (id) {
        var p = new FormData();
        p.append('action', 'toggle_pin');
        p.append('note_id', id);
        p.append('csrf_token', getCsrfToken());
        fetch(siteUrl + '/modules/notes/notes.php', { method: 'POST', body: p })
            .then(function (r) { return r.json(); })
            .then(function (r) { if (r.success) window.location.reload(); else showToast(r.message || 'Failed.', 'error'); })
            .catch(function () { showToast('Network error.', 'error'); });
    };

    window.toggleArchiveNote = function (id) {
        var p = new FormData();
        p.append('action', 'toggle_archive');
        p.append('note_id', id);
        p.append('csrf_token', getCsrfToken());
        fetch(siteUrl + '/modules/notes/notes.php', { method: 'POST', body: p })
            .then(function (r) { return r.json(); })
            .then(function (r) { if (r.success) window.location.reload(); else showToast(r.message || 'Failed.', 'error'); })
            .catch(function () { showToast('Network error.', 'error'); });
    };
})();
