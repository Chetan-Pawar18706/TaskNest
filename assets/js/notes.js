(function () {
    const noteModal = document.getElementById('noteModal');
    const noteViewModal = document.getElementById('noteViewModal');
    const noteCategoryModal = document.getElementById('noteCategoryModal');
    const noteConfirmModal = document.getElementById('noteConfirmModal');
    const noteForm = document.getElementById('noteForm');
    const noteCategoryForm = document.getElementById('noteCategoryForm');
    const noteModalTitle = document.getElementById('noteModalTitle');
    const noteEditor = document.getElementById('noteEditor');
    const noteContent = document.getElementById('noteContent');
    const noteCategorySelect = document.getElementById('noteCategory');
    const noteCategoryList = document.getElementById('noteCategoryList');
    const noteImagesPreview = document.getElementById('noteImagesPreview');
    const noteImageInput = document.getElementById('noteImageInput');
    const noteConfirmActionBtn = document.getElementById('noteConfirmActionBtn');
    const noteConfirmTitle = document.getElementById('noteConfirmTitle');
    const noteConfirmBody = document.getElementById('noteConfirmBody');
    const openNoteModalBtn = document.getElementById('openNoteModal');
    const openNoteCategoryModalBtn = document.getElementById('openNoteCategoryModal');
    const emptyStateAddNote = document.getElementById('emptyStateAddNote');
    const bulkArchiveBtn = document.getElementById('bulkArchiveBtn');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

    let pendingAction = null;
    let currentNoteId = 0;
    let uploadedImages = [];

    function getCsrfToken() {
        const tokenInput = document.querySelector('#noteForm input[name="csrf_token"]');
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

    document.querySelectorAll('[data-close-modal]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var modalId = this.getAttribute('data-close-modal');
            var modal = document.getElementById(modalId);
            closeModal(modal);
        });
    });

    function showToast(message, type) {
        if (window.TaskNest && window.TaskNest.Toast) {
            window.TaskNest.Toast.show(message, type);
        } else if (window.showToast) {
            window.showToast(message, type);
        } else {
            alert(message);
        }
    }

    // Rich text toolbar
    document.getElementById('richTextToolbar').addEventListener('click', function (e) {
        var btn = e.target.closest('button');
        if (!btn) return;
        e.preventDefault();
        var cmd = btn.getAttribute('data-cmd');
        var val = btn.getAttribute('data-val') || null;
        if (cmd === 'formatBlock' && val) {
            document.execCommand(cmd, false, '<' + val + '>');
        } else {
            document.execCommand(cmd, false, null);
        }
        noteEditor.focus();
    });

    // Sync editor content to hidden input before form submit
    function syncEditorContent() {
        noteContent.value = noteEditor.innerHTML;
    }

    // Image upload
    document.getElementById('addNoteImageBtn').addEventListener('click', function () {
        noteImageInput.click();
    });

    noteImageInput.addEventListener('change', function () {
        var files = noteImageInput.files;
        if (!files.length) return;

        for (var i = 0; i < files.length; i++) {
            (function (file) {
                var formData = new FormData();
                formData.append('action', 'upload_image');
                formData.append('note_id', currentNoteId || '0');
                formData.append('note_image', file);
                formData.append('csrf_token', getCsrfToken());

                fetch('notes.php', { method: 'POST', body: formData })
                    .then(function (r) { return r.json(); })
                    .then(function (result) {
                        if (result.success && result.image) {
                            uploadedImages.push(result.image);
                            renderImagePreview(result.image);
                            showToast('Image uploaded.', 'success');
                        } else {
                            showToast(result.message || 'Upload failed.', 'error');
                        }
                    })
                    .catch(function () {
                        showToast('Upload failed.', 'error');
                    });
            })(files[i]);
        }
        noteImageInput.value = '';
    });

    function renderImagePreview(image) {
        var div = document.createElement('div');
        div.style.position = 'relative';
        div.style.display = 'inline-block';
        div.style.margin = '0.25rem';
        div.innerHTML = '<img src="' + image.url + '" alt="' + (image.original_name || 'image') + '" style="max-width:120px;max-height:80px;object-fit:cover;border-radius:4px;border:1px solid var(--border-color);">' +
            '<button type="button" class="btn btn-danger btn-sm" data-remove-image="' + image.id + '" style="position:absolute;top:-6px;right:-6px;padding:2px 6px;font-size:0.65rem;border-radius:50%;">&times;</button>';
        noteImagesPreview.appendChild(div);
    }

    noteImagesPreview.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-remove-image]');
        if (!btn) return;
        var imageId = btn.getAttribute('data-remove-image');
        var formData = new FormData();
        formData.append('action', 'delete_image');
        formData.append('image_id', imageId);
        formData.append('csrf_token', getCsrfToken());

        fetch('notes.php', { method: 'POST', body: formData })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (result.success) {
                    btn.parentElement.remove();
                    uploadedImages = uploadedImages.filter(function (img) { return String(img.id) !== String(imageId); });
                    showToast('Image removed.', 'success');
                }
            });
    });

    function resetNoteForm() {
        noteForm.reset();
        document.getElementById('noteId').value = '';
        noteModalTitle.textContent = 'Add Note';
        noteEditor.innerHTML = '';
        noteContent.value = '';
        noteImagesPreview.innerHTML = '';
        uploadedImages = [];
        currentNoteId = 0;
    }

    function fillNoteForm(note) {
        resetNoteForm();
        document.getElementById('noteId').value = note.id;
        document.getElementById('noteTitle').value = note.title || '';
        document.getElementById('noteCategory').value = note.category_id || '';
        noteEditor.innerHTML = note.content || '';
        noteContent.value = note.content || '';
        document.getElementById('noteIsPinned').checked = !!parseInt(note.is_pinned);
        document.getElementById('noteIsArchived').checked = !!parseInt(note.is_archived);
        noteModalTitle.textContent = 'Edit Note';
        currentNoteId = parseInt(note.id) || 0;

        if (note.images && note.images.length) {
            note.images.forEach(function (img) {
                uploadedImages.push(img);
                renderImagePreview({ id: img.id, url: 'uploads/notes/' + img.filename, original_name: img.original_name });
            });
        }
        openModal(noteModal);
    }

    function fetchNote(noteId) {
        var payload = new FormData();
        payload.append('action', 'get_note');
        payload.append('note_id', noteId);
        payload.append('csrf_token', getCsrfToken());

        fetch('notes.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (!result.success) {
                    showToast(result.message || 'Unable to load note.', 'error');
                    return;
                }
                fillNoteForm(result.note);
            })
            .catch(function () {
                showToast('Unable to load note.', 'error');
            });
    }

    function viewNote(noteId) {
        var payload = new FormData();
        payload.append('action', 'get_note');
        payload.append('note_id', noteId);
        payload.append('csrf_token', getCsrfToken());

        fetch('notes.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (!result.success) {
                    showToast(result.message || 'Unable to load note.', 'error');
                    return;
                }
                var note = result.note;
                document.getElementById('noteViewTitle').textContent = note.title;
                document.getElementById('noteViewContent').innerHTML = note.content || '<p style="color:var(--text-secondary);">No content</p>';
                var imagesDiv = document.getElementById('noteViewImages');
                imagesDiv.innerHTML = '';
                if (note.images && note.images.length) {
                    note.images.forEach(function (img) {
                        var imgEl = document.createElement('img');
                        imgEl.src = 'uploads/notes/' + img.filename;
                        imgEl.alt = img.original_name || 'image';
                        imagesDiv.appendChild(imgEl);
                    });
                }
                openModal(noteViewModal);
            })
            .catch(function () {
                showToast('Unable to load note.', 'error');
            });
    }

    function submitNoteForm(event) {
        event.preventDefault();
        syncEditorContent();
        var payload = new FormData(noteForm);
        payload.set('action', 'save_note');
        payload.set('csrf_token', getCsrfToken());

        fetch('notes.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (!result.success) {
                    showToast(result.message || 'Unable to save note.', 'error');
                    return;
                }
                closeModal(noteModal);
                showToast(result.message || 'Note saved.', 'success');
                window.location.reload();
            })
            .catch(function () {
                showToast('Unable to save note.', 'error');
            });
    }

    function loadNoteCategories() {
        var payload = new FormData();
        payload.append('action', 'get_categories');
        payload.append('csrf_token', getCsrfToken());

        fetch('notes.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (!result.success) return;
                noteCategorySelect.innerHTML = '<option value="">No Category</option>';
                if (result.categories) {
                    result.categories.forEach(function (cat) {
                        var opt = document.createElement('option');
                        opt.value = cat.id;
                        opt.textContent = cat.name;
                        noteCategorySelect.appendChild(opt);
                    });
                }
                renderNoteCategories(result.categories || []);
            });
    }

    function renderNoteCategories(categories) {
        if (!noteCategoryList) return;
        noteCategoryList.innerHTML = '';
        categories.forEach(function (cat) {
            var item = document.createElement('div');
            item.className = 'note-category-item';
            item.innerHTML = '<span style="display:flex;align-items:center;gap:0.5rem;"><span style="width:12px;height:12px;border-radius:50%;background:' + (cat.color || '#6366f1') + ';display:inline-block;"></span>' + cat.name + '</span>' +
                '<div><button class="link-btn" type="button" data-edit-note-cat="' + cat.id + '">Edit</button> <button class="link-btn" type="button" data-delete-note-cat="' + cat.id + '">Delete</button></div>';
            noteCategoryList.appendChild(item);
        });
    }

    function submitNoteCategoryForm(event) {
        event.preventDefault();
        var payload = new FormData(noteCategoryForm);
        payload.set('action', 'save_category');
        payload.set('csrf_token', getCsrfToken());

        fetch('notes.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (!result.success) {
                    showToast(result.message || 'Unable to save category.', 'error');
                    return;
                }
                noteCategoryForm.reset();
                document.getElementById('noteCategoryId').value = '';
                showToast(result.message || 'Category saved.', 'success');
                loadNoteCategories();
            });
    }

    function confirmNoteAction(title, body, action, noteId) {
        pendingAction = { action: action, noteId: noteId };
        noteConfirmTitle.textContent = title;
        noteConfirmBody.textContent = body;
        openModal(noteConfirmModal);
    }

    function performPendingNoteAction() {
        if (!pendingAction) return;
        var payload = new FormData();
        payload.append('action', pendingAction.action);
        payload.append('note_id', pendingAction.noteId);
        payload.append('csrf_token', getCsrfToken());
        fetch('notes.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                closeModal(noteConfirmModal);
                if (!result.success) {
                    showToast(result.message || 'Action failed.', 'error');
                    return;
                }
                showToast(result.message || 'Action completed.', 'success');
                window.location.reload();
            })
            .catch(function () {
                showToast('Action failed.', 'error');
            });
    }

    function getSelectedNoteIds() {
        return Array.from(document.querySelectorAll('.note-checkbox:checked')).map(function (cb) { return cb.value; });
    }

    function bulkNoteAction(action) {
        var noteIds = getSelectedNoteIds();
        if (!noteIds.length) {
            showToast('Select at least one note first.', 'error');
            return;
        }
        var payload = new FormData();
        payload.append('action', 'bulk_action');
        payload.append('bulk_action', action);
        payload.append('note_ids', noteIds.join(','));
        payload.append('csrf_token', getCsrfToken());
        fetch('notes.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (!result.success) {
                    showToast(result.message || 'Bulk action failed.', 'error');
                    return;
                }
                showToast(result.message || 'Bulk action completed.', 'success');
                window.location.reload();
            });
    }

    // Event delegation for note card actions
    document.getElementById('notesContainer').addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-action]');
        if (!btn) return;
        var action = btn.getAttribute('data-action');
        var noteId = btn.getAttribute('data-id');

        switch (action) {
            case 'view':
                viewNote(noteId);
                break;
            case 'edit':
                fetchNote(noteId);
                break;
            case 'pin':
                var payload = new FormData();
                payload.append('action', 'toggle_pin');
                payload.append('note_id', noteId);
                payload.append('csrf_token', getCsrfToken());
                fetch('notes.php', { method: 'POST', body: payload })
                    .then(function (r) { return r.json(); })
                    .then(function (result) {
                        if (result.success) { showToast(result.message, 'success'); window.location.reload(); }
                        else showToast(result.message || 'Failed.', 'error');
                    });
                break;
            case 'archive':
                var payload2 = new FormData();
                payload2.append('action', 'toggle_archive');
                payload2.append('note_id', noteId);
                payload2.append('csrf_token', getCsrfToken());
                fetch('notes.php', { method: 'POST', body: payload2 })
                    .then(function (r) { return r.json(); })
                    .then(function (result) {
                        if (result.success) { showToast(result.message, 'success'); window.location.reload(); }
                        else showToast(result.message || 'Failed.', 'error');
                    });
                break;
            case 'duplicate':
                var payload3 = new FormData();
                payload3.append('action', 'duplicate_note');
                payload3.append('note_id', noteId);
                payload3.append('csrf_token', getCsrfToken());
                fetch('notes.php', { method: 'POST', body: payload3 })
                    .then(function (r) { return r.json(); })
                    .then(function (result) {
                        if (result.success) { showToast(result.message, 'success'); window.location.reload(); }
                        else showToast(result.message || 'Failed.', 'error');
                    });
                break;
            case 'delete':
                confirmNoteAction('Delete Note', 'Move this note to trash?', 'delete_note', noteId);
                break;
            case 'restore':
                var payload4 = new FormData();
                payload4.append('action', 'restore_note');
                payload4.append('note_id', noteId);
                payload4.append('csrf_token', getCsrfToken());
                fetch('notes.php', { method: 'POST', body: payload4 })
                    .then(function (r) { return r.json(); })
                    .then(function (result) {
                        if (result.success) { showToast(result.message, 'success'); window.location.reload(); }
                        else showToast(result.message || 'Failed.', 'error');
                    });
                break;
            case 'permanent_delete':
                confirmNoteAction('Permanent Delete', 'This cannot be undone. Delete forever?', 'permanent_delete_note', noteId);
                break;
        }
    });

    // Category modal actions
    document.getElementById('noteCategoryList').addEventListener('click', function (e) {
        var editBtn = e.target.closest('[data-edit-note-cat]');
        var delBtn = e.target.closest('[data-delete-note-cat]');
        if (editBtn) {
            var catId = editBtn.getAttribute('data-edit-note-cat');
            var payload = new FormData();
            payload.append('action', 'get_categories');
            payload.append('csrf_token', getCsrfToken());
            fetch('notes.php', { method: 'POST', body: payload })
                .then(function (r) { return r.json(); })
                .then(function (result) {
                    if (!result.success) return;
                    var cat = (result.categories || []).find(function (c) { return String(c.id) === String(catId); });
                    if (cat) {
                        document.getElementById('noteCategoryId').value = cat.id;
                        document.getElementById('noteCategoryName').value = cat.name;
                        document.getElementById('noteCategoryColor').value = cat.color || '#6366f1';
                    }
                });
            return;
        }
        if (delBtn) {
            var delCatId = delBtn.getAttribute('data-delete-note-cat');
            confirmNoteAction('Delete Category', 'Delete this note category?', 'delete_category', delCatId);
        }
    });

    openNoteModalBtn && openNoteModalBtn.addEventListener('click', function () {
        resetNoteForm();
        loadNoteCategories();
        openModal(noteModal);
    });

    openNoteCategoryModalBtn && openNoteCategoryModalBtn.addEventListener('click', function () {
        loadNoteCategories();
        openModal(noteCategoryModal);
    });

    emptyStateAddNote && emptyStateAddNote.addEventListener('click', function () {
        resetNoteForm();
        loadNoteCategories();
        openModal(noteModal);
    });

    noteForm && noteForm.addEventListener('submit', submitNoteForm);
    noteCategoryForm && noteCategoryForm.addEventListener('submit', submitNoteCategoryForm);
    noteConfirmActionBtn && noteConfirmActionBtn.addEventListener('click', performPendingNoteAction);

    bulkArchiveBtn && bulkArchiveBtn.addEventListener('click', function () { bulkNoteAction('archive'); });
    bulkDeleteBtn && bulkDeleteBtn.addEventListener('click', function () { bulkNoteAction('delete'); });

    loadNoteCategories();

    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeModal(noteModal);
            closeModal(noteViewModal);
            closeModal(noteCategoryModal);
            closeModal(noteConfirmModal);
        }
    });
})();
