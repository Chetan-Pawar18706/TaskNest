(function () {
    var shoppingForm = document.getElementById('shoppingAddForm');
    var shoppingList = document.getElementById('shoppingList');
    var clearCompletedBtn = document.getElementById('clearCompletedBtn');
    var editItemModal = document.getElementById('editItemModal');
    var editItemForm = document.getElementById('editItemForm');
    var shopConfirmModal = document.getElementById('shopConfirmModal');
    var shopConfirmDeleteBtn = document.getElementById('shopConfirmDeleteBtn');
    var pendingDeleteId = null;

    function getCsrfToken() { var t = document.querySelector('input[name="csrf_token"]'); return t ? t.value : ''; }
    function showToast(msg, type) { if (window.TaskNest && window.TaskNest.Toast) window.TaskNest.Toast.show(msg, type); else alert(msg); }
    function openModal(m) { if (!m) return; m.setAttribute('aria-hidden', 'false'); m.classList.add('is-open'); document.body.classList.add('modal-open'); }
    function closeModal(m) { if (!m) return; m.setAttribute('aria-hidden', 'true'); m.classList.remove('is-open'); document.body.classList.remove('modal-open'); }

    document.querySelectorAll('[data-close-modal]').forEach(function (btn) {
        btn.addEventListener('click', function () { closeModal(document.getElementById(this.getAttribute('data-close-modal'))); });
    });

    function addItem(e) {
        e.preventDefault();
        var nameInput = document.getElementById('shoppingItemName');
        var name = nameInput.value.trim();
        if (!name) return;

        var p = new FormData(shoppingForm);
        p.set('action', 'save_item');
        p.set('csrf_token', getCsrfToken());
        fetch('shopping.php', { method: 'POST', body: p }).then(function (r) { return r.json(); }).then(function (r) {
            if (!r.success) { showToast(r.message || 'Failed.', 'error'); return; }
            nameInput.value = '';
            document.getElementById('shoppingItemQty').value = 1;
            document.getElementById('shoppingItemPrice').value = '';
            document.getElementById('shoppingItemCategory').value = '';
            window.location.reload();
        });
    }

    function toggleComplete(itemId) {
        var p = new FormData();
        p.append('action', 'toggle_complete');
        p.append('item_id', itemId);
        p.append('csrf_token', getCsrfToken());
        fetch('shopping.php', { method: 'POST', body: p }).then(function (r) { return r.json(); }).then(function (r) {
            if (r.success) window.location.reload();
            else showToast(r.message || 'Failed.', 'error');
        });
    }

    function deleteItem(itemId) {
        var p = new FormData();
        p.append('action', 'delete_item');
        p.append('item_id', itemId);
        p.append('csrf_token', getCsrfToken());
        fetch('shopping.php', { method: 'POST', body: p }).then(function (r) { return r.json(); }).then(function (r) {
            closeModal(shopConfirmModal);
            if (r.success) { showToast(r.message || 'Deleted.', 'success'); window.location.reload(); }
            else showToast(r.message || 'Failed.', 'error');
        });
    }

    function fetchItem(id) {
        var p = new FormData();
        p.append('action', 'get_item');
        p.append('item_id', id);
        p.append('csrf_token', getCsrfToken());
        fetch('shopping.php', { method: 'POST', body: p }).then(function (r) { return r.json(); }).then(function (r) {
            if (!r.success) { showToast(r.message || 'Not found.', 'error'); return; }
            fillEditForm(r.item);
        });
    }

    function fillEditForm(item) {
        document.getElementById('editItemId').value = item.id;
        document.getElementById('editItemName').value = item.name || '';
        document.getElementById('editItemQty').value = item.quantity || 1;
        document.getElementById('editItemPrice').value = item.estimated_price || '';
        document.getElementById('editItemCategory').value = item.category || '';
        document.getElementById('editItemNotes').value = item.notes || '';
        openModal(editItemModal);
    }

    function submitEditForm(e) {
        e.preventDefault();
        var p = new FormData(editItemForm);
        p.set('action', 'save_item');
        p.set('csrf_token', getCsrfToken());
        fetch('shopping.php', { method: 'POST', body: p }).then(function (r) { return r.json(); }).then(function (r) {
            if (!r.success) { showToast(r.message || 'Save failed.', 'error'); return; }
            closeModal(editItemModal);
            showToast(r.message || 'Updated.', 'success');
            window.location.reload();
        });
    }

    function clearCompleted() {
        var p = new FormData();
        p.append('action', 'clear_completed');
        p.append('csrf_token', getCsrfToken());
        fetch('shopping.php', { method: 'POST', body: p }).then(function (r) { return r.json(); }).then(function (r) {
            if (r.success) { showToast(r.message, 'success'); window.location.reload(); }
            else showToast(r.message || 'Failed.', 'error');
        });
    }

    shoppingForm && shoppingForm.addEventListener('submit', addItem);
    editItemForm && editItemForm.addEventListener('submit', submitEditForm);

    shoppingList && shoppingList.addEventListener('click', function (e) {
        var cb = e.target.closest('.shopping-checkbox');
        if (cb) { toggleComplete(cb.getAttribute('data-id')); return; }
        var editBtn = e.target.closest('button[data-action="edit"]');
        if (editBtn) { fetchItem(editBtn.getAttribute('data-id')); return; }
        var delBtn = e.target.closest('button[data-action="delete"]');
        if (delBtn) {
            pendingDeleteId = delBtn.getAttribute('data-id');
            openModal(shopConfirmModal);
            return;
        }
    });

    shopConfirmDeleteBtn && shopConfirmDeleteBtn.addEventListener('click', function () {
        if (pendingDeleteId) { deleteItem(pendingDeleteId); pendingDeleteId = null; }
    });

    clearCompletedBtn && clearCompletedBtn.addEventListener('click', clearCompleted);

    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeModal(editItemModal);
            closeModal(shopConfirmModal);
        }
    });
})();
