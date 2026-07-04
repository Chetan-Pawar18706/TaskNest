<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin($auth);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/modules/borrow/borrow.php';
    exit;
}

$page_title = 'Borrow & Lend';
$additional_css = ['borrow.css'];
$additional_js = ['borrow.js'];
include 'modules/borrow/index.php';
