(function () {
    var habitModal = document.getElementById('habitModal');
    var habitForm = document.getElementById('habitForm');
    var habitModalTitle = document.getElementById('habitModalTitle');
    var openHabitModalBtn = document.getElementById('openHabitModal');
    var emptyStateAdd = document.getElementById('emptyStateAddHabit');
    var habitsGrid = document.getElementById('habitsGrid');

    function getCsrfToken() { var t = document.querySelector('#habitForm input[name="csrf_token"]'); return t ? t.value : ''; }
    function openModal(m) { if (!m) return; m.setAttribute('aria-hidden', 'false'); m.classList.add('is-open'); document.body.classList.add('modal-open'); }
    function closeModal(m) { if (!m) return; m.setAttribute('aria-hidden', 'true'); m.classList.remove('is-open'); document.body.classList.remove('modal-open'); }
    document.querySelectorAll('[data-close-modal]').forEach(function (b) { b.addEventListener('click', function () { closeModal(document.getElementById(this.getAttribute('data-close-modal'))); }); });
    function showToast(msg, type) { if (window.TaskNest && window.TaskNest.Toast) window.TaskNest.Toast.show(msg, type); else alert(msg); }

    function resetForm() { habitForm.reset(); document.getElementById('habitId').value = ''; habitModalTitle.textContent = 'Add Habit'; }
    function fillForm(h) { resetForm(); document.getElementById('habitId').value = h.id; document.getElementById('habitName').value = h.name || ''; document.getElementById('habitDescription').value = h.description || ''; document.getElementById('habitFrequency').value = h.frequency || 'daily'; document.getElementById('habitTargetCount').value = h.target_count || 1; document.getElementById('habitColor').value = h.color || '#6366f1'; habitModalTitle.textContent = 'Edit Habit'; openModal(habitModal); }

    function submitForm(e) {
        e.preventDefault();
        var p = new FormData(habitForm); p.set('action', 'save_habit'); p.set('csrf_token', getCsrfToken());
        fetch('habits.php', { method: 'POST', body: p }).then(function (r) { return r.json(); }).then(function (r) {
            if (!r.success) { showToast(r.message || 'Failed.', 'error'); return; }
            closeModal(habitModal); showToast(r.message || 'Saved.', 'success'); window.location.reload();
        });
    }

    function logHabit(habitId) {
        var p = new FormData(); p.append('action', 'log_habit'); p.append('habit_id', habitId); p.append('log_date', new Date().toISOString().slice(0, 10)); p.append('csrf_token', getCsrfToken());
        fetch('habits.php', { method: 'POST', body: p }).then(function (r) { return r.json(); }).then(function (r) {
            if (r.success) { showToast(r.message || 'Logged!', 'success'); window.location.reload(); }
            else showToast(r.message || 'Failed.', 'error');
        });
    }

    function deleteHabit(id) {
        if (!confirm('Delete this habit?')) return;
        var p = new FormData(); p.append('action', 'delete_habit'); p.append('habit_id', id); p.append('csrf_token', getCsrfToken());
        fetch('habits.php', { method: 'POST', body: p }).then(function (r) { return r.json(); }).then(function (r) {
            if (r.success) { showToast(r.message, 'success'); window.location.reload(); }
            else showToast(r.message || 'Failed.', 'error');
        });
    }

    habitsGrid && habitsGrid.addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-action]');
        if (!btn) return;
        var action = btn.getAttribute('data-action'); var id = btn.getAttribute('data-id');
        if (action === 'edit') {
            var p = new FormData();
            p.append('action', 'get_habit');
            p.append('habit_id', id);
            p.append('csrf_token', getCsrfToken());
            fetch('habits.php', { method: 'POST', body: p })
                .then(function (r) { return r.json(); })
                .then(function (result) {
                    if (!result.success) { showToast(result.message || 'Unable to load habit.', 'error'); return; }
                    var h = result.habit;
                    document.getElementById('habitId').value = h.id;
                    document.getElementById('habitName').value = h.name || '';
                    document.getElementById('habitDescription').value = h.description || '';
                    document.getElementById('habitFrequency').value = h.frequency || 'daily';
                    document.getElementById('habitTargetCount').value = h.target_count || 1;
                    document.getElementById('habitColor').value = h.color || '#6366f1';
                    document.getElementById('habitModalTitle').textContent = 'Edit Habit';
                    openModal(habitModal);
                })
                .catch(function () { showToast('Unable to load habit.', 'error'); });
            return;
        }
        if (action === 'log') logHabit(id);
        if (action === 'delete') deleteHabit(id);

    });

    var dayElems = document.querySelectorAll('.habit-day[data-habit-id]');
    dayElems.forEach(function (day) {
        day.addEventListener('click', function () {
            var habitId = day.getAttribute('data-habit-id');
            var logDate = day.getAttribute('data-date');
            var p = new FormData(); p.append('action', 'log_habit'); p.append('habit_id', habitId); p.append('log_date', logDate); p.append('csrf_token', getCsrfToken());
            fetch('habits.php', { method: 'POST', body: p }).then(function (r) { return r.json(); }).then(function (r) {
                if (r.success) window.location.reload();
                else showToast(r.message || 'Failed.', 'error');
            });
        });
    });

    openHabitModalBtn && openHabitModalBtn.addEventListener('click', function () { resetForm(); openModal(habitModal); });
    emptyStateAdd && emptyStateAdd.addEventListener('click', function () { resetForm(); openModal(habitModal); });
    habitForm && habitForm.addEventListener('submit', submitForm);

    window.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(habitModal); });
})();
