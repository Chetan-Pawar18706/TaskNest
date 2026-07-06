// Password Manager JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Lock vault button
    var lockBtn = document.getElementById('lockVaultBtn');
    if (lockBtn) {
        lockBtn.addEventListener('click', function() {
            var formData = new FormData();
            formData.append('action', 'lock_vault');
            formData.append('csrf_token', csrfToken);

            fetch(siteUrl + '/modules/passwords/passwords.php', {
                method: 'POST',
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                window.location.href = siteUrl + '/passwords.php';
            });
        });
    }

    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var targetId = this.getAttribute('data-target');
            var input = document.getElementById(targetId);
            if (input.type === 'password') {
                input.type = 'text';
                this.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>';
            } else {
                input.type = 'password';
                this.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>';
            }
        });
    });

    // Password generator toggle
    var genBtn = document.getElementById('generatePasswordBtn');
    if (genBtn) {
        genBtn.addEventListener('click', function() {
            var gen = document.getElementById('passwordGenerator');
            if (gen.style.display === 'none') {
                gen.style.display = 'block';
                generatePassword();
            } else {
                gen.style.display = 'none';
            }
        });
    }

    // Password length slider
    var lengthSlider = document.getElementById('passwordLength');
    if (lengthSlider) {
        lengthSlider.addEventListener('input', function() {
            document.getElementById('lengthValue').textContent = this.value;
            generatePassword();
        });
    }

    // Checkbox changes
    ['genUppercase', 'genLowercase', 'genNumbers', 'genSymbols'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('change', generatePassword);
    });

    // Regenerate
    var regenBtn = document.getElementById('regenerateBtn');
    if (regenBtn) {
        regenBtn.addEventListener('click', generatePassword);
    }

    // Use generated password
    var useBtn = document.getElementById('usePasswordBtn');
    if (useBtn) {
        useBtn.addEventListener('click', function() {
            var preview = document.getElementById('generatorPreview');
            var passwordInput = document.getElementById('password');
            if (preview && passwordInput) {
                passwordInput.value = preview.textContent;
                passwordInput.type = 'text';
                document.getElementById('passwordGenerator').style.display = 'none';
            }
        });
    }

    // Favorites filter
    var favFilter = document.getElementById('favoritesFilter');
    if (favFilter) {
        favFilter.addEventListener('click', function() {
            this.classList.toggle('active');
            loadPasswords();
        });
    }

    // Search
    var searchInput = document.getElementById('passwordSearch');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(loadPasswords, 300));
    }

    // Category filter
    var catFilter = document.getElementById('categoryFilter');
    if (catFilter) {
        catFilter.addEventListener('change', loadPasswords);
    }

    // Select all checkbox
    var selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            var checked = this.checked;
            document.querySelectorAll('.password-select').forEach(function(cb) {
                cb.checked = checked;
                var card = cb.closest('.password-card');
                if (card) card.classList.toggle('selected', checked);
            });
            updateBulkActions();
        });
    }

    // Bulk delete
    var bulkDelete = document.getElementById('bulkDeleteBtn');
    if (bulkDelete) {
        bulkDelete.addEventListener('click', function() {
            var selected = [];
            document.querySelectorAll('.password-select:checked').forEach(function(cb) {
                selected.push(cb.value);
            });
            if (selected.length === 0) return;
            if (!confirm('Delete ' + selected.length + ' selected passwords?')) return;

            var formData = new FormData();
            formData.append('action', 'bulk_action');
            formData.append('type', 'delete');
            formData.append('ids', JSON.stringify(selected));
            formData.append('csrf_token', csrfToken);

            fetch(siteUrl + '/modules/passwords/passwords.php', {
                method: 'POST',
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    loadPasswords();
                } else {
                    alert(data.message || 'Error deleting passwords');
                }
            });
        });
    }

    // Load passwords on list page
    if (document.getElementById('passwordsGrid')) {
        loadPasswords();
    }
});

function generatePassword() {
    var length = parseInt(document.getElementById('passwordLength').value);
    var formData = new FormData();
    formData.append('action', 'generate_password');
    formData.append('length', length);
    formData.append('uppercase', document.getElementById('genUppercase').checked ? '1' : '');
    formData.append('lowercase', document.getElementById('genLowercase').checked ? '1' : '');
    formData.append('numbers', document.getElementById('genNumbers').checked ? '1' : '');
    formData.append('symbols', document.getElementById('genSymbols').checked ? '1' : '');

    fetch(siteUrl + '/modules/passwords/passwords.php', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            document.getElementById('generatorPreview').textContent = data.password;
        }
    });
}

function loadPasswords() {
    var grid = document.getElementById('passwordsGrid');
    var emptyState = document.getElementById('passwordsEmpty');
    if (!grid) return;

    var search = document.getElementById('passwordSearch');
    var category = document.getElementById('categoryFilter');
    var favorites = document.getElementById('favoritesFilter');

    var formData = new FormData();
    formData.append('action', 'get_passwords');
    formData.append('search', search ? search.value : '');
    formData.append('category_id', category ? category.value : '');
    formData.append('favorites_only', favorites && favorites.classList.contains('active') ? '1' : '');

    fetch(siteUrl + '/modules/passwords/passwords.php', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) {
            grid.innerHTML = '<div class="passwords-loading">' + (data.message || 'Error loading passwords') + '</div>';
            return;
        }

        var passwords = data.passwords || [];

        if (passwords.length === 0) {
            grid.innerHTML = '';
            if (emptyState) emptyState.style.display = 'block';
            var totalEl = document.getElementById('totalCount');
            var favEl = document.getElementById('favoritesCount');
            if (totalEl) totalEl.textContent = '0';
            if (favEl) favEl.textContent = '0';
            return;
        }

        if (emptyState) emptyState.style.display = 'none';

        var html = '';
        passwords.forEach(function(p) {
            var initial = p.title.charAt(0).toUpperCase();
            var categoryBadge = p.category_name ? '<span class="password-category-badge">' + escapeHtml(p.category_name) + '</span>' : '';
            var usernameMeta = p.username ? '<span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg> ' + escapeHtml(p.username) + '</span>' : '';
            var urlMeta = p.url ? '<span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg></span>' : '';

            html += '<div class="password-card ' + (p.is_favorite == 1 ? 'favorited' : '') + '" data-id="' + p.id + '">';
            html += '<input type="checkbox" class="password-select" value="' + p.id + '" onchange="updateBulkActions()">';
            html += '<div class="password-favicon">' + initial + '</div>';
            html += '<div class="password-info">';
            html += '<div class="password-info-title">' + escapeHtml(p.title) + '</div>';
            html += '<div class="password-info-meta">' + usernameMeta + urlMeta + categoryBadge + '</div>';
            html += '</div>';
            html += '<div class="password-actions">';
            html += '<button class="password-fav-btn" onclick="toggleFavorite(' + p.id + ', this)" title="Toggle Favorite"><svg width="16" height="16" viewBox="0 0 24 24" fill="' + (p.is_favorite == 1 ? 'currentColor' : 'none') + '" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg></button>';
            html += '<a href="' + siteUrl + '/modules/passwords/passwords-edit.php?id=' + p.id + '" class="btn btn-icon" title="Edit"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></a>';
            html += '<button class="btn btn-icon" onclick="deletePassword(' + p.id + ')" title="Delete"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button>';
            html += '</div>';
            html += '</div>';
        });

        grid.innerHTML = html;

        var totalEl = document.getElementById('totalCount');
        var favEl = document.getElementById('favoritesCount');
        if (totalEl) totalEl.textContent = passwords.length;
        if (favEl) {
            var favCount = passwords.filter(function(p) { return p.is_favorite == 1; }).length;
            favEl.textContent = favCount;
        }
    })
    .catch(function(err) {
        grid.innerHTML = '<div class="passwords-loading">Error loading passwords</div>';
    });
}

function toggleFavorite(id, btn) {
    var formData = new FormData();
    formData.append('action', 'toggle_favorite');
    formData.append('password_id', id);
    formData.append('csrf_token', csrfToken);

    fetch(siteUrl + '/modules/passwords/passwords.php', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            loadPasswords();
        }
    });
}

function deletePassword(id) {
    if (!confirm('Delete this password?')) return;

    var formData = new FormData();
    formData.append('action', 'delete_password');
    formData.append('password_id', id);
    formData.append('csrf_token', csrfToken);

    fetch(siteUrl + '/modules/passwords/passwords.php', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            loadPasswords();
        } else {
            alert(data.message || 'Error deleting password');
        }
    });
}

function updateBulkActions() {
    var selected = document.querySelectorAll('.password-select:checked');
    var bulkBar = document.getElementById('bulkActions');
    var countEl = document.getElementById('selectedCount');
    if (bulkBar && countEl) {
        if (selected.length > 0) {
            bulkBar.style.display = 'flex';
            countEl.textContent = selected.length + ' selected';
        } else {
            bulkBar.style.display = 'none';
        }
    }
    // Update card selected state
    document.querySelectorAll('.password-card').forEach(function(card) {
        var cb = card.querySelector('.password-select');
        card.classList.toggle('selected', cb && cb.checked);
    });
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
        timeout = setTimeout(function() {
            func.apply(context, args);
        }, wait);
    };
}
