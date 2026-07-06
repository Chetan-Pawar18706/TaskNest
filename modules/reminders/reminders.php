<?php
require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

requireLogin($auth);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    $user_id = $auth->getUserId();

    $writeActions = ['save_reminder', 'delete_reminder', 'toggle_reminder', 'bulk_action'];
    if (in_array($action, $writeActions)) {
        $csrf = $_POST['csrf_token'] ?? '';
        if (!$auth->verifyCsrfToken($csrf)) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
            exit;
        }
    }

    ensureReminderTableExists($mysqli);

    switch ($action) {
        case 'save_reminder':
            echo json_encode(saveReminder($mysqli, $user_id, $_POST));
            break;
        case 'get_reminders':
            $filters = [
                'search' => $_POST['search'] ?? '',
                'priority' => $_POST['priority'] ?? '',
                'category' => $_POST['category'] ?? '',
            ];
            $status = $_POST['status'] ?? '';
            $today = date('Y-m-d');
            if ($status === 'overdue') {
                $filters['date_to'] = date('Y-m-d', strtotime('-1 day'));
            } elseif ($status === 'today') {
                $filters['date_from'] = $today;
                $filters['date_to'] = $today;
            } elseif ($status === 'upcoming') {
                $filters['date_from'] = date('Y-m-d', strtotime('+1 day'));
            }
            echo json_encode(['success' => true, 'reminders' => getReminders($mysqli, $user_id, $filters)]);
            break;
        case 'get_reminder':
            $id = (int)($_POST['reminder_id'] ?? 0);
            $reminder = getReminderById($mysqli, $user_id, $id);
            echo json_encode($reminder ? ['success' => true, 'reminder' => $reminder] : ['success' => false, 'message' => 'Not found.']);
            break;
        case 'delete_reminder':
            echo json_encode(deleteReminder($mysqli, $user_id, (int)($_POST['reminder_id'] ?? 0)));
            break;
        case 'toggle_reminder':
            echo json_encode(toggleReminder($mysqli, $user_id, (int)($_POST['reminder_id'] ?? 0)));
            break;
        case 'bulk_action':
            $type = $_POST['type'] ?? '';
            $ids = json_decode($_POST['ids'] ?? '[]', true);
            if ($type === 'delete' && is_array($ids)) {
                $deleted = 0;
                foreach ($ids as $id) {
                    $r = deleteReminder($mysqli, $user_id, (int)$id);
                    if ($r['success']) $deleted++;
                }
                echo json_encode(['success' => true, 'message' => "$deleted reminder(s) deleted."]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid action.']);
            }
            break;
        case 'get_bell':
            $upcoming = getUpcomingRemindersForBell($mysqli, $user_id);
            $overdue = getOverdueReminders($mysqli, $user_id);
            $count = getReminderCountForBell($mysqli, $user_id);
            echo json_encode(['success' => true, 'count' => $count, 'upcoming' => $upcoming, 'overdue' => $overdue]);
            break;
        case 'get_categories':
            echo json_encode(['success' => true, 'categories' => getReminderCategories($mysqli, $user_id)]);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
    }
    exit;
}

http_response_code(404);
echo 'Not Found';
