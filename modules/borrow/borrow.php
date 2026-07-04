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
    if (!$auth->verifyCsrfToken($csrf)) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
        exit;
    }

    switch ($action) {
        case 'save_borrow':
            echo json_encode(saveBorrowHandler($mysqli, $user_id, $_POST));
            break;
        case 'get_borrow':
            echo json_encode(getBorrowHandler($mysqli, $user_id, $_POST));
            break;
        case 'delete_borrow':
            echo json_encode(deleteBorrowHandler($mysqli, $user_id, $_POST));
            break;
        case 'mark_returned':
            echo json_encode(markReturnedHandler($mysqli, $user_id, $_POST));
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
    exit;
}

http_response_code(404);
echo 'Not Found';
