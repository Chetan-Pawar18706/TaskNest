<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin($auth);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/modules/shopping/shopping.php';
    exit;
}

$page_title = 'Shopping';
$additional_css = ['shopping.css'];
$additional_js = ['shopping.js'];
include 'modules/shopping/index.php';
