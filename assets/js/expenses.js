(function () {
    var expenseModal = document.getElementById('expenseModal');
    var expenseCategoryModal = document.getElementById('expenseCategoryModal');
    var budgetModal = document.getElementById('budgetModal');
    var expenseConfirmModal = document.getElementById('expenseConfirmModal');
    var expenseForm = document.getElementById('expenseForm');
    var expenseCategoryForm = document.getElementById('expenseCategoryForm');
    var budgetForm = document.getElementById('budgetForm');
    var expenseModalTitle = document.getElementById('expenseModalTitle');
    var expenseTypeInput = document.getElementById('expenseType');
    var expenseCategorySelect = document.getElementById('expenseCategory');
    var expCatList = document.getElementById('expCatList');
    var expConfirmActionBtn = document.getElementById('expConfirmActionBtn');
    var expConfirmTitle = document.getElementById('expConfirmTitle');
    var expConfirmBody = document.getElementById('expConfirmBody');
    var openExpenseModalBtn = document.getElementById('openExpenseModal');
    var openExpenseCategoryModalBtn = document.getElementById('openExpenseCategoryModal');
    var openBudgetModalBtn = document.getElementById('openBudgetModal');
    var emptyStateAddExpense = document.getElementById('emptyStateAddExpense');
    var typeToggle = document.getElementById('typeToggle');
    var budgetsContainer = document.getElementById('budgetsContainer');

    var pendingAction = null;

    function getCsrfToken() {
        var t = document.querySelector('#expenseForm input[name="csrf_token"]');
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

    // Tabs
    document.querySelectorAll('.expenses-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.expenses-tab').forEach(function (t) { t.classList.remove('active'); });
            document.querySelectorAll('.tab-content').forEach(function (c) { c.classList.remove('active'); });
            tab.classList.add('active');
            var tabEl = document.getElementById('tab-' + tab.getAttribute('data-tab'));
            if (tabEl) tabEl.classList.add('active');
            if (tab.getAttribute('data-tab') === 'charts') loadCharts();
            if (tab.getAttribute('data-tab') === 'budgets') loadBudgets();
        });
    });

    // Type toggle
    typeToggle && typeToggle.addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-type]');
        if (!btn) return;
        typeToggle.querySelectorAll('button').forEach(function (b) { b.classList.remove('active', 'income-active'); });
        btn.classList.add('active');
        if (btn.getAttribute('data-type') === 'income') btn.classList.add('income-active');
        expenseTypeInput.value = btn.getAttribute('data-type');
        loadExpenseCategories(btn.getAttribute('data-type'));
    });

    function loadExpenseCategories(type) {
        var payload = new FormData();
        payload.append('action', 'get_categories');
        payload.append('type', type || '');
        payload.append('csrf_token', getCsrfToken());
        fetch('expenses.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (!result.success) return;
                expenseCategorySelect.innerHTML = '<option value="">No Category</option>';
                (result.categories || []).forEach(function (cat) {
                    var opt = document.createElement('option');
                    opt.value = cat.id;
                    opt.textContent = cat.name;
                    expenseCategorySelect.appendChild(opt);
                });
                renderExpCategories(result.categories || []);
            });
    }

    function renderExpCategories(cats) {
        if (!expCatList) return;
        expCatList.innerHTML = '';
        cats.forEach(function (cat) {
            var item = document.createElement('div');
            item.className = 'expense-category-item';
            item.innerHTML = '<span style="display:flex;align-items:center;gap:0.5rem;"><span style="width:12px;height:12px;border-radius:50%;background:' + (cat.color || '#6366f1') + ';display:inline-block;"></span>' + cat.name + ' <small style="color:var(--text-muted);">(' + cat.type + ')</small></span>' +
                '<div><button class="link-btn" type="button" data-edit-exp-cat="' + cat.id + '">Edit</button> <button class="link-btn" type="button" data-delete-exp-cat="' + cat.id + '">Delete</button></div>';
            expCatList.appendChild(item);
        });
    }

    function resetExpenseForm() {
        expenseForm.reset();
        document.getElementById('expenseId').value = '';
        expenseModalTitle.textContent = 'Add Transaction';
        expenseTypeInput.value = 'expense';
        typeToggle.querySelectorAll('button').forEach(function (b) { b.classList.remove('active', 'income-active'); });
        typeToggle.querySelector('[data-type="expense"]').classList.add('active');
        loadExpenseCategories('expense');
    }

    function fillExpenseForm(exp) {
        resetExpenseForm();
        document.getElementById('expenseId').value = exp.id;
        document.getElementById('expenseTitle').value = exp.title || '';
        document.getElementById('expenseAmount').value = exp.amount || '';
        document.getElementById('expenseDate').value = exp.transaction_date || '';
        document.getElementById('expenseNotes').value = exp.notes || '';
        expenseTypeInput.value = exp.type || 'expense';
        typeToggle.querySelectorAll('button').forEach(function (b) {
            b.classList.remove('active', 'income-active');
            if (b.getAttribute('data-type') === exp.type) {
                b.classList.add('active');
                if (exp.type === 'income') b.classList.add('income-active');
            }
        });
        loadExpenseCategories(exp.type);
        setTimeout(function () { expenseCategorySelect.value = exp.category_id || ''; }, 200);
        expenseModalTitle.textContent = 'Edit Transaction';
        openModal(expenseModal);
    }

    function fetchExpense(id) {
        var payload = new FormData();
        payload.append('action', 'get_expense');
        payload.append('expense_id', id);
        payload.append('csrf_token', getCsrfToken());
        fetch('expenses.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (!result.success) { showToast(result.message || 'Not found.', 'error'); return; }
                fillExpenseForm(result.expense);
            });
    }

    function submitExpenseForm(e) {
        e.preventDefault();
        var payload = new FormData(expenseForm);
        payload.set('action', 'save_expense');
        payload.set('csrf_token', getCsrfToken());
        fetch('expenses.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (!result.success) { showToast(result.message || 'Save failed.', 'error'); return; }
                closeModal(expenseModal);
                showToast(result.message || 'Saved.', 'success');
                window.location.reload();
            });
    }

    function submitExpCatForm(e) {
        e.preventDefault();
        var payload = new FormData(expenseCategoryForm);
        payload.set('action', 'save_category');
        payload.set('csrf_token', getCsrfToken());
        fetch('expenses.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (!result.success) { showToast(result.message || 'Save failed.', 'error'); return; }
                expenseCategoryForm.reset();
                document.getElementById('expCatId').value = '';
                showToast(result.message || 'Saved.', 'success');
                loadExpenseCategories();
            });
    }

    function confirmExpAction(title, body, action, id) {
        pendingAction = { action: action, id: id };
        expConfirmTitle.textContent = title;
        expConfirmBody.textContent = body;
        openModal(expenseConfirmModal);
    }

    function performPendingExpAction() {
        if (!pendingAction) return;
        var payload = new FormData();
        payload.append('action', pendingAction.action);
        payload.append('expense_id', pendingAction.id);
        payload.append('csrf_token', getCsrfToken());
        fetch('expenses.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                closeModal(expenseConfirmModal);
                if (!result.success) { showToast(result.message || 'Failed.', 'error'); return; }
                showToast(result.message || 'Done.', 'success');
                window.location.reload();
            });
    }

    // Transaction actions
    var txnList = document.getElementById('transactionList');
    if (txnList) txnList.addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-action]');
        if (!btn) return;
        var action = btn.getAttribute('data-action');
        var id = btn.getAttribute('data-id');
        if (action === 'edit') fetchExpense(id);
        else if (action === 'delete') confirmExpAction('Delete Transaction', 'Delete this transaction?', 'delete_expense', id);
    });

    // Category actions
    expCatList && expCatList.addEventListener('click', function (e) {
        var editBtn = e.target.closest('[data-edit-exp-cat]');
        var delBtn = e.target.closest('[data-delete-exp-cat]');
        if (editBtn) {
            var catId = editBtn.getAttribute('data-edit-exp-cat');
            var payload = new FormData();
            payload.append('action', 'get_categories');
            payload.append('csrf_token', getCsrfToken());
            fetch('expenses.php', { method: 'POST', body: payload })
                .then(function (r) { return r.json(); })
                .then(function (result) {
                    var cat = (result.categories || []).find(function (c) { return String(c.id) === String(catId); });
                    if (cat) {
                        document.getElementById('expCatId').value = cat.id;
                        document.getElementById('expCatName').value = cat.name;
                        document.getElementById('expCatColor').value = cat.color || '#6366f1';
                        document.getElementById('expCatType').value = cat.type || 'expense';
                    }
                });
            return;
        }
        if (delBtn) {
            confirmExpAction('Delete Category', 'Delete this category?', 'delete_category', delBtn.getAttribute('data-delete-exp-cat'));
        }
    });

    function submitBudgetForm(e) {
        e.preventDefault();
        var payload = new FormData(budgetForm);
        payload.set('action', 'save_budget');
        payload.set('csrf_token', getCsrfToken());
        fetch('expenses.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (!result.success) { showToast(result.message || 'Save failed.', 'error'); return; }
                closeModal(budgetModal);
                showToast(result.message || 'Saved.', 'success');
                loadBudgets();
            });
    }

    function loadBudgets() {
        var payload = new FormData();
        payload.append('action', 'get_budgets');
        payload.append('csrf_token', getCsrfToken());
        fetch('expenses.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (!result.success) return;
                budgetsContainer.innerHTML = '';
                if (!result.budgets || !result.budgets.length) {
                    budgetsContainer.innerHTML = '<div class="empty-state"><p>No budgets set yet.</p></div>';
                    return;
                }
                result.budgets.forEach(function (b) {
                    var pct = b.percentage;
                    var barClass = pct < 70 ? 'safe' : (pct < 90 ? 'warning' : 'danger');
                    var card = document.createElement('div');
                    card.className = 'budget-card';
                    card.innerHTML = '<h4>' + (b.category_name || 'Total Budget') + '</h4>' +
                        '<div class="budget-progress"><div class="budget-progress-bar ' + barClass + '" style="width:' + pct + '%"></div></div>' +
                        '<div class="budget-info"><span>$' + parseFloat(b.spent).toFixed(2) + ' spent of $' + parseFloat(b.amount).toFixed(2) + '</span><span>' + pct + '%</span></div>' +
                        '<div style="margin-top:0.75rem;display:flex;gap:0.5rem;">' +
                        '<button class="btn btn-secondary btn-sm" type="button" data-action="edit_budget" data-id="' + b.id + '">Edit</button>' +
                        '<button class="btn btn-danger btn-sm" type="button" data-action="delete_budget" data-id="' + b.id + '">Delete</button></div>';
                    budgetsContainer.appendChild(card);
                });
            });
    }

    function fetchBudget(id) {
        var payload = new FormData();
        payload.append('action', 'get_budget');
        payload.append('budget_id', id);
        payload.append('csrf_token', getCsrfToken());
        fetch('expenses.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (!result.success) { showToast(result.message || 'Not found.', 'error'); return; }
                fillBudgetForm(result.budget);
            });
    }

    function fillBudgetForm(b) {
        document.getElementById('budgetId').value = b.id;
        document.getElementById('budgetAmount').value = b.amount || '';
        document.getElementById('budgetPeriod').value = b.period || 'monthly';
        document.getElementById('budgetStartDate').value = b.start_date || '';
        document.getElementById('budgetEndDate').value = b.end_date || '';
        document.getElementById('budgetModalTitle').textContent = 'Edit Budget';
        var payload = new FormData();
        payload.append('action', 'get_categories');
        payload.append('type', 'expense');
        payload.append('csrf_token', getCsrfToken());
        fetch('expenses.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                var sel = document.getElementById('budgetCategory');
                sel.innerHTML = '<option value="">Total Budget</option>';
                (result.categories || []).forEach(function (c) {
                    var opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.name;
                    sel.appendChild(opt);
                });
                sel.value = b.category_id || '';
            });
        openModal(budgetModal);
    }

    budgetsContainer && budgetsContainer.addEventListener('click', function (e) {
        var btn = e.target.closest('button[data-action]');
        if (!btn) return;
        var action = btn.getAttribute('data-action');
        var id = btn.getAttribute('data-id');
        if (action === 'edit_budget') {
            fetchBudget(id);
        } else if (action === 'delete_budget') {
            confirmExpAction('Delete Budget', 'Remove this budget?', 'delete_budget', id);
        }
    });

    var incomeExpenseChart = null;
    var categoryPieChart = null;

    function loadCharts() {
        var payload = new FormData();
        payload.append('action', 'chart_data');
        payload.append('months', '6');
        payload.append('csrf_token', getCsrfToken());
        fetch('expenses.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (!result.success) return;
                var data = result.chart;
                var ctx = document.getElementById('incomeExpenseChart');
                if (!ctx) return;
                if (incomeExpenseChart) incomeExpenseChart.destroy();
                if (typeof Chart === 'undefined') return;
                incomeExpenseChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [
                            { label: 'Income', data: data.income, backgroundColor: '#22c55e' },
                            { label: 'Expenses', data: data.expense, backgroundColor: '#ef4444' }
                        ]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
                });
            });

        var payload2 = new FormData();
        payload2.append('action', 'category_breakdown');
        payload2.append('type', 'expense');
        payload2.append('month', new Date().getMonth() + 1);
        payload2.append('year', new Date().getFullYear());
        payload2.append('csrf_token', getCsrfToken());
        fetch('expenses.php', { method: 'POST', body: payload2 })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                if (!result.success) return;
                var cats = result.categories;
                var ctx2 = document.getElementById('categoryPieChart');
                if (!ctx2) return;
                if (categoryPieChart) categoryPieChart.destroy();
                var labels = cats.map(function (c) { return c.name || 'Uncategorized'; });
                var values = cats.map(function (c) { return parseFloat(c.total); });
                var colors = cats.map(function (c) { return c.color || '#6366f1'; });
                if (typeof Chart === 'undefined') return;
                categoryPieChart = new Chart(ctx2, {
                    type: 'doughnut',
                    data: { labels: labels, datasets: [{ data: values, backgroundColor: colors }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
                });
            });
    }

    openExpenseModalBtn && openExpenseModalBtn.addEventListener('click', function () { resetExpenseForm(); openModal(expenseModal); });
    openExpenseCategoryModalBtn && openExpenseCategoryModalBtn.addEventListener('click', function () { loadExpenseCategories(); openModal(expenseCategoryModal); });
    openBudgetModalBtn && openBudgetModalBtn.addEventListener('click', function () {
        document.getElementById('budgetId').value = '';
        document.getElementById('budgetModalTitle').textContent = 'Add Budget';
        var payload = new FormData();
        payload.append('action', 'get_categories');
        payload.append('type', 'expense');
        payload.append('csrf_token', getCsrfToken());
        fetch('expenses.php', { method: 'POST', body: payload })
            .then(function (r) { return r.json(); })
            .then(function (result) {
                var sel = document.getElementById('budgetCategory');
                sel.innerHTML = '<option value="">Total Budget</option>';
                (result.categories || []).forEach(function (c) {
                    var opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.name;
                    sel.appendChild(opt);
                });
            });
        openModal(budgetModal);
    });
    emptyStateAddExpense && emptyStateAddExpense.addEventListener('click', function () { resetExpenseForm(); openModal(expenseModal); });

    expenseForm && expenseForm.addEventListener('submit', submitExpenseForm);
    expenseCategoryForm && expenseCategoryForm.addEventListener('submit', submitExpCatForm);
    budgetForm && budgetForm.addEventListener('submit', submitBudgetForm);
    expConfirmActionBtn && expConfirmActionBtn.addEventListener('click', performPendingExpAction);

    loadExpenseCategories('expense');

    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeModal(expenseModal);
            closeModal(expenseCategoryModal);
            closeModal(budgetModal);
            closeModal(expenseConfirmModal);
        }
    });
})();
