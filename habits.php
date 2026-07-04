<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin($auth);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/modules/habits/habits.php';
    exit;
}

$page_title = 'Habits';
$additional_css = ['habits.css'];
$additional_js = ['habits.js'];
include 'modules/habits/index.php';
