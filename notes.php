<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin($auth);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/modules/notes/notes.php';
    exit;
}

$page_title = 'Notes';
$additional_css = ['notes.css'];
$additional_js = ['notes.js'];
include 'modules/notes/index.php';
