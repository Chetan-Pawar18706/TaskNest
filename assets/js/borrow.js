(function () {
    var borrowModal = document.getElementById('borrowModal');
    var borrowConfirmModal = document.getElementById('borrowConfirmModal');
    var borrowForm = document.getElementById('borrowForm');
    var borrowModalTitle = document.getElementById('borrowModalTitle');
    var borrowItemType = document.getElementById('borrowItemType');
    var borrowAmountGroup = document.getElementById('borrowAmountGroup');
    var borrowConfirmActionBtn = document.getElementById('borrowConfirmActionBtn');
    var borrowConfirmTitle = document.getElementById('borrowConfirmTitle');
    var borrowConfirmBody = document.getElementById('borrowConfirmBody');
    var openBorrowModalBtn = document.getElementById('openBorrowModal');
    var emptyStateAddBorrow = document.getElementById('emptyStateAddBorrow');

    var pendingAction = null;

    function getCsrfToken() {
        var t = document.querySelector('#borrowForm input[name="csrf_token"]');
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

    // Toggle amount field visibility
    borrowItemType && borrowItemType.addEventListener('change', function () {
        borrowAmountGroup.style.display = borrowItemType.value === 'money' ? 'block' : 'none';
    });

    function resetBorrowForm() {
        borrowForm.reset();
        document.getElementById('borrowId').value = '';
        borrowModalTitle.textContent = 'Add Record';
    if (borrowAmountGroup) borrowAmountGroup.style.display = 'none';
    }

    function fillBorrowForm(item) {
        resetBorrowForm();
        document.getElementById('borrowId').value = item.id;
        document.getElementById('borrowTitle').value = item.title || '';
        document.getElementById('borrowType').value = item.type || 'borrowed';
        document.getElementById('borrowItemType').value = item.item_type || 'item';
        document.getElementById('borrowAmount').value = item.amount || '';
        document.getElementById('borrowPerson').value = item.person_name || '';
        document.getElementById('borrowContact').value = item.person_contact || '';
        document.getElementById('borrowDescription').value = item.description || '';
        document.getElementById('borrowDate').value = item.borrow_date || '';
        document.getElementById('borrowReturnDate').value = item.return_date || '';
        borrowAmountGroup.style.display = item.item_type === 'money' ? 'block' : 'none';
        borrowModalTitle.textContent = 'Edit Record';
        openModal(borrowModal);
    }

    function fetchBorrow(id) {
        var payload = new FormData();
        payload.append('action', 'get_borrow');
        payload.append('borrow_id', id);
        payload.append('csrf_token', getCsrfToken());
        fetch('borrow.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (!result.success) { showToast(result.message || 'Not found.', 'error'); return; }
                fillBorrowForm(result.item);
            });
    }

    function submitBorrowForm(e) {
        e.preventDefault();
        var payload = new FormData(borrowForm);
        payload.set('action', 'save_borrow');
        payload.set('csrf_token', getCsrfToken());
        fetch('borrow.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (!result.success) { showToast(result.message || 'Save failed.', 'error'); return; }
                closeModal(borrowModal);
                showToast(result.message || 'Saved.', 'success');
                window.location.reload();
            });
    }

    function confirmBorrowAction(title, body, action, id) {
        pendingAction = { action: action, id: id };
        borrowConfirmTitle.textContent = title;
        borrowConfirmBody.textContent = body;
        openModal(borrowConfirmModal);
    }

    function performPendingBorrowAction() {
        if (!pendingAction) return;
        var payload = new FormData();
        payload.append('action', pendingAction.action);
        payload.append('borrow_id', pendingAction.id);
        payload.append('csrf_token', getCsrfToken());
        fetch('borrow.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                closeModal(borrowConfirmModal);
                if (!result.success) { showToast(result.message || 'Failed.', 'error'); return; }
                showToast(result.message || 'Done.', 'success');
                window.location.reload();
            });
    }

    // Event delegation
    var borrowList = document.getElementById('borrowList');
    if (borrowList) borrowList.addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-action]');
        if (!btn) return;
        var action = btn.getAttribute('data-action');
        var id = btn.getAttribute('data-id');
        if (action === 'edit') fetchBorrow(id);
        else if (action === 'mark_returned') confirmBorrowAction('Mark Returned', 'Mark this item as returned?', 'mark_returned', id);
        else if (action === 'delete') confirmBorrowAction('Delete Record', 'Delete this record?', 'delete_borrow', id);
    });

    openBorrowModalBtn && openBorrowModalBtn.addEventListener('click', function () { resetBorrowForm(); openModal(borrowModal); });
    emptyStateAddBorrow && emptyStateAddBorrow.addEventListener('click', function () { resetBorrowForm(); openModal(borrowModal); });

    borrowForm && borrowForm.addEventListener('submit', submitBorrowForm);
    borrowConfirmActionBtn && borrowConfirmActionBtn.addEventListener('click', performPendingBorrowAction);

    // Initialize amount field visibility
    if (borrowAmountGroup) borrowAmountGroup.style.display = 'none';

    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeModal(borrowModal);
            closeModal(borrowConfirmModal);
        }
    });
})();
