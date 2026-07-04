<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin($auth);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/modules/documents/documents.php';
    exit;
}

$page_title = 'Documents';
$additional_css = ['documents.css'];
$additional_js = ['documents.js'];
include 'modules/documents/index.php';
