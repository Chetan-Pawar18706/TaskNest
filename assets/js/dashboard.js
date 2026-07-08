(function() {
    'use strict';

    const state = {
        calendarMonth: new Date().getMonth(),
        calendarYear: new Date().getFullYear(),
        chartInstance: null
    };

    function initDashboard() {
        initCharts();
        renderCalendar();
        bindSearch();
        bindChartRange();
    }

    function initCharts() {
        if (typeof Chart === 'undefined') return;
        renderExpenseChart();

        var completionCanvas = document.getElementById('completionChart');
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

        var habitCanvas = document.getElementById('habitChart');
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
    }

    function renderExpenseChart(months) {
        var canvas = document.getElementById('expenseChart');
        if (!canvas) return;

        if (months) {
            var formData = new FormData();
            formData.append('action', 'chart_data');
            formData.append('months', months);
            formData.append('csrf_token', typeof csrfToken !== 'undefined' ? csrfToken : '');
            fetch(siteUrl + '/modules/expenses/expenses.php', { method: 'POST', body: formData })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        canvas.dataset.labels = JSON.stringify(data.labels);
                        canvas.dataset.income = JSON.stringify(data.income);
                        canvas.dataset.expenses = JSON.stringify(data.expenses);
                    }
                    drawExpenseChart(canvas);
                })
                .catch(function() { drawExpenseChart(canvas); });
        } else {
            drawExpenseChart(canvas);
        }
    }

    function drawExpenseChart(canvas) {
        if (typeof Chart === 'undefined') return;
        if (state.chartInstance) { state.chartInstance.destroy(); }

        state.chartInstance = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: JSON.parse(canvas.dataset.labels || '[]'),
                datasets: [
                    {
                        label: 'Income',
                        data: JSON.parse(canvas.dataset.income || '[]'),
                        backgroundColor: '#10b981',
                        borderRadius: 4
                    },
                    {
                        label: 'Expenses',
                        data: JSON.parse(canvas.dataset.expenses || '[]'),
                        backgroundColor: '#ef4444',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    function bindChartRange() {
        document.querySelectorAll('.chart-range-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.chart-range-btn').forEach(function(b) { b.classList.remove('active'); });
                btn.classList.add('active');
                renderExpenseChart(parseInt(btn.getAttribute('data-months')));
            });
        });
    }

    function renderCalendar() {
        var calendar = document.getElementById('dashboardCalendar');
        if (!calendar) return;

        var firstDay = new Date(state.calendarYear, state.calendarMonth, 1);
        var lastDay = new Date(state.calendarYear, state.calendarMonth + 1, 0);
        var startOffset = firstDay.getDay();
        var totalDays = lastDay.getDate();
        var today = new Date();
        var monthLabel = calendar.dataset.monthLabel || firstDay.toLocaleString('en', { month: 'long', year: 'numeric' });

        var events = [];
        try { events = JSON.parse(calendar.dataset.events || '[]'); } catch(e) {}

        var eventsByDate = {};
        events.forEach(function(ev) {
            if (!eventsByDate[ev.event_date]) eventsByDate[ev.event_date] = [];
            eventsByDate[ev.event_date].push(ev);
        });

        var html = '<div class="calendar-grid">';
        var days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        days.forEach(function(day) {
            html += '<div class="calendar-day-name">' + day + '</div>';
        });

        for (var i = 0; i < startOffset; i++) {
            html += '<div class="calendar-cell is-muted"></div>';
        }

        for (var day = 1; day <= totalDays; day++) {
            var isToday = today.getMonth() === state.calendarMonth && today.getFullYear() === state.calendarYear && today.getDate() === day;
            var dateStr = state.calendarYear + '-' + String(state.calendarMonth + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
            var hasEvent = eventsByDate[dateStr] && eventsByDate[dateStr].length > 0;
            var eventTitles = hasEvent ? eventsByDate[dateStr].map(function(e) { return typeof TaskNest !== 'undefined' ? TaskNest.encodeHTML(e.title) : e.title; }).join(', ') : '';

            html += '<div class="calendar-cell' + (isToday ? ' is-today' : '') + (hasEvent ? ' has-event' : '') + '" data-date="' + dateStr + '">';
            html += '<span class="calendar-cell-number">' + day + '</span>';
            if (hasEvent) {
                html += '<span class="calendar-event-dot"></span>';
            }
            html += '</div>';
        }

        html += '</div>';
        html += '<div class="calendar-tooltip" id="calendarTooltip"></div>';
        calendar.innerHTML = html;

        var tooltip = document.getElementById('calendarTooltip');
        calendar.querySelectorAll('.calendar-cell.has-event').forEach(function(cell) {
            cell.addEventListener('mouseenter', function(e) {
                var dateStr = cell.getAttribute('data-date');
                var evts = eventsByDate[dateStr];
                if (!evts || evts.length === 0) return;
                var html2 = '<div class="tooltip-header">' + dateStr + '</div>';
                evts.forEach(function(ev) {
                    var safeTitle = typeof TaskNest !== 'undefined' ? TaskNest.encodeHTML(ev.title) : ev.title;
                var safeDesc = ev.description ? (typeof TaskNest !== 'undefined' ? TaskNest.encodeHTML(ev.description) : ev.description) : '';
                html2 += '<div class="tooltip-item"><span class="tooltip-dot"></span>' + safeTitle + (safeDesc ? '<span class="tooltip-desc">' + safeDesc + '</span>' : '') + '</div>';
                });
                tooltip.innerHTML = html2;
                tooltip.style.display = 'block';
                var rect = cell.getBoundingClientRect();
                var calRect = calendar.getBoundingClientRect();
                tooltip.style.left = (rect.left - calRect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
                tooltip.style.top = (rect.top - calRect.top - tooltip.offsetHeight - 8) + 'px';
            });
            cell.addEventListener('mouseleave', function() {
                tooltip.style.display = 'none';
            });
        });

        var title = document.getElementById('calendarMonthTitle');
        if (title) title.textContent = monthLabel;
    }

    function bindSearch() {
        var searchInput = document.getElementById('dashboardSearch');
        var suggestions = document.getElementById('searchSuggestions');
        if (!searchInput || !suggestions) return;

        searchInput.addEventListener('focus', function() { suggestions.classList.add('show'); });
        searchInput.addEventListener('blur', function() { setTimeout(function() { suggestions.classList.remove('show'); }, 150); });
        searchInput.addEventListener('input', function(event) {
            var value = event.target.value.trim().toLowerCase();
            var items = suggestions.querySelectorAll('button');
            items.forEach(function(item) {
                var text = item.textContent.toLowerCase();
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
