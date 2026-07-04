<?php
require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';
requireLogin($auth);

$user = $auth->getUser();
if (!isset($user['role']) || strtolower($user['role']) !== 'admin') {
    header('Location: ' . SITE_URL . '/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    $admin_id = $auth->getUserId();

    $csrf = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCsrfToken($csrf)) { echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']); exit; }

    switch ($action) {
        case 'toggle_user': echo json_encode(toggleUserStatusHandler($mysqli, $_POST)); break;
        case 'deactivate_user': echo json_encode(deleteUserHandler($mysqli, $_POST)); break;
        case 'reply_feedback': echo json_encode(replyFeedbackHandler($mysqli, $_POST)); break;
        case 'save_settings': echo json_encode(saveSiteSettingsHandler($mysqli, $_POST)); break;
        default: echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
    exit;
}

http_response_code(404);
echo 'Not Found';
