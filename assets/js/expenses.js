(function () {
    function getCsrfToken() { return (typeof csrfToken !== 'undefined') ? csrfToken : ''; }
    function showToast(msg, type) {
        if (window.TaskNest && window.TaskNest.Toast) window.TaskNest.Toast.show(msg, type);
        else alert(msg);
    }

    window.deleteExpense = function (id) {
        var p = new FormData();
        p.append('action', 'delete_expense');
        p.append('expense_id', id);
        p.append('csrf_token', getCsrfToken());
        fetch(siteUrl + '/modules/expenses/expenses.php', { method: 'POST', body: p })
            .then(function (r) { return r.json(); })
            .then(function (r) {
                if (!r.success) { showToast(r.message || 'Failed.', 'error'); return; }
                showToast(r.message || 'Deleted.', 'success');
                window.location.reload();
            })
            .catch(function () { showToast('Network error.', 'error'); });
    };

    window.deleteBudget = function (id) {
        var p = new FormData();
        p.append('action', 'delete_budget');
        p.append('budget_id', id);
        p.append('csrf_token', getCsrfToken());
        fetch(siteUrl + '/modules/expenses/expenses.php', { method: 'POST', body: p })
            .then(function (r) { return r.json(); })
            .then(function (r) {
                if (!r.success) { showToast(r.message || 'Failed.', 'error'); return; }
                showToast(r.message || 'Deleted.', 'success');
                window.location.reload();
            })
            .catch(function () { showToast('Network error.', 'error'); });
    };

    // CSV Export
    window.exportCSV = function () {
        window.location.href = siteUrl + '/modules/expenses/expenses.php?action=export_csv';
    };

    // Tabs
    var chartInit = false;
    document.querySelectorAll('.expenses-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.expenses-tab').forEach(function (t) { t.classList.remove('active'); });
            document.querySelectorAll('.tab-content').forEach(function (c) { c.classList.remove('active'); });
            tab.classList.add('active');
            var target = document.getElementById('tab-' + tab.getAttribute('data-tab'));
            if (target) target.classList.add('active');
            if (tab.getAttribute('data-tab') === 'charts' && !chartInit) {
                chartInit = true;
                initCharts();
            }
        });
    });

    // Restore active tab from URL
    var urlParams = new URLSearchParams(window.location.search);
    var activeTab = urlParams.get('tab') || 'transactions';
    var validTabs = ['transactions', 'budgets', 'charts', 'categories'];
    if (validTabs.indexOf(activeTab) === -1) activeTab = 'transactions';
    var tabBtn = document.querySelector('.expenses-tab[data-tab="' + activeTab + '"]');
    if (tabBtn) tabBtn.click();

    function initCharts() {
        if (typeof Chart === 'undefined') return;

        var incomeCanvas = document.getElementById('incomeExpenseChart');
        if (incomeCanvas) {
            new Chart(incomeCanvas, {
                type: 'bar',
                data: {
                    labels: JSON.parse(incomeCanvas.dataset.labels || '[]'),
                    datasets: [
                        {
                            label: 'Income',
                            data: JSON.parse(incomeCanvas.dataset.income || '[]'),
                            backgroundColor: '#10b981'
                        },
                        {
                            label: 'Expenses',
                            data: JSON.parse(incomeCanvas.dataset.expenses || '[]'),
                            backgroundColor: '#ef4444'
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

        var pieCanvas = document.getElementById('categoryPieChart');
        if (pieCanvas) {
            var pieLabels = JSON.parse(pieCanvas.dataset.labels || '[]');
            var pieValues = JSON.parse(pieCanvas.dataset.values || '[]');
            if (pieLabels.length > 0) {
                var colors = ['#6366f1', '#818cf8', '#8b5cf6', '#a78bfa', '#10b981', '#f59e0b', '#ef4444', '#3b82f6'];
                new Chart(pieCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: pieLabels,
                        datasets: [{
                            data: pieValues,
                            backgroundColor: colors.slice(0, pieLabels.length)
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
                });
            }
        }
    }
})();
