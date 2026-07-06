(function () {
    function getCsrfToken() { return (typeof csrfToken !== 'undefined') ? csrfToken : ''; }
    function showToast(msg, type) {
        if (window.TaskNest && window.TaskNest.Toast) window.TaskNest.Toast.show(msg, type);
        else alert(msg);
    }

    window.deleteDocument = function (id) {
        var p = new FormData();
        p.append('action', 'delete_document');
        p.append('document_id', id);
        p.append('csrf_token', getCsrfToken());
        fetch(siteUrl + '/modules/documents/documents.php', { method: 'POST', body: p })
            .then(function (r) { return r.json(); })
            .then(function (r) {
                if (!r.success) { showToast(r.message || 'Failed.', 'error'); return; }
                showToast(r.message || 'Deleted.', 'success');
                window.location.reload();
            })
            .catch(function () { showToast('Network error.', 'error'); });
    };
})();
