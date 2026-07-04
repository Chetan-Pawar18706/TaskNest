<?php
require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';
requireLogin($auth);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    $user_id = $auth->getUserId();
    $csrf = $_POST['csrf_token'] ?? '';
    if (!$auth->verifyCsrfToken($csrf)) { echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']); exit; }

    switch ($action) {
        case 'save_item': echo json_encode(saveShoppingHandler($mysqli, $user_id, $_POST)); break;
        case 'get_item': echo json_encode(getShoppingItemHandler($mysqli, $user_id, $_POST)); break;
        case 'toggle_complete': echo json_encode(toggleShoppingCompleteHandler($mysqli, $user_id, $_POST)); break;
        case 'delete_item': echo json_encode(deleteShoppingHandler($mysqli, $user_id, $_POST)); break;
        case 'clear_completed': echo json_encode(clearCompletedShoppingHandler($mysqli, $user_id, $_POST)); break;
        default: echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
    exit;
}
http_response_code(404);
echo 'Not Found';
