<?php
/**
 * TaskNest - Logout Handler
 */

require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

requireLogin($auth);

$auth->logout();
redirect(SITE_URL . '/auth/login.php?message=' . urlencode('You have been logged out successfully.'));
