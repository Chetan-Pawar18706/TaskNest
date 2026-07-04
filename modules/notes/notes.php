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
        case 'save_note':
            echo json_encode(saveNoteHandler($mysqli, $user_id, $_POST));
            break;
        case 'get_note':
            echo json_encode(getNoteHandler($mysqli, $user_id, $_POST));
            break;
        case 'delete_note':
            echo json_encode(deleteNoteHandler($mysqli, $user_id, $_POST));
            break;
        case 'restore_note':
            echo json_encode(restoreNoteHandler($mysqli, $user_id, $_POST));
            break;
        case 'permanent_delete_note':
            echo json_encode(permanentDeleteNoteHandler($mysqli, $user_id, $_POST));
            break;
        case 'toggle_pin':
            echo json_encode(toggleNotePinHandler($mysqli, $user_id, $_POST));
            break;
        case 'toggle_archive':
            echo json_encode(toggleNoteArchiveHandler($mysqli, $user_id, $_POST));
            break;
        case 'duplicate_note':
            echo json_encode(duplicateNoteHandler($mysqli, $user_id, $_POST));
            break;
        case 'bulk_action':
            echo json_encode(bulkNoteActionHandler($mysqli, $user_id, $_POST));
            break;
        case 'save_category':
            echo json_encode(saveNoteCategoryHandler($mysqli, $user_id, $_POST));
            break;
        case 'delete_category':
            echo json_encode(deleteNoteCategoryHandler($mysqli, $user_id, $_POST));
            break;
        case 'get_categories':
            echo json_encode(getNoteCategoriesHandler($mysqli, $user_id, $_POST));
            break;
        case 'upload_image':
            echo json_encode(uploadNoteImageHandler($mysqli, $user_id, $_POST));
            break;
        case 'delete_image':
            echo json_encode(deleteNoteImageHandler($mysqli, $user_id, $_POST));
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
    exit;
}

http_response_code(404);
echo 'Not Found';
