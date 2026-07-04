<?php
/**
 * TaskNest - Logout Handler
 */

require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin($auth);

$auth->logout();
redirect(SITE_URL . '/login.php?message=' . urlencode('You have been logged out successfully.'));
