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
        case 'save_goal': echo json_encode(saveGoalHandler($mysqli, $user_id, $_POST)); break;
        case 'update_progress': echo json_encode(updateGoalProgressHandler($mysqli, $user_id, $_POST)); break;
        case 'delete_goal': echo json_encode(deleteGoalHandler($mysqli, $user_id, $_POST)); break;
        case 'save_category': echo json_encode(saveGoalCategoryHandler($mysqli, $user_id, $_POST)); break;
        case 'delete_category': echo json_encode(deleteGoalCategoryHandler($mysqli, $user_id, $_POST)); break;
        default: echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
    exit;
}
http_response_code(404);
echo 'Not Found';
