<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin($auth);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/modules/expenses/expenses.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    require_once __DIR__ . '/modules/expenses/expenses.php';
    exit;
}

$page_title = 'Expenses';
$additional_css = ['expenses.css'];
$additional_js = ['expenses.js'];
include 'modules/expenses/index.php';
