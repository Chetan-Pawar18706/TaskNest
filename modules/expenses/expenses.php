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
        case 'save_expense':
            echo json_encode(saveExpenseHandler($mysqli, $user_id, $_POST));
            break;
        case 'get_expense':
            echo json_encode(getExpenseHandler($mysqli, $user_id, $_POST));
            break;
        case 'delete_expense':
            echo json_encode(deleteExpenseHandler($mysqli, $user_id, $_POST));
            break;
        case 'save_category':
            echo json_encode(saveExpenseCategoryHandler($mysqli, $user_id, $_POST));
            break;
        case 'delete_category':
            echo json_encode(deleteExpenseCategoryHandler($mysqli, $user_id, $_POST));
            break;
        case 'get_categories':
            echo json_encode(getExpenseCategoriesHandler($mysqli, $user_id, $_POST));
            break;
        case 'chart_data':
            echo json_encode(getExpenseChartDataHandler($mysqli, $user_id, $_POST));
            break;
        case 'category_breakdown':
            echo json_encode(getExpenseCategoryBreakdownHandler($mysqli, $user_id, $_POST));
            break;
        case 'save_budget':
            echo json_encode(saveBudgetHandler($mysqli, $user_id, $_POST));
            break;
        case 'get_budgets':
            echo json_encode(getBudgetsHandler($mysqli, $user_id, $_POST));
            break;
        case 'delete_budget':
            echo json_encode(deleteBudgetHandler($mysqli, $user_id, $_POST));
            break;
        case 'get_budget':
            echo json_encode(getBudgetHandler($mysqli, $user_id, $_POST));
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $user_id = $auth->getUserId();
    if ($_GET['action'] === 'export_csv') {
        exportExpensesCsvHandler($mysqli, $user_id, $_GET);
    }
    if ($_GET['action'] === 'chart_data') {
        header('Content-Type: application/json');
        $months = (int) ($_GET['months'] ?? 6);
        echo json_encode(getIncomeExpenseChartData($mysqli, $user_id, $months));
        exit;
    }
}

http_response_code(404);
echo 'Not Found';
