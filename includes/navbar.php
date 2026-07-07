<?php
/**
 * TaskNest - Navbar Component
 */
$user = $auth->getUser();
$avatar_url = $user['avatar_url'] ?? '';
if (!empty($avatar_url) && strpos($avatar_url, 'http') !== 0) {
    $avatar_url = SITE_URL . '/' . ltrim($avatar_url, '/');
}
if (empty($avatar_url) || $avatar_url === SITE_URL . '/') {
    $avatar_url = SITE_URL . '/assets/images/default-avatar.svg';
}
?>
<nav class="top-navbar">
    <div class="navbar-left">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
        <h1 class="page-title"><?php echo htmlspecialchars($page_title ?? 'Dashboard', ENT_QUOTES, 'UTF-8'); ?></h1>
    </div>
    
    <div class="navbar-right">
        <!-- Search -->
        <div class="search-box">
            <input type="text" class="search-input" id="dashboardSearch" placeholder="Search..." aria-label="Search">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
            <div class="search-suggestions" id="searchSuggestions">
                <button type="button">Tasks</button>
                <button type="button">Notes</button>
                <button type="button">Expenses</button>
                <button type="button">Goals</button>
            </div>
        </div>
        
        <!-- Theme Toggle -->
        <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme">
            <svg class="icon sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="5"></circle>
                <line x1="12" y1="1" x2="12" y2="3"></line>
                <line x1="12" y1="21" x2="12" y2="23"></line>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                <line x1="1" y1="12" x2="3" y2="12"></line>
                <line x1="21" y1="12" x2="23" y2="12"></line>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
            </svg>
            <svg class="icon moon-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
            </svg>
        </button>
        
        <!-- Notifications -->
        <div class="navbar-notifications-wrapper">
            <button class="navbar-icon-btn" id="notificationsBtn" aria-label="Notifications">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <span class="notification-badge" id="bellBadge" style="display:none;">0</span>
            </button>
        </div>
        
        <!-- User Menu -->
        <div class="user-menu-wrapper">
            <button class="user-menu-btn" id="userMenuBtn" aria-label="User menu">
                <img src="<?php echo htmlspecialchars($avatar_url); ?>" alt="User avatar" class="user-avatar">
                <span class="user-name"><?php echo htmlspecialchars($user['first_name'] ?? $user['username']); ?></span>
                <svg class="icon dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>
            
            <!-- User Dropdown Menu -->
            <div class="dropdown-menu" id="userMenu">
                <div class="dropdown-header">
                    <img src="<?php echo htmlspecialchars($avatar_url); ?>" alt="User avatar" class="user-avatar-lg">
                    <div>
                        <p class="user-full-name"><?php echo htmlspecialchars(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')); ?></p>
                        <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                </div>
                
                <div class="dropdown-divider"></div>
                
                <a href="<?php echo SITE_URL; ?>/profile.php" class="dropdown-item">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span>My Profile</span>
                </a>
                
                <a href="<?php echo SITE_URL; ?>/settings.php" class="dropdown-item">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 1v6m0 6v6M4.22 4.22l4.24 4.24m2.98 2.98l4.24 4.24M1 12h6m6 0h6M4.22 19.78l4.24-4.24m2.98-2.98l4.24-4.24M19.78 19.78l-4.24-4.24m-2.98-2.98l-4.24-4.24"></path>
                    </svg>
                    <span>Settings</span>
                </a>
                
                <div class="dropdown-divider"></div>
                
                <a href="<?php echo SITE_URL; ?>/auth/logout.php" class="dropdown-item text-danger">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
</nav>
