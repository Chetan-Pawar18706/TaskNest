(function() {
    'use strict';

    function initAdmin() {
        if (typeof Chart === 'undefined') return;
        renderUserRegistrationsChart();
        renderModuleUsageChart();
        bindTabs();
    }

    function renderUserRegistrationsChart() {
        var canvas = document.getElementById('adminUserChart');
        if (!canvas) return;
        var labels = JSON.parse(canvas.dataset.labels || '[]');
        var counts = JSON.parse(canvas.dataset.counts || '[]');
        if (labels.length === 0) {
            labels = ['No data'];
            counts = [0];
        }
        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Registrations',
                    data: counts,
                    backgroundColor: 'rgba(99, 102, 241, 0.7)',
                    borderColor: '#6366f1',
                    borderWidth: 1,
                    borderRadius: 4
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

    function renderModuleUsageChart() {
        var canvas = document.getElementById('adminModuleChart');
        if (!canvas) return;
        var labels = JSON.parse(canvas.dataset.labels || '[]');
        var counts = JSON.parse(canvas.dataset.counts || '[]');
        if (labels.length === 0) {
            labels = ['No data'];
            counts = [0];
        }
        var colors = [
            '#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
            '#ec4899', '#14b8a6', '#f97316', '#06b6d4', '#84cc16'
        ];
        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: counts,
                    backgroundColor: colors.slice(0, labels.length)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { padding: 12 } } }
            }
        });
    }

    function bindTabs() {
        document.querySelectorAll('.admin-tab').forEach(function(tab) {
            tab.addEventListener('click', function() {
                var target = tab.getAttribute('data-tab');
                document.querySelectorAll('.admin-tab').forEach(function(t) { t.classList.remove('active'); });
                tab.classList.add('active');
                document.querySelectorAll('.admin-tab-content').forEach(function(c) { c.classList.remove('active'); });
                var panel = document.getElementById('tab-' + target);
                if (panel) panel.classList.add('active');
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAdmin);
    } else {
        initAdmin();
    }
})();
