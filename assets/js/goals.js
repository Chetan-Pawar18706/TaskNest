(function () {
    var goalModal = document.getElementById('goalModal');
    var goalForm = document.getElementById('goalForm');
    var goalModalTitle = document.getElementById('goalModalTitle');
    var progressModal = document.getElementById('goalProgressModal');
    var progressForm = document.getElementById('goalProgressForm');
    var openGoalModalBtn = document.getElementById('openGoalModal');
    var emptyStateAdd = document.getElementById('emptyStateAddGoal');
    var goalsList = document.getElementById('goalsList');

    function getCsrfToken() { var t = document.querySelector('#goalForm input[name="csrf_token"]'); return t ? t.value : ''; }
    function openModal(m) { if (!m) return; m.setAttribute('aria-hidden', 'false'); m.classList.add('is-open'); document.body.classList.add('modal-open'); }
    function closeModal(m) { if (!m) return; m.setAttribute('aria-hidden', 'true'); m.classList.remove('is-open'); document.body.classList.remove('modal-open'); }
    document.querySelectorAll('[data-close-modal]').forEach(function (b) { b.addEventListener('click', function () { closeModal(document.getElementById(this.getAttribute('data-close-modal'))); }); });
    function showToast(msg, type) { if (window.TaskNest && window.TaskNest.Toast) window.TaskNest.Toast.show(msg, type); else alert(msg); }

    function resetForm() { goalForm.reset(); document.getElementById('goalId').value = ''; goalModalTitle.textContent = 'Add Goal'; }
    function fillForm(g) {
        resetForm();
        document.getElementById('goalId').value = g.id;
        document.getElementById('goalTitle').value = g.title || '';
        document.getElementById('goalDescription').value = g.description || '';
        document.getElementById('goalCategory').value = g.category || '';
        document.getElementById('goalTargetValue').value = g.target_value || '';
        document.getElementById('goalCurrentValue').value = g.current_value || '';
        document.getElementById('goalUnit').value = g.unit || '';
        document.getElementById('goalStartDate').value = g.start_date || '';
        document.getElementById('goalDueDate').value = g.due_date || '';
        document.getElementById('goalStatus').value = g.status || 'active';
        goalModalTitle.textContent = 'Edit Goal';
        openModal(goalModal);
    }

    function submitForm(e) {
        e.preventDefault();
        var p = new FormData(goalForm); p.set('action', 'save_goal'); p.set('csrf_token', getCsrfToken());
        fetch('goals.php', { method: 'POST', body: p }).then(function (r) { return r.json(); }).then(function (r) {
            if (!r.success) { showToast(r.message || 'Failed.', 'error'); return; }
            closeModal(goalModal); showToast(r.message || 'Saved.', 'success'); window.location.reload();
        });
    }

    function openProgressModal(goalId, currentVal) {
        document.getElementById('progressGoalId').value = goalId;
        document.getElementById('progressCurrentValue').value = currentVal || 0;
        openModal(progressModal);
    }

    function submitProgress(e) {
        e.preventDefault();
        var p = new FormData(progressForm); p.set('action', 'update_progress'); p.set('csrf_token', getCsrfToken());
        fetch('goals.php', { method: 'POST', body: p }).then(function (r) { return r.json(); }).then(function (r) {
            if (!r.success) { showToast(r.message || 'Failed.', 'error'); return; }
            closeModal(progressModal); showToast(r.message || 'Updated.', 'success'); window.location.reload();
        });
    }

    function deleteGoal(id) {
        if (!confirm('Delete this goal?')) return;
        var p = new FormData(); p.append('action', 'delete_goal'); p.append('goal_id', id); p.append('csrf_token', getCsrfToken());
        fetch('goals.php', { method: 'POST', body: p }).then(function (r) { return r.json(); }).then(function (r) {
            if (r.success) { showToast(r.message, 'success'); window.location.reload(); }
            else showToast(r.message || 'Failed.', 'error');
        });
    }

    goalsList && goalsList.addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-action]');
        if (!btn) return;
        var action = btn.getAttribute('data-action'); var id = btn.getAttribute('data-id');
        if (action === 'edit') {
            var card = btn.closest('.goal-card');
            fillForm({ id: id, title: card.getAttribute('data-title'), description: card.getAttribute('data-description'), category: card.getAttribute('data-category'), target_value: card.getAttribute('data-target'), current_value: card.getAttribute('data-current'), unit: card.getAttribute('data-unit'), start_date: card.getAttribute('data-start'), due_date: card.getAttribute('data-due'), status: card.getAttribute('data-status') });
        }
        if (action === 'progress') openProgressModal(id, btn.getAttribute('data-current'));
        if (action === 'delete') deleteGoal(id);
    });

    openGoalModalBtn && openGoalModalBtn.addEventListener('click', function () { resetForm(); openModal(goalModal); });
    emptyStateAdd && emptyStateAdd.addEventListener('click', function () { resetForm(); openModal(goalModal); });
    goalForm && goalForm.addEventListener('submit', submitForm);
    progressForm && progressForm.addEventListener('submit', submitProgress);

    window.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeModal(goalModal); closeModal(progressModal); } });
})();
