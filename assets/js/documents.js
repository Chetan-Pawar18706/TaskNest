(function () {
    var docUploadModal = document.getElementById('docUploadModal');
    var docPreviewModal = document.getElementById('docPreviewModal');
    var docCategoryModal = document.getElementById('docCategoryModal');
    var docConfirmModal = document.getElementById('docConfirmModal');
    var docUploadForm = document.getElementById('docUploadForm');
    var docCategoryForm = document.getElementById('docCategoryForm');
    var docModalTitle = document.getElementById('docModalTitle');
    var docSubmitBtn = document.getElementById('docSubmitBtn');
    var docFileInput = document.getElementById('docFileInput');
    var docDropzone = document.getElementById('docDropzone');
    var docFileName = document.getElementById('docFileName');
    var docCategorySelect = document.getElementById('docCategory');
    var docCatList = document.getElementById('docCatList');
    var docConfirmActionBtn = document.getElementById('docConfirmActionBtn');
    var docConfirmTitle = document.getElementById('docConfirmTitle');
    var docConfirmBody = document.getElementById('docConfirmBody');
    var openDocUploadModalBtn = document.getElementById('openDocUploadModal');
    var openDocCategoryModalBtn = document.getElementById('openDocCategoryModal');
    var emptyStateUploadDoc = document.getElementById('emptyStateUploadDoc');

    var pendingAction = null;
    var isEditing = false;

    function getCsrfToken() {
        var t = document.querySelector('#docUploadForm input[name="csrf_token"]');
        return t ? t.value : '';
    }

    function openModal(m) { if (!m) return; m.setAttribute('aria-hidden', 'false'); m.classList.add('is-open'); document.body.classList.add('modal-open'); }
    function closeModal(m) { if (!m) return; m.setAttribute('aria-hidden', 'true'); m.classList.remove('is-open'); document.body.classList.remove('modal-open'); }

    document.querySelectorAll('[data-close-modal]').forEach(function (btn) {
        btn.addEventListener('click', function () { closeModal(document.getElementById(this.getAttribute('data-close-modal'))); });
    });

    function showToast(msg, type) {
        if (window.TaskNest && window.TaskNest.Toast) window.TaskNest.Toast.show(msg, type);
        else if (window.showToast) window.showToast(msg, type);
        else alert(msg);
    }

    // Dropzone
    if (docDropzone) {
        docDropzone.addEventListener('click', function () { docFileInput.click(); });
        docDropzone.addEventListener('dragover', function (e) { e.preventDefault(); docDropzone.classList.add('drag-over'); });
        docDropzone.addEventListener('dragleave', function () { docDropzone.classList.remove('drag-over'); });
        docDropzone.addEventListener('drop', function (e) {
            e.preventDefault();
            docDropzone.classList.remove('drag-over');
            if (e.dataTransfer.files.length) {
                docFileInput.files = e.dataTransfer.files;
                docFileName.textContent = e.dataTransfer.files[0].name;
            }
        });
    }
    docFileInput && docFileInput.addEventListener('change', function () {
        if (docFileInput.files.length) {
            docFileName.textContent = docFileInput.files[0].name;
        }
    });

    function loadDocCategories() {
        var payload = new FormData();
        payload.append('action', 'get_categories');
        payload.append('csrf_token', getCsrfToken());
        fetch('documents.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (!result.success) return;
                docCategorySelect.innerHTML = '<option value="">No Category</option>';
                (result.categories || []).forEach(function (cat) {
                    var opt = document.createElement('option');
                    opt.value = cat.id;
                    opt.textContent = cat.name;
                    docCategorySelect.appendChild(opt);
                });
                renderDocCategories(result.categories || []);
            });
    }

    function renderDocCategories(cats) {
        if (!docCatList) return;
        docCatList.innerHTML = '';
        cats.forEach(function (cat) {
            var item = document.createElement('div');
            item.className = 'doc-category-item';
            item.innerHTML = '<span style="display:flex;align-items:center;gap:0.5rem;"><span style="width:12px;height:12px;border-radius:50%;background:' + (cat.color || '#6366f1') + ';display:inline-block;"></span>' + cat.name + '</span>' +
                '<div><button class="link-btn" type="button" data-edit-doc-cat="' + cat.id + '">Edit</button> <button class="link-btn" type="button" data-delete-doc-cat="' + cat.id + '">Delete</button></div>';
            docCatList.appendChild(item);
        });
    }

    function resetDocForm() {
        docUploadForm.reset();
        document.getElementById('docId').value = '';
        docFileName.textContent = '';
        docModalTitle.textContent = 'Upload Document';
        docSubmitBtn.textContent = 'Upload';
        isEditing = false;
    }

    function fillDocForm(doc) {
        resetDocForm();
        isEditing = true;
        document.getElementById('docId').value = doc.id;
        document.getElementById('docTitle').value = doc.title || '';
        document.getElementById('docDescription').value = doc.description || '';
        document.getElementById('docExpiry').value = doc.expiry_date || '';
        document.getElementById('docReminder').value = doc.reminder_date || '';
        document.getElementById('docImportant').checked = !!parseInt(doc.is_important);
        docFileName.textContent = doc.original_name || '';
        docModalTitle.textContent = 'Edit Document';
        docSubmitBtn.textContent = 'Save Changes';
        loadDocCategories();
        setTimeout(function () { docCategorySelect.value = doc.category_id || ''; }, 200);
        openModal(docUploadModal);
    }

    function fetchDoc(id) {
        var payload = new FormData();
        payload.append('action', 'get_document');
        payload.append('document_id', id);
        payload.append('csrf_token', getCsrfToken());
        fetch('documents.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (!result.success) { showToast(result.message || 'Not found.', 'error'); return; }
                fillDocForm(result.document);
            });
    }

    function submitDocForm(e) {
        e.preventDefault();
        var payload = new FormData(docUploadForm);
        payload.set('action', isEditing ? 'update_document' : 'upload_document');
        payload.set('csrf_token', getCsrfToken());
        fetch('documents.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (!result.success) { showToast(result.message || 'Save failed.', 'error'); return; }
                closeModal(docUploadModal);
                showToast(result.message || 'Saved.', 'success');
                window.location.reload();
            });
    }

    function submitDocCatForm(e) {
        e.preventDefault();
        var payload = new FormData(docCategoryForm);
        payload.set('action', 'save_category');
        payload.set('csrf_token', getCsrfToken());
        fetch('documents.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (!result.success) { showToast(result.message || 'Save failed.', 'error'); return; }
                docCategoryForm.reset();
                document.getElementById('docCatId').value = '';
                showToast(result.message || 'Saved.', 'success');
                loadDocCategories();
            });
    }

    function confirmDocAction(title, body, action, id) {
        pendingAction = { action: action, id: id };
        docConfirmTitle.textContent = title;
        docConfirmBody.textContent = body;
        openModal(docConfirmModal);
    }

    function performPendingDocAction() {
        if (!pendingAction) return;
        var payload = new FormData();
        payload.append('action', pendingAction.action);
        payload.append('document_id', pendingAction.id);
        payload.append('csrf_token', getCsrfToken());
        fetch('documents.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                closeModal(docConfirmModal);
                if (!result.success) { showToast(result.message || 'Failed.', 'error'); return; }
                showToast(result.message || 'Done.', 'success');
                window.location.reload();
            });
    }

    // Document actions
    var docsContainer = document.getElementById('docsContainer');
    if (docsContainer) docsContainer.addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-action]');
        if (!btn) return;
        var action = btn.getAttribute('data-action');
        var id = btn.getAttribute('data-id');
        if (action === 'preview') previewDocument(id);
        else if (action === 'edit') fetchDoc(id);
        else if (action === 'delete') confirmDocAction('Delete Document', 'Move this document to trash?', 'delete_document', id);
    });

    function previewDocument(id) {
        var payload = new FormData();
        payload.append('action', 'get_document');
        payload.append('document_id', id);
        payload.append('csrf_token', getCsrfToken());
        fetch('documents.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (!result.success) { showToast(result.message || 'Not found.', 'error'); return; }
                var doc = result.document;
                document.getElementById('docPreviewTitle').textContent = doc.title;
                var content = document.getElementById('docPreviewContent');
                var ext = doc.original_name.split('.').pop().toLowerCase();
                var url = 'uploads/documents/' + doc.filename;

                if (['jpg', 'jpeg', 'png', 'gif'].indexOf(ext) !== -1) {
                    content.innerHTML = '<img src="' + url + '" alt="' + doc.title + '">';
                } else if (ext === 'pdf') {
                    content.innerHTML = '<iframe src="' + url + '"></iframe>';
                } else {
                    content.innerHTML = '<p>Preview not available for this file type.</p><a href="' + url + '" download="' + doc.original_name + '" class="btn btn-primary">Download to view</a>';
                }
                openModal(docPreviewModal);
            });
    }

    // Category actions
    docCatList && docCatList.addEventListener('click', function (e) {
        var editBtn = e.target.closest('[data-edit-doc-cat]');
        var delBtn = e.target.closest('[data-delete-doc-cat]');
        if (editBtn) {
            var catId = editBtn.getAttribute('data-edit-doc-cat');
            var payload = new FormData();
            payload.append('action', 'get_categories');
            payload.append('csrf_token', getCsrfToken());
            fetch('documents.php', { method: 'POST', body: payload })
                .then(function (r) { return r.json(); })
                .then(function (result) {
                    var cat = (result.categories || []).find(function (c) { return String(c.id) === String(catId); });
                    if (cat) {
                        document.getElementById('docCatId').value = cat.id;
                        document.getElementById('docCatName').value = cat.name;
                        document.getElementById('docCatColor').value = cat.color || '#6366f1';
                    }
                });
            return;
        }
        if (delBtn) {
            confirmDocAction('Delete Category', 'Delete this document category?', 'delete_category', delBtn.getAttribute('data-delete-doc-cat'));
        }
    });

    openDocUploadModalBtn && openDocUploadModalBtn.addEventListener('click', function () { resetDocForm(); loadDocCategories(); openModal(docUploadModal); });
    openDocCategoryModalBtn && openDocCategoryModalBtn.addEventListener('click', function () { loadDocCategories(); openModal(docCategoryModal); });
    emptyStateUploadDoc && emptyStateUploadDoc.addEventListener('click', function () { resetDocForm(); loadDocCategories(); openModal(docUploadModal); });

    docUploadForm && docUploadForm.addEventListener('submit', submitDocForm);
    docCategoryForm && docCategoryForm.addEventListener('submit', submitDocCatForm);
    docConfirmActionBtn && docConfirmActionBtn.addEventListener('click', performPendingDocAction);

    loadDocCategories();

    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeModal(docUploadModal);
            closeModal(docPreviewModal);
            closeModal(docCategoryModal);
            closeModal(docConfirmModal);
        }
    });
})();
