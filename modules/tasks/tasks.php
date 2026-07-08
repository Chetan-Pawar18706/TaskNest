<?php
require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

requireLogin($auth);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    $user_id = $auth->getUserId();

    $writeActions = ['save_task','delete_task','restore_task','permanent_delete_task',
                     'duplicate_task','update_status','bulk_action',
                     'save_category','delete_category'];
    if (in_array($action, $writeActions)) {
        $csrf = $_POST['csrf_token'] ?? '';
        if (!$auth->verifyCsrfToken($csrf)) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
            exit;
        }
    }

    switch ($action) {
        case 'save_task':
            echo json_encode(saveTaskHandler($mysqli, $user_id, $_POST));
            break;
        case 'delete_task':
            echo json_encode(deleteTaskHandler($mysqli, $user_id, $_POST));
            break;
        case 'restore_task':
            echo json_encode(restoreTaskHandler($mysqli, $user_id, $_POST));
            break;
        case 'permanent_delete_task':
            echo json_encode(permanentDeleteTaskHandler($mysqli, $user_id, $_POST));
            break;
        case 'duplicate_task':
            echo json_encode(duplicateTaskHandler($mysqli, $user_id, $_POST));
            break;
        case 'update_status':
            echo json_encode(updateTaskStatusHandler($mysqli, $user_id, $_POST));
            break;
        case 'bulk_action':
            echo json_encode(bulkTaskActionHandler($mysqli, $user_id, $_POST));
            break;
        case 'save_category':
            echo json_encode(saveCategoryHandler($mysqli, $user_id, $_POST));
            break;
        case 'delete_category':
            echo json_encode(deleteCategoryHandler($mysqli, $user_id, $_POST));
            break;
        case 'get_task':
            echo json_encode(getTaskHandler($mysqli, $user_id, $_POST));
            break;
        case 'get_categories':
            echo json_encode(getCategoriesHandler($mysqli, $user_id, $_POST));
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
    exit;
}

http_response_code(404);
echo 'Not Found';
