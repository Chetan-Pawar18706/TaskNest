(function() {
    'use strict';

    const state = {
        calendarMonth: new Date().getMonth(),
        calendarYear: new Date().getFullYear()
    };

    function initDashboard() {
        initCharts();
        renderCalendar();
        bindQuickActions();
        bindSearch();
    }

    function initCharts() {
        const expenseCanvas = document.getElementById('expenseChart');
        const completionCanvas = document.getElementById('completionChart');
        const habitCanvas = document.getElementById('habitChart');

        if (typeof Chart === 'undefined') {
            return;
        }

        if (expenseCanvas) {
            new Chart(expenseCanvas, {
                type: 'bar',
                data: {
                    labels: JSON.parse(expenseCanvas.dataset.labels || '[]'),
                    datasets: [{
                        label: 'Expenses',
                        data: JSON.parse(expenseCanvas.dataset.values || '[]'),
                        backgroundColor: ['#6366f1', '#818cf8', '#8b5cf6', '#a78bfa', '#10b981', '#34d399']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        }

        if (completionCanvas) {
            new Chart(completionCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'Pending'],
                    datasets: [{
                        data: JSON.parse(completionCanvas.dataset.values || '[0,0]'),
                        backgroundColor: ['#10b981', '#f59e0b']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
            });
        }

        if (habitCanvas) {
            new Chart(habitCanvas, {
                type: 'line',
                data: {
                    labels: JSON.parse(habitCanvas.dataset.labels || '[]'),
                    datasets: [{
                        label: 'Completion',
                        data: JSON.parse(habitCanvas.dataset.values || '[]'),
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.14)',
                        tension: 0.35,
                        fill: true
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
        }

        window.addEventListener('resize', () => {
            if (typeof Chart !== 'undefined') {
                Object.values(Chart.instances).forEach(function(instance) {
                    instance.resize();
                });
            }
        });
    }

    function renderCalendar() {
        const calendar = document.getElementById('dashboardCalendar');
        if (!calendar) {
            return;
        }

        const firstDay = new Date(state.calendarYear, state.calendarMonth, 1);
        const lastDay = new Date(state.calendarYear, state.calendarMonth + 1, 0);
        const startOffset = firstDay.getDay();
        const totalDays = lastDay.getDate();
        const today = new Date();
        const monthLabel = calendar.dataset.monthLabel || firstDay.toLocaleString('en', { month: 'long', year: 'numeric' });

        let html = '<div class="calendar-grid">';
        const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        days.forEach((day) => {
            html += '<div class="calendar-day-name">' + day + '</div>';
        });

        for (let i = 0; i < startOffset; i += 1) {
            html += '<div class="calendar-cell is-muted"></div>';
        }

        for (let day = 1; day <= totalDays; day += 1) {
            const isToday = today.getMonth() === state.calendarMonth && today.getFullYear() === state.calendarYear && today.getDate() === day;
            html += '<div class="calendar-cell' + (isToday ? ' is-today' : '') + '">';
            html += '<span class="calendar-cell-number">' + day + '</span>';
            html += '<span class="calendar-event-dot"></span>';
            html += '</div>';
        }

        html += '</div>';
        calendar.innerHTML = html;
        const title = document.getElementById('calendarMonthTitle');
        if (title) {
            title.textContent = monthLabel;
        }
    }

    function bindQuickActions() {
        document.querySelectorAll('[data-modal-action]').forEach((button) => {
            button.addEventListener('click', () => {
                const title = button.getAttribute('data-modal-title') || 'Quick Action';
                const message = button.getAttribute('data-modal-message') || 'This feature will be available in a future update.';
                if (typeof TaskNest !== 'undefined' && TaskNest.Modal) {
                    TaskNest.Modal.create('quick-action', { title, content: '<p>' + message + '</p>', size: 'md' }).open();
                } else {
                    window.alert(message);
                }
            });
        });
    }

    function bindSearch() {
        const searchInput = document.getElementById('dashboardSearch');
        const suggestions = document.getElementById('searchSuggestions');
        if (!searchInput || !suggestions) {
            return;
        }

        searchInput.addEventListener('focus', () => suggestions.classList.add('show'));
        searchInput.addEventListener('blur', () => setTimeout(() => suggestions.classList.remove('show'), 150));
        searchInput.addEventListener('input', (event) => {
            const value = event.target.value.trim().toLowerCase();
            const items = suggestions.querySelectorAll('button');
            items.forEach((item) => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(value) ? 'block' : 'none';
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboard);
    } else {
        initDashboard();
    }
})();
