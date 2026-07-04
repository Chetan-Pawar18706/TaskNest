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
        case 'upload_document':
            echo json_encode(uploadDocumentHandler($mysqli, $user_id, $_POST));
            break;
        case 'get_document':
            echo json_encode(getDocumentHandler($mysqli, $user_id, $_POST));
            break;
        case 'update_document':
            echo json_encode(updateDocumentHandler($mysqli, $user_id, $_POST));
            break;
        case 'delete_document':
            echo json_encode(deleteDocumentHandler($mysqli, $user_id, $_POST));
            break;
        case 'permanent_delete_document':
            echo json_encode(permanentDeleteDocumentHandler($mysqli, $user_id, $_POST));
            break;
        case 'save_category':
            echo json_encode(saveDocumentCategoryHandler($mysqli, $user_id, $_POST));
            break;
        case 'delete_category':
            echo json_encode(deleteDocumentCategoryHandler($mysqli, $user_id, $_POST));
            break;
        case 'get_categories':
            echo json_encode(getDocumentCategoriesHandler($mysqli, $user_id, $_POST));
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
    exit;
}

http_response_code(404);
echo 'Not Found';
