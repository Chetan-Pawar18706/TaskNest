<?php
/**
 * TaskNest - Sidebar Component
 */
if (!isset($user) || !is_array($user)) {
    $user = $auth->getUser() ?? [];
}
$current_page = basename($_SERVER['PHP_SELF']);
$is_admin = isset($user['role']) && strtolower($user['role']) === 'admin';
$navClass = function ($page) use ($current_page) {
    return $current_page === $page ? 'active' : '';
};
?>
<aside class="sidebar" id="mainSidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <a href="<?php echo SITE_URL; ?>/dashboard.php" class="logo">
            <img src="<?php echo SITE_URL; ?>/assets/images/logo-dark.png" alt="<?php echo SITE_NAME; ?> Logo" class="logo-img">
            <span class="logo-text"><?php echo SITE_NAME; ?></span>
        </a>
        <button class="sidebar-close" id="sidebarClose" aria-label="Close sidebar">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>
    
    <!-- Sidebar Menu -->
    <nav class="sidebar-nav">
        <div class="nav-section">
            <button class="nav-section-toggle" type="button" aria-expanded="true">
                <h3 class="nav-section-title">Main</h3>
            </button>
            <ul class="nav-menu">
                <li class="nav-item <?php echo $navClass('dashboard.php'); ?>">
                    <a href="<?php echo SITE_URL; ?>/dashboard.php" class="nav-link">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        <span class="nav-label">Dashboard</span>
                    </a>
                </li>
                
                <li class="nav-item <?php echo $navClass('tasks.php'); ?>">
                    <a href="<?php echo SITE_URL; ?>/tasks.php" class="nav-link">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 11 12 14 22 4"></polyline>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                        </svg>
                        <span class="nav-label">Tasks</span>
                    </a>
                </li>
                
                <li class="nav-item <?php echo $navClass('notes.php'); ?>">
                    <a href="<?php echo SITE_URL; ?>/notes.php" class="nav-link">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"></path>
                            <line x1="6" y1="8" x2="18" y2="8"></line>
                            <line x1="6" y1="12" x2="18" y2="12"></line>
                            <line x1="6" y1="16" x2="18" y2="16"></line>
                        </svg>
                        <span class="nav-label">Notes</span>
                    </a>
                </li>
                
                <li class="nav-item <?php echo $navClass('expenses.php'); ?>">
                    <a href="<?php echo SITE_URL; ?>/expenses.php" class="nav-link">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="1"></circle>
                            <path d="M12 1v6m0 6v6"></path>
                            <path d="M4.22 4.22l4.24 4.24m2.98 2.98l4.24 4.24"></path>
                            <path d="M1 12h6m6 0h6"></path>
                            <path d="M4.22 19.78l4.24-4.24m2.98-2.98l4.24-4.24"></path>
                            <path d="M19.78 19.78l-4.24-4.24m-2.98-2.98l-4.24-4.24"></path>
                            <path d="M19.78 4.22l-4.24 4.24m-2.98 2.98l-4.24 4.24"></path>
                        </svg>
                        <span class="nav-label">Expenses</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="nav-section">
            <button class="nav-section-toggle" type="button" aria-expanded="true">
                <h3 class="nav-section-title">Organize</h3>
            </button>
            <ul class="nav-menu">
                <li class="nav-item <?php echo $navClass('documents.php'); ?>">
                    <a href="<?php echo SITE_URL; ?>/documents.php" class="nav-link">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                            <polyline points="13 2 13 9 20 9"></polyline>
                        </svg>
                        <span class="nav-label">Documents</span>
                    </a>
                </li>
                
                <li class="nav-item <?php echo $navClass('habits.php'); ?>">
                    <a href="<?php echo SITE_URL; ?>/habits.php" class="nav-link">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="1"></circle>
                            <circle cx="12" cy="5" r="1"></circle>
                            <circle cx="12" cy="19" r="1"></circle>
                            <circle cx="19" cy="12" r="1"></circle>
                            <circle cx="5" cy="12" r="1"></circle>
                            <circle cx="17.66" cy="6.34" r="1"></circle>
                            <circle cx="6.34" cy="17.66" r="1"></circle>
                            <circle cx="17.66" cy="17.66" r="1"></circle>
                            <circle cx="6.34" cy="6.34" r="1"></circle>
                        </svg>
                        <span class="nav-label">Habits</span>
                    </a>
                </li>
                
                <li class="nav-item <?php echo $navClass('goals.php'); ?>">
                    <a href="<?php echo SITE_URL; ?>/goals.php" class="nav-link">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="8 12 12 16 16 12"></polyline>
                            <line x1="12" y1="8" x2="12" y2="16"></line>
                        </svg>
                        <span class="nav-label">Goals</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="nav-section">
            <button class="nav-section-toggle" type="button" aria-expanded="true">
                <h3 class="nav-section-title">Track</h3>
            </button>
            <ul class="nav-menu">
                <li class="nav-item <?php echo $navClass('shopping.php'); ?>">
                    <a href="<?php echo SITE_URL; ?>/shopping.php" class="nav-link">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <span class="nav-label">Shopping</span>
                    </a>
                </li>
                
                <li class="nav-item <?php echo $navClass('borrow.php'); ?>">
                    <a href="<?php echo SITE_URL; ?>/borrow.php" class="nav-link">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span class="nav-label">Borrow / Lend</span>
                    </a>
                </li>
            </ul>
        </div>
        <?php if ($is_admin): ?>
        <div class="nav-section">
            <button class="nav-section-toggle" type="button" aria-expanded="true">
                <h3 class="nav-section-title">Admin</h3>
            </button>
            <ul class="nav-menu">
                <li class="nav-item <?php echo $navClass('admin-root.php'); ?>">
                    <a href="<?php echo SITE_URL; ?>/modules/admin/admin-root.php" class="nav-link">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2 3 6v6c0 5 3.5 8.5 9 10 5.5-1.5 9-5 9-10V6l-9-4Z"></path>
                            <path d="M9 12l2 2 4-4"></path>
                        </svg>
                        <span class="nav-label">Administration</span>
                    </a>
                </li>
            </ul>
        </div>
        <?php endif; ?>
    </nav>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="sidebar-profile">
            <?php if (!empty($user['avatar_url'])): ?>
                <img src="<?php echo SITE_URL; ?>/<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="Profile" class="sidebar-profile-avatar">
            <?php else: ?>
                <img src="<?php echo SITE_URL; ?>/assets/images/default-avatar.svg" alt="Profile" class="sidebar-profile-avatar">
            <?php endif; ?>
            <div>
                <strong><?php echo htmlspecialchars($user['first_name'] ?? $user['username']); ?></strong>
                <p>Premium workspace</p>
            </div>
        </div>
        <div class="sidebar-footer-links">
            <a href="<?php echo SITE_URL; ?>/settings.php" class="sidebar-footer-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                <span>Settings</span>
            </a>
            <a href="<?php echo SITE_URL; ?>/2fa-setup.php" class="sidebar-footer-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                <span>Security</span>
            </a>
        </div>
        <a href="<?php echo SITE_URL; ?>/logout.php" class="btn btn-secondary btn-block">Logout</a>
        <div class="version-info">
            <small>TaskNest v1.0 Phase 10</small>
        </div>
    </div>
</aside>
