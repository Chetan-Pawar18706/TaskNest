<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin($auth);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/modules/goals/goals.php';
    exit;
}

$page_title = 'Goals';
$additional_css = ['goals.css'];
$additional_js = ['goals.js'];
include 'modules/goals/index.php';
