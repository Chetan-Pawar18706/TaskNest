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
        // Actions that don't need vault unlocked
        case 'unlock_vault':
            $password = $_POST['vault_password'] ?? '';
            $stmt = safePrepare($mysqli, 'SELECT password_hash FROM users WHERE id = ?');
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            if ($row && password_verify($password, $row['password_hash'])) {
                $_SESSION['vault_password'] = $password;
                $_SESSION['vault_unlocked'] = true;
                $_SESSION['vault_unlock_time'] = time();
                echo json_encode(['success' => true, 'message' => 'Vault unlocked.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid password.']);
            }
            break;
        case 'lock_vault':
            unset($_SESSION['vault_password']);
            unset($_SESSION['vault_unlocked']);
            unset($_SESSION['vault_unlock_time']);
            echo json_encode(['success' => true, 'message' => 'Vault locked.']);
            break;
        case 'check_vault':
            $locked = empty($_SESSION['vault_unlocked']) || empty($_SESSION['vault_unlock_time']) || (time() - $_SESSION['vault_unlock_time'] >= 1800);
            echo json_encode(['success' => true, 'locked' => $locked]);
            break;
        case 'generate_password':
            $length = (int)($_POST['length'] ?? 16);
            $options = [
                'uppercase' => !empty($_POST['uppercase']),
                'lowercase' => !empty($_POST['lowercase']),
                'numbers' => !empty($_POST['numbers']),
                'symbols' => !empty($_POST['symbols']),
            ];
            echo json_encode(['success' => true, 'password' => generateStrongPassword($length, $options)]);
            break;

        // Actions that need vault unlocked
        default:
            $vault_unlocked = !empty($_SESSION['vault_unlocked']) && !empty($_SESSION['vault_unlock_time']) && (time() - $_SESSION['vault_unlock_time'] < 1800);
            $vaultPassword = $_SESSION['vault_password'] ?? null;
            if (!$vault_unlocked || $vaultPassword === null) {
                unset($_SESSION['vault_unlocked'], $_SESSION['vault_unlock_time'], $_SESSION['vault_password']);
                echo json_encode(['success' => false, 'message' => 'Vault is locked. Please unlock first.']);
                exit;
            }
            $encryptionKey = deriveEncryptionKey($vaultPassword, 'tasknest-' . $user_id . '-vault');

            switch ($action) {
                case 'save_password':
                    echo json_encode(savePasswordHandler($mysqli, $user_id, $_POST, $encryptionKey));
                    break;
                case 'get_passwords':
                    echo json_encode(getPasswordsHandler($mysqli, $user_id, $_POST));
                    break;
                case 'get_password':
                    echo json_encode(getPasswordHandler($mysqli, $user_id, $_POST, $encryptionKey));
                    break;
                case 'delete_password':
                    echo json_encode(deletePasswordHandler($mysqli, $user_id, $_POST));
                    break;
                case 'toggle_favorite':
                    echo json_encode(togglePasswordFavoriteHandler($mysqli, $user_id, $_POST));
                    break;
                case 'bulk_action':
                    echo json_encode(bulkPasswordActionHandler($mysqli, $user_id, $_POST));
                    break;
                case 'save_category':
                    echo json_encode(savePasswordCategoryHandler($mysqli, $user_id, $_POST));
                    break;
                case 'delete_category':
                    echo json_encode(deletePasswordCategoryHandler($mysqli, $user_id, $_POST));
                    break;
                case 'get_categories':
                    echo json_encode(getPasswordCategoriesHandler($mysqli, $user_id, $_POST));
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Unknown action']);
            }
    }
    exit;
}

http_response_code(404);
echo 'Not Found';
