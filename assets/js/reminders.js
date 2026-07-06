document.addEventListener('DOMContentLoaded', function() {
    // Load reminders on list page
    if (document.getElementById('remindersGrid')) {
        loadReminders();
        bindFilters();
        bindBulkActions();
    }
});

function bindFilters() {
    var search = document.getElementById('reminderSearch');
    var priority = document.getElementById('priorityFilter');
    var category = document.getElementById('categoryFilter');
    var status = document.getElementById('statusFilter');

    if (search) search.addEventListener('input', debounce(loadReminders, 300));
    if (priority) priority.addEventListener('change', loadReminders);
    if (category) category.addEventListener('change', loadReminders);
    if (status) status.addEventListener('change', loadReminders);
}

function bindBulkActions() {
    var selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            var checked = this.checked;
            document.querySelectorAll('.reminder-select').forEach(function(cb) {
                cb.checked = checked;
                var card = cb.closest('.reminder-card');
                if (card) card.classList.toggle('selected', checked);
            });
            updateBulkActions();
        });
    }

    var bulkDelete = document.getElementById('bulkDeleteBtn');
    if (bulkDelete) {
        bulkDelete.addEventListener('click', function() {
            var selected = [];
            document.querySelectorAll('.reminder-select:checked').forEach(function(cb) { selected.push(cb.value); });
            if (selected.length === 0) return;
            if (!confirm('Delete ' + selected.length + ' selected reminders?')) return;

            var formData = new FormData();
            formData.append('action', 'bulk_action');
            formData.append('type', 'delete');
            formData.append('ids', JSON.stringify(selected));
            formData.append('csrf_token', csrfToken);

            fetch(siteUrl + '/modules/reminders/reminders.php', { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) { if (data.success) loadReminders(); });
        });
    }
}

function loadReminders() {
    var grid = document.getElementById('remindersGrid');
    var emptyState = document.getElementById('remindersEmpty');
    if (!grid) return;

    var search = document.getElementById('reminderSearch');
    var priority = document.getElementById('priorityFilter');
    var category = document.getElementById('categoryFilter');
    var status = document.getElementById('statusFilter');

    var formData = new FormData();
    formData.append('action', 'get_reminders');
    formData.append('search', search ? search.value : '');
    formData.append('priority', priority ? priority.value : '');
    formData.append('category', category ? category.value : '');
    formData.append('status', status ? status.value : '');

    fetch(siteUrl + '/modules/reminders/reminders.php', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) { grid.innerHTML = '<div class="reminders-loading">' + (data.message || 'Error') + '</div>'; return; }

        var reminders = data.reminders || [];
        if (reminders.length === 0) { grid.innerHTML = ''; if (emptyState) emptyState.style.display = 'block'; return; }
        if (emptyState) emptyState.style.display = 'none';

        var now = new Date();
        var html = '';
        reminders.forEach(function(r) {
            var reminderDate = new Date(r.reminder_date + 'T' + r.reminder_time);
            var diffMs = reminderDate - now;
            var diffMin = Math.floor(diffMs / 60000);
            var isOverdue = diffMs < 0;
            var isToday = r.reminder_date === now.toISOString().split('T')[0];

            var cardClass = 'reminder-card';
            if (isOverdue) cardClass += ' overdue';
            else if (isToday) cardClass += ' today';

            var timeLabel = '';
            var badgeClass = 'normal';
            if (isOverdue) {
                var absMin = Math.abs(diffMin);
                if (absMin < 60) timeLabel = absMin + 'm overdue';
                else if (absMin < 1440) timeLabel = Math.floor(absMin / 60) + 'h overdue';
                else timeLabel = Math.floor(absMin / 1440) + 'd overdue';
                badgeClass = 'overdue';
            } else if (diffMin <= 60) {
                timeLabel = diffMin + ' min';
                badgeClass = 'urgent';
            } else if (diffMin <= 180) {
                timeLabel = (diffMin / 60).toFixed(1) + ' hrs';
                badgeClass = 'soon';
            } else {
                timeLabel = Math.floor(diffMin / 60) + ' hrs';
                badgeClass = 'normal';
            }

            var repeatLabel = r.repeat_type !== 'none' ? '<span class="reminder-repeat-badge">' + r.repeat_type + '</span>' : '';
            var categoryLabel = r.category ? '<span class="reminder-category-badge">' + escapeHtml(r.category) + '</span>' : '';
            var desc = r.description ? '<div class="reminder-info-desc">' + escapeHtml(r.description) + '</div>' : '';

            html += '<div class="' + cardClass + '" data-id="' + r.id + '">';
            html += '<input type="checkbox" class="reminder-select" value="' + r.id + '" onchange="updateBulkActions()">';
            html += '<div class="reminder-priority-dot ' + r.priority + '"></div>';
            html += '<div class="reminder-info">';
            html += '<div class="reminder-info-title">' + escapeHtml(r.title) + '</div>';
            html += '<div class="reminder-info-meta">';
            html += '<span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg> ' + r.reminder_date + '</span>';
            html += '<span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg> ' + r.reminder_time.substring(0, 5) + '</span>';
            html += categoryLabel + repeatLabel;
            html += '<span class="reminder-time-badge ' + badgeClass + '">' + timeLabel + '</span>';
            html += '</div>';
            html += desc;
            html += '</div>';
            html += '<div class="reminder-actions">';
            html += '<a href="' + siteUrl + '/modules/reminders/reminders-edit.php?id=' + r.id + '" class="btn btn-icon" title="Edit"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></a>';
            html += '<button class="btn btn-icon" onclick="deleteReminder(' + r.id + ')" title="Delete"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button>';
            html += '</div>';
            html += '</div>';
        });

        grid.innerHTML = html;
    })
    .catch(function() { grid.innerHTML = '<div class="reminders-loading">Error loading reminders</div>'; });
}

function deleteReminder(id) {
    if (!confirm('Delete this reminder?')) return;
    var formData = new FormData();
    formData.append('action', 'delete_reminder');
    formData.append('reminder_id', id);
    formData.append('csrf_token', csrfToken);

    fetch(siteUrl + '/modules/reminders/reminders.php', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) { if (data.success) loadReminders(); else alert(data.message); });
}

function updateBulkActions() {
    var selected = document.querySelectorAll('.reminder-select:checked');
    var bulkBar = document.getElementById('bulkActions');
    var countEl = document.getElementById('selectedCount');
    if (bulkBar && countEl) {
        if (selected.length > 0) { bulkBar.style.display = 'flex'; countEl.textContent = selected.length + ' selected'; }
        else { bulkBar.style.display = 'none'; }
    }
    document.querySelectorAll('.reminder-card').forEach(function(card) {
        var cb = card.querySelector('.reminder-select');
        card.classList.toggle('selected', cb && cb.checked);
    });
}

// Bell icon functionality
function initBellIcon() {
    var btn = document.getElementById('notificationsBtn');
    if (!btn) return;

    var wrapper = btn.closest('.navbar-notifications-wrapper') || btn.parentElement;
    wrapper.style.position = 'relative';

    var dropdown = document.createElement('div');
    dropdown.className = 'bell-dropdown';
    dropdown.id = 'bellDropdown';
    dropdown.setAttribute('tabindex', '-1');
    wrapper.appendChild(dropdown);

    function openBell() {
        loadBellDropdown();
        dropdown.classList.add('show');
        btn.classList.add('bell-active');
    }

    function closeBell() {
        dropdown.classList.remove('show');
        btn.classList.remove('bell-active');
    }

    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        if (dropdown.classList.contains('show')) {
            closeBell();
        } else {
            closeAllDropdowns();
            openBell();
        }
    });

    document.addEventListener('click', function(e) {
        if (!dropdown.contains(e.target) && !btn.contains(e.target)) {
            closeBell();
        }
    });

    dropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    loadBellCount();
}

function loadBellCount() {
    var formData = new FormData();
    formData.append('action', 'get_bell');
    formData.append('csrf_token', typeof csrfToken !== 'undefined' ? csrfToken : '');

    fetch(siteUrl + '/modules/reminders/reminders.php', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) return;
        var badge = document.querySelector('.notification-badge');
        if (badge) {
            var total = data.count + (data.overdue ? data.overdue.length : 0);
            badge.textContent = total;
            badge.style.display = total > 0 ? 'flex' : 'none';
        }
    });
}

function loadBellDropdown() {
    var dropdown = document.getElementById('bellDropdown');
    if (!dropdown) return;

    dropdown.innerHTML = '<div class="bell-dropdown-header"><h3>Reminders</h3></div><div class="bell-dropdown-list"><div class="bell-dropdown-empty">Loading...</div></div>';

    var formData = new FormData();
    formData.append('action', 'get_bell');
    formData.append('csrf_token', typeof csrfToken !== 'undefined' ? csrfToken : '');

    fetch(siteUrl + '/modules/reminders/reminders.php', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) { dropdown.innerHTML = '<div class="bell-dropdown-empty">Error loading</div>'; return; }

        var overdue = data.overdue || [];
        var upcoming = data.upcoming || [];
        var all = [];

        overdue.forEach(function(r) { r.urgency = 'overdue'; all.push(r); });
        upcoming.forEach(function(r) { all.push(r); });

        if (all.length === 0) {
            dropdown.innerHTML = '<div class="bell-dropdown-header"><h3>Reminders</h3></div><div class="bell-dropdown-empty"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:8px;opacity:0.4"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg><br>No upcoming reminders</div><div class="bell-dropdown-footer"><a href="' + siteUrl + '/modules/reminders/reminders-add.php">+ Add Reminder</a></div>';
            return;
        }

        var html = '<div class="bell-dropdown-header"><h3>Reminders <span class="bell-header-count">(' + all.length + ')</span></h3></div>';
        html += '<div class="bell-dropdown-list">';

        all.forEach(function(r) {
            var timeStr = r.reminder_date + ' ' + r.reminder_time.substring(0, 5);
            var label = '';
            if (r.urgency === 'overdue') label = 'Overdue';
            else if (r.time_label) label = r.time_label;

            var itemClass = 'bell-dropdown-item';
            if (r.urgency === 'overdue') itemClass += ' overdue-item';

            html += '<a href="' + siteUrl + '/modules/reminders/reminders-edit.php?id=' + r.id + '" class="' + itemClass + '">';
            html += '<div class="bell-item-dot ' + r.urgency + '"></div>';
            html += '<div class="bell-item-content">';
            html += '<div class="bell-item-title">' + escapeHtml(r.title) + '</div>';
            html += '<div class="bell-item-time">';
            html += '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';
            html += timeStr;
            html += '</div>';
            html += '</div>';
            if (label) html += '<span class="bell-item-badge ' + r.urgency + '">' + label + '</span>';
            html += '</a>';
        });

        html += '</div>';
        html += '<div class="bell-dropdown-footer"><a href="' + siteUrl + '/reminders.php">View All Reminders</a></div>';
        dropdown.innerHTML = html;
    });
}

function closeAllDropdowns() {
    document.querySelectorAll('.bell-dropdown').forEach(function(d) { d.classList.remove('show'); });
    document.querySelectorAll('.bell-active').forEach(function(b) { b.classList.remove('bell-active'); });
}

function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

function debounce(func, wait) {
    var timeout;
    return function() {
        var context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(function() { func.apply(context, args); }, wait);
    };
}

// Auto-init bell on every page
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBellIcon);
} else {
    initBellIcon();
}
