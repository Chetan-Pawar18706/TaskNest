<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireLogin($auth);

$user = $auth->getUser();
if (!isset($user['role']) || strtolower($user['role']) !== 'admin') {
    header('Location: ' . SITE_URL . '/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/admin.php';
    exit;
}

$page_title = 'Administration';
$additional_css = ['admin.css'];
$additional_js = ['admin.js'];
include __DIR__ . '/index.php';
