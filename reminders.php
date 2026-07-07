<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (!$auth->isLoggedIn()) {
    redirect(SITE_URL . '/auth/login.php');
}

ensureReminderTableExists($mysqli);

// Process pending reminder emails on page load
processPendingReminderEmails($mysqli);

redirect(SITE_URL . '/modules/reminders/index.php');
