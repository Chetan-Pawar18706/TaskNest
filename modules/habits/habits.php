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
        case 'save_habit': echo json_encode(saveHabitHandler($mysqli, $user_id, $_POST)); break;
        case 'get_habit': echo json_encode(getHabitHandler($mysqli, $user_id, $_POST)); break;
        case 'log_habit': echo json_encode(logHabitHandler($mysqli, $user_id, $_POST)); break;
        case 'delete_habit': echo json_encode(deleteHabitHandler($mysqli, $user_id, $_POST)); break;
        case 'chart_data': echo json_encode(getHabitChartDataHandler($mysqli, $user_id, $_POST)); break;
        default: echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
    exit;
}
http_response_code(404);
echo 'Not Found';
