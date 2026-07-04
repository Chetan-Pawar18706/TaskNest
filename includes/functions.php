<?php
/**
 * TaskNest - Utility Functions
 */

/**
 * Safe wrapper for $mysqli->prepare() that returns a no-op statement
 * instead of false, preventing "bind_param() on bool" errors when
 * a table does not exist or the query is invalid.
 */
class _NullStmt {
    public function bind_param() { return false; }
    public function execute() { return false; }
    public function get_result() { return new _NullResult(); }
    public function affected_rows() { return 0; }
    public function insert_id() { return 0; }
    public function store_result() { return false; }
    public function close() { return true; }
    public function __get($name) { return 0; }
}

class _NullResult {
    public function fetch_assoc() { return null; }
    public function fetch_array() { return null; }
    public function fetch_all() { return []; }
    public function num_rows() { return 0; }
    public function __get($name) { return 0; }
}

function safePrepare($mysqli, $sql) {
    $stmt = @$mysqli->prepare($sql);
    if ($stmt === false) {
        logError("SQL prepare failed: " . $mysqli->error . " | Query: " . substr($sql, 0, 200), 'SQL_ERROR');
        return new _NullStmt();
    }
    return $stmt;
}

/**
 * Sanitize input to prevent XSS
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Escape data for SQL (use prepared statements instead)
 */
function escape($data) {
    global $mysqli;
    return $mysqli->real_escape_string($data);
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 */
function isStrongPassword($password) {
    return strlen($password) >= 8 && 
           preg_match('/[A-Z]/', $password) && 
           preg_match('/[0-9]/', $password) &&
           preg_match('/[^A-Za-z0-9]/', $password);
}

/**
 * Generate random string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Redirect to URL
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Require login, redirect if not
 */
function requireLogin($auth) {
    if (!$auth->isLoggedIn()) {
        redirect(SITE_URL . '/login.php');
    }
}

/**
 * Require guest, redirect if logged in
 */
function requireGuest($auth) {
    if ($auth->isLoggedIn()) {
        redirect(SITE_URL . '/dashboard.php');
    }
}

/**
 * Format date
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

/**
 * Get time ago string (e.g., "2 hours ago")
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return formatDate($datetime, 'M d, Y');
    }
}

/**
 * Validate file upload
 */
function validateFileUpload($file, $allowed_types = ALLOWED_UPLOAD_TYPES, $max_size = MAX_UPLOAD_SIZE) {
    $errors = [];
    
    if (!isset($file['error']) || is_array($file['error'])) {
        $errors[] = 'Invalid file upload';
        return ['valid' => false, 'errors' => $errors];
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload failed with error code: ' . $file['error'];
        return ['valid' => false, 'errors' => $errors];
    }
    
    if ($file['size'] > $max_size) {
        $errors[] = 'File size exceeds maximum limit of ' . ($max_size / 1024 / 1024) . 'MB';
        return ['valid' => false, 'errors' => $errors];
    }
    
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_types)) {
        $errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $allowed_types);
        return ['valid' => false, 'errors' => $errors];
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowed_mimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif'
    ];
    
    if (!isset($allowed_mimes[$file_ext]) || $mime_type !== $allowed_mimes[$file_ext]) {
        $errors[] = 'File MIME type does not match extension';
        return ['valid' => false, 'errors' => $errors];
    }
    
    return ['valid' => true, 'errors' => []];
}

/**
 * Save uploaded file
 */
function saveUploadedFile($file, $upload_dir = AVATAR_UPLOAD_DIR) {
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $filename = bin2hex(random_bytes(16)) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $filepath,
            'url' => str_replace(UPLOAD_DIR, UPLOAD_URL, $filepath)
        ];
    }
    
    return ['success' => false, 'error' => 'Failed to save file'];
}

/**
 * Get file URL
 */
function getFileUrl($filename, $upload_url = AVATAR_UPLOAD_URL) {
    return $upload_url . $filename;
}

/**
 * Format bytes to human readable
 */
function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Check whether a table exists in the current database.
 */
function tableExists($mysqli, $table_name) {
    $stmt = safePrepare($mysqli, "SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param('s', $table_name);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

/**
 * Generate dashboard cards data
 */
function getDashboardCounts($mysqli, $user_id) {
    $counts = [
        'tasks' => 0,
        'completed_tasks' => 0,
        'pending_tasks' => 0,
        'notes' => 0,
        'expenses' => 0,
        'monthly_expense' => 0,
        'documents' => 0,
        'habits' => 0,
        'goals' => 0,
        'shopping' => 0,
        'borrow' => 0
    ];

    if (tableExists($mysqli, 'tasks')) {
        $stmt = safePrepare($mysqli, "SELECT COUNT(*) AS total, SUM(CASE WHEN completed = 1 THEN 1 ELSE 0 END) AS completed, SUM(CASE WHEN completed = 0 THEN 1 ELSE 0 END) AS pending FROM tasks WHERE user_id = ? AND is_deleted = 0");
        if ($stmt) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            if ($row) {
                $counts['tasks'] = (int) ($row['total'] ?? 0);
                $counts['completed_tasks'] = (int) ($row['completed'] ?? 0);
                $counts['pending_tasks'] = (int) ($row['pending'] ?? 0);
            }
        }
    }

    if (tableExists($mysqli, 'notes')) {
        $stmt = safePrepare($mysqli, "SELECT COUNT(*) AS total FROM notes WHERE user_id = ? AND is_deleted = 0");
        if ($stmt) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $counts['notes'] = (int) ($row['total'] ?? 0);
        }
    }

    if (tableExists($mysqli, 'expenses')) {
        $stmt = safePrepare($mysqli, "SELECT COUNT(*) AS total, COALESCE(SUM(amount), 0) AS amount FROM expenses WHERE user_id = ? AND is_deleted = 0 AND created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')");
        if ($stmt) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $counts['expenses'] = (int) ($row['total'] ?? 0);
            $counts['monthly_expense'] = (float) ($row['amount'] ?? 0);
        }
    }

    if (tableExists($mysqli, 'documents')) {
        $stmt = safePrepare($mysqli, "SELECT COUNT(*) AS total FROM documents WHERE user_id = ? AND is_deleted = 0");
        if ($stmt) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $counts['documents'] = (int) ($row['total'] ?? 0);
        }
    }

    if (tableExists($mysqli, 'habits')) {
        $stmt = safePrepare($mysqli, "SELECT COUNT(*) AS total FROM habits WHERE user_id = ? AND is_deleted = 0");
        if ($stmt) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $counts['habits'] = (int) ($row['total'] ?? 0);
        }
    }

    if (tableExists($mysqli, 'goals')) {
        $stmt = safePrepare($mysqli, "SELECT COUNT(*) AS total FROM goals WHERE user_id = ? AND status = 'active'");
        if ($stmt) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $counts['goals'] = (int) ($row['total'] ?? 0);
        }
    }

    if (tableExists($mysqli, 'shopping')) {
        $stmt = safePrepare($mysqli, "SELECT COUNT(*) AS total FROM shopping WHERE user_id = ? AND is_deleted = 0 AND is_completed = 0");
        if ($stmt) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $counts['shopping'] = (int) ($row['total'] ?? 0);
        }
    }

    if (tableExists($mysqli, 'borrow_items')) {
        $stmt = safePrepare($mysqli, "SELECT COUNT(*) AS total FROM borrow_items WHERE user_id = ? AND status = 'pending'");
        if ($stmt) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $counts['borrow'] = (int) ($row['total'] ?? 0);
        }
    }

    return $counts;
}

/**
 * Get recent activity
 */
function getRecentActivity($mysqli, $user_id, $limit = 10) {
    if (!tableExists($mysqli, 'activity_logs')) {
        return [];
    }

    $stmt = safePrepare($mysqli, "
        SELECT action, entity_type, description, created_at
        FROM activity_logs
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT ?
    ");
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $activities = [];
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }

    return $activities;
}

/**
 * Get reminders due in the next X days.
 */
function getUpcomingReminders($mysqli, $user_id, $days = 7) {
    $reminders = [];

    if (tableExists($mysqli, 'tasks')) {
        $stmt = safePrepare($mysqli, "SELECT title, due_date AS due_date, 'Task' AS label FROM tasks WHERE user_id = ? AND due_date IS NOT NULL AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY) ORDER BY due_date ASC LIMIT 5");
        if ($stmt) {
            $stmt->bind_param('ii', $user_id, $days);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $reminders[] = $row;
            }
        }
    }

    if (tableExists($mysqli, 'goals')) {
        $stmt = safePrepare($mysqli, "SELECT title, due_date AS due_date, 'Goal' AS label FROM goals WHERE user_id = ? AND due_date IS NOT NULL AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY) ORDER BY due_date ASC LIMIT 5");
        if ($stmt) {
            $stmt->bind_param('ii', $user_id, $days);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $reminders[] = $row;
            }
        }
    }

    if (tableExists($mysqli, 'documents')) {
        $stmt = safePrepare($mysqli, "SELECT title, expiry_date AS due_date, 'Document' AS label FROM documents WHERE user_id = ? AND expiry_date IS NOT NULL AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY) ORDER BY expiry_date ASC LIMIT 5");
        if ($stmt) {
            $stmt->bind_param('ii', $user_id, $days);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $reminders[] = $row;
            }
        }
    }

    if (tableExists($mysqli, 'borrow')) {
        $stmt = safePrepare($mysqli, "SELECT title, return_date AS due_date, 'Borrow' AS label FROM borrow WHERE user_id = ? AND return_date IS NOT NULL AND return_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY) ORDER BY return_date ASC LIMIT 5");
        if ($stmt) {
            $stmt->bind_param('ii', $user_id, $days);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $reminders[] = $row;
            }
        }
    }

    usort($reminders, function ($a, $b) {
        return strcmp($a['due_date'], $b['due_date']);
    });

    return array_slice($reminders, 0, 8);
}

/**
 * Prepare monthly expense chart data.
 */
function getExpenseChartData($mysqli, $user_id, $months = 6) {
    $labels = [];
    $values = [];

    if (tableExists($mysqli, 'expenses')) {
        $stmt = safePrepare($mysqli, "SELECT DATE_FORMAT(created_at, '%b') AS month_label, SUM(amount) AS total FROM expenses WHERE user_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH) GROUP BY YEAR(created_at), MONTH(created_at) ORDER BY created_at ASC");
        if ($stmt) {
            $stmt->bind_param('ii', $user_id, $months);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $labels[] = $row['month_label'];
                $values[] = (float) ($row['total'] ?? 0);
            }
        }
    }

    if (empty($labels)) {
        for ($index = $months - 1; $index >= 0; $index--) {
            $labels[] = date('M', strtotime("-$index months"));
            $values[] = 0;
        }
    }

    return ['labels' => $labels, 'values' => $values];
}

/**
 * Prepare task completion chart data.
 */
function getTaskCompletionData($mysqli, $user_id) {
    $completed = 0;
    $pending = 0;

    if (tableExists($mysqli, 'tasks')) {
        $stmt = safePrepare($mysqli, "SELECT SUM(CASE WHEN completed = 1 THEN 1 ELSE 0 END) AS completed_count, SUM(CASE WHEN completed = 0 THEN 1 ELSE 0 END) AS pending_count FROM tasks WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $completed = (int) ($row['completed_count'] ?? 0);
            $pending = (int) ($row['pending_count'] ?? 0);
        }
    }

    return ['completed' => $completed, 'pending' => $pending];
}

/**
 * Prepare habit progress chart data.
 */
function getHabitProgressData($mysqli, $user_id) {
    $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    $values = [0, 0, 0, 0, 0, 0, 0];

    if (tableExists($mysqli, 'habit_logs')) {
        $stmt = safePrepare($mysqli, "SELECT DAYNAME(created_at) AS day_name, COUNT(*) AS total FROM habit_logs WHERE user_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DAYNAME(created_at)");
        if ($stmt) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $index = array_search($row['day_name'], $labels, true);
                if ($index !== false) {
                    $values[$index] = (int) ($row['total'] ?? 0);
                }
            }
        }
    }

    return ['labels' => $labels, 'values' => $values];
}

/**
 * Get calendar events for the current month.
 */
function getCalendarEvents($mysqli, $user_id, $days = 30) {
    if (!tableExists($mysqli, 'calendar_events')) {
        return [];
    }

    $stmt = safePrepare($mysqli, "SELECT title, event_date FROM calendar_events WHERE user_id = ? AND event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY) ORDER BY event_date ASC");
    if (!$stmt) {
        return [];
    }
    $stmt->bind_param('ii', $user_id, $days);
    $stmt->execute();
    $result = $stmt->get_result();

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    return $events;
}

/**
 * Check if running on HTTPS
 */
function isHttps() {
    return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
}

/**
 * Get user's timezone
 */
function getUserTimezone($user_data) {
    return $user_data['timezone'] ?? 'UTC';
}

/**
 * Convert to user timezone
 */
function convertToUserTimezone($datetime, $user_timezone = 'UTC') {
    try {
        $dt = new DateTime($datetime, new DateTimeZone('UTC'));
        $dt->setTimezone(new DateTimeZone($user_timezone));
        return $dt->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        return $datetime;
    }
}

/**
 * Send JSON response
 */
function sendJsonResponse($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Log message to file
 */
function logMessage($message, $type = 'INFO') {
    $log_file = __DIR__ . '/../logs/app.log';
    if (!is_dir(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] [$type] $message\n", 3, $log_file);
}

/**
 * Get gravatar URL
 */
function getGravatarUrl($email, $size = 150, $default = 'mp') {
    $hash = md5(strtolower(trim($email)));
    return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d={$default}";
}

function ensureTaskTablesExist($mysqli) {
    $statements = [
        "CREATE TABLE IF NOT EXISTS task_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            color VARCHAR(20) DEFAULT '#6366f1',
            icon VARCHAR(50) DEFAULT 'task',
            is_deleted TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_category_user (user_id, name),
            INDEX idx_user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS tasks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'Pending',
            priority VARCHAR(20) NOT NULL DEFAULT 'Medium',
            category_id INT DEFAULT NULL,
            due_date DATE DEFAULT NULL,
            reminder_datetime DATETIME DEFAULT NULL,
            completed TINYINT(1) DEFAULT 0,
            is_deleted TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            completed_at TIMESTAMP NULL DEFAULT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES task_categories(id) ON DELETE SET NULL,
            INDEX idx_user_status (user_id, status),
            INDEX idx_user_due (user_id, due_date),
            INDEX idx_user_deleted (user_id, is_deleted)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS task_activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            task_id INT DEFAULT NULL,
            action VARCHAR(100) NOT NULL,
            description TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_task_activity (task_id, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];

    foreach ($statements as $statement) {
        if (!$mysqli->query($statement)) {
            logError("Table creation failed: " . $mysqli->error . " | Query: " . substr($statement, 0, 200), 'SQL_ERROR');
        }
    }
}

function logTaskActivity($mysqli, $user_id, $task_id, $action, $description) {
    ensureTaskTablesExist($mysqli);
    $stmt = safePrepare($mysqli, "INSERT INTO task_activity_logs (user_id, task_id, action, description) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param('iiss', $user_id, $task_id, $action, $description);
        $stmt->execute();
    }
}

function getTaskCategories($mysqli, $user_id) {
    ensureTaskTablesExist($mysqli);
    $stmt = safePrepare($mysqli, "SELECT id, name, color, icon FROM task_categories WHERE user_id = ? AND is_deleted = 0 ORDER BY name ASC");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }

    return $categories;
}

function getTaskStats($mysqli, $user_id) {
    ensureTaskTablesExist($mysqli);
    $stmt = safePrepare($mysqli, "SELECT COUNT(*) AS total, SUM(CASE WHEN status = 'Completed' OR completed = 1 THEN 1 ELSE 0 END) AS completed, SUM(CASE WHEN is_deleted = 0 AND (status <> 'Completed' AND completed = 0) THEN 1 ELSE 0 END) AS pending, SUM(CASE WHEN due_date IS NOT NULL AND due_date < CURDATE() AND is_deleted = 0 AND (status <> 'Completed' AND completed = 0) THEN 1 ELSE 0 END) AS overdue FROM tasks WHERE user_id = ? AND is_deleted = 0");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    return [
        'total' => (int) ($row['total'] ?? 0),
        'completed' => (int) ($row['completed'] ?? 0),
        'pending' => (int) ($row['pending'] ?? 0),
        'overdue' => (int) ($row['overdue'] ?? 0)
    ];
}

function buildTaskPaginationUrl($filters, $page) {
    $params = [];
    foreach ($filters as $key => $value) {
        if ($value === '' || $value === null || $value === false || $value === 0) {
            continue;
        }
        $params[] = urlencode($key) . '=' . urlencode((string) $value);
    }
    $params[] = 'page=' . (int) $page;
    return '?' . implode('&', $params);
}

function getTasksForList($mysqli, $user_id, $filters) {
    ensureTaskTablesExist($mysqli);

    $conditions = ['t.user_id = ?','t.is_deleted = 0'];
    $params = [$user_id];
    $types = 'i';

    if (!empty($filters['search'])) {
        $search = '%' . $filters['search'] . '%';
        $conditions[] = '(t.title LIKE ? OR t.description LIKE ?)';
        $params[] = $search;
        $params[] = $search;
        $types .= 'ss';
    }
    if (!empty($filters['status'])) {
        $conditions[] = 't.status = ?';
        $params[] = $filters['status'];
        $types .= 's';
    }
    if (!empty($filters['priority'])) {
        $conditions[] = 't.priority = ?';
        $params[] = $filters['priority'];
        $types .= 's';
    }
    if (!empty($filters['category'])) {
        $conditions[] = 't.category_id = ?';
        $params[] = (int) $filters['category'];
        $types .= 'i';
    }
    if (!empty($filters['date_range'])) {
        switch ($filters['date_range']) {
            case 'today':
                $conditions[] = 't.due_date = CURDATE()';
                break;
            case 'week':
                $conditions[] = 't.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)';
                break;
            case 'month':
                $conditions[] = 't.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)';
                break;
        }
    }
    if (!empty($filters['overdue'])) {
        $conditions[] = 't.due_date < CURDATE() AND (t.status <> ? AND t.completed = 0)';
        $params[] = 'Completed';
        $types .= 's';
    }
    if (!empty($filters['due_today'])) {
        $conditions[] = 't.due_date = CURDATE()';
    }
    if (!empty($filters['due_week'])) {
        $conditions[] = 't.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)';
    }

    $whereSql = implode(' AND ', $conditions);
    $countStmt = safePrepare($mysqli, "SELECT COUNT(*) AS total FROM tasks t WHERE $whereSql");
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $countRow = $countStmt->get_result()->fetch_assoc();
    $total = (int) ($countRow['total'] ?? 0);

    $perPage = 15;
    $page = max(1, (int) ($filters['page'] ?? 1));
    $offset = ($page - 1) * $perPage;
    $totalPages = max(1, (int) ceil($total / $perPage));

    $sortMap = [
        'due_date' => 't.due_date IS NULL, t.due_date ASC',
        'priority' => 'FIELD(t.priority, "High", "Medium", "Low") ASC, t.due_date ASC',
        'status' => 'FIELD(t.status, "Pending", "In Progress", "Completed") ASC, t.due_date ASC',
        'created_at' => 't.created_at DESC',
        'title' => 't.title ASC'
    ];
    $sortColumn = $sortMap[$filters['sort']] ?? 't.due_date IS NULL, t.due_date ASC';

    $stmt = safePrepare($mysqli, "SELECT t.*, tc.name AS category_name FROM tasks t LEFT JOIN task_categories tc ON tc.id = t.category_id WHERE $whereSql ORDER BY $sortColumn LIMIT ? OFFSET ?");
    $bindTypes = $types . 'ii';
    $bindParams = array_merge($params, [$perPage, $offset]);
    $stmt->bind_param($bindTypes, ...$bindParams);
    $stmt->execute();
    $result = $stmt->get_result();

    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }

    return [
        'tasks' => $tasks,
        'total' => $total,
        'total_pages' => $totalPages,
        'page' => $page,
        'per_page' => $perPage
    ];
}

function saveTaskHandler($mysqli, $user_id, $post) {
    ensureTaskTablesExist($mysqli);
    $title = trim($post['title'] ?? '');
    if ($title === '') {
        return ['success' => false, 'message' => 'Task title is required.'];
    }

    $taskId = !empty($post['task_id']) ? (int) $post['task_id'] : 0;
    $status = $post['status'] ?? 'Pending';
    $priority = $post['priority'] ?? 'Medium';
    $categoryId = !empty($post['category_id']) ? (int) $post['category_id'] : null;
    $dueDate = !empty($post['due_date']) ? $post['due_date'] : null;
    $reminderDatetime = !empty($post['reminder_datetime']) ? $post['reminder_datetime'] : null;
    $description = trim($post['description'] ?? '');
    $completed = ($status === 'Completed') ? 1 : 0;

    if ($categoryId !== null) {
        $categoryStmt = safePrepare($mysqli, 'SELECT id FROM task_categories WHERE id = ? AND user_id = ? AND is_deleted = 0');
        $categoryStmt->bind_param('ii', $categoryId, $user_id);
        $categoryStmt->execute();
        if ($categoryStmt->get_result()->num_rows === 0) {
            $categoryId = null;
        }
    }

    if ($taskId > 0) {
        $stmt = safePrepare($mysqli, "UPDATE tasks SET title = ?, description = ?, status = ?, priority = ?, category_id = ?, due_date = ?, reminder_datetime = ?, completed = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ? AND is_deleted = 0");
        $stmt->bind_param('ssssissiii', $title, $description, $status, $priority, $categoryId, $dueDate, $reminderDatetime, $completed, $taskId, $user_id);
        if ($stmt->execute()) {
            logTaskActivity($mysqli, $user_id, $taskId, 'task_updated', 'Task updated');
            return ['success' => true, 'message' => 'Task updated successfully.'];
        }
        return ['success' => false, 'message' => 'Unable to update task.'];
    }

    $stmt = safePrepare($mysqli, "INSERT INTO tasks (user_id, title, description, status, priority, category_id, due_date, reminder_datetime, completed) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('issssissi', $user_id, $title, $description, $status, $priority, $categoryId, $dueDate, $reminderDatetime, $completed);
    if ($stmt->execute()) {
        $newId = $stmt->insert_id;
        logTaskActivity($mysqli, $user_id, $newId, 'task_created', 'Task created');
        return ['success' => true, 'message' => 'Task created successfully.', 'task_id' => $newId];
    }

    return ['success' => false, 'message' => 'Unable to create task.'];
}

function getTaskHandler($mysqli, $user_id, $post) {
    ensureTaskTablesExist($mysqli);
    $taskId = !empty($post['task_id']) ? (int) $post['task_id'] : 0;
    $stmt = safePrepare($mysqli, 'SELECT * FROM tasks WHERE id = ? AND user_id = ? AND is_deleted = 0');
    $stmt->bind_param('ii', $taskId, $user_id);
    $stmt->execute();
    $task = $stmt->get_result()->fetch_assoc();

    if ($task) {
        return ['success' => true, 'task' => $task];
    }

    return ['success' => false, 'message' => 'Task not found.'];
}

function deleteTaskHandler($mysqli, $user_id, $post) {
    ensureTaskTablesExist($mysqli);
    $taskId = !empty($post['task_id']) ? (int) $post['task_id'] : 0;
    $stmt = safePrepare($mysqli, 'UPDATE tasks SET is_deleted = 1, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $taskId, $user_id);
    if ($stmt->execute()) {
        logTaskActivity($mysqli, $user_id, $taskId, 'task_deleted', 'Task deleted');
        return ['success' => true, 'message' => 'Task moved to trash.'];
    }
    return ['success' => false, 'message' => 'Unable to delete task.'];
}

function restoreTaskHandler($mysqli, $user_id, $post) {
    ensureTaskTablesExist($mysqli);
    $taskId = !empty($post['task_id']) ? (int) $post['task_id'] : 0;
    $stmt = safePrepare($mysqli, 'UPDATE tasks SET is_deleted = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $taskId, $user_id);
    if ($stmt->execute()) {
        logTaskActivity($mysqli, $user_id, $taskId, 'task_restored', 'Task restored');
        return ['success' => true, 'message' => 'Task restored.'];
    }
    return ['success' => false, 'message' => 'Unable to restore task.'];
}

function permanentDeleteTaskHandler($mysqli, $user_id, $post) {
    ensureTaskTablesExist($mysqli);
    $taskId = !empty($post['task_id']) ? (int) $post['task_id'] : 0;
    $stmt = safePrepare($mysqli, 'DELETE FROM tasks WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $taskId, $user_id);
    if ($stmt->execute()) {
        logTaskActivity($mysqli, $user_id, $taskId, 'task_permanently_deleted', 'Task permanently deleted');
        return ['success' => true, 'message' => 'Task permanently deleted.'];
    }
    return ['success' => false, 'message' => 'Unable to permanently delete task.'];
}

function duplicateTaskHandler($mysqli, $user_id, $post) {
    ensureTaskTablesExist($mysqli);
    $taskId = !empty($post['task_id']) ? (int) $post['task_id'] : 0;
    $stmt = safePrepare($mysqli, 'SELECT * FROM tasks WHERE id = ? AND user_id = ? AND is_deleted = 0');
    $stmt->bind_param('ii', $taskId, $user_id);
    $stmt->execute();
    $task = $stmt->get_result()->fetch_assoc();

    if (!$task) {
        return ['success' => false, 'message' => 'Task not found.'];
    }

    $insertStmt = safePrepare($mysqli, 'INSERT INTO tasks (user_id, title, description, status, priority, category_id, due_date, reminder_datetime, completed) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $duplicateTitle = $task['title'] . ' (Copy)';
    $insertStmt->bind_param('issssissi', $user_id, $duplicateTitle, $task['description'], $task['status'], $task['priority'], $task['category_id'], $task['due_date'], $task['reminder_datetime'], $task['completed']);
    if ($insertStmt->execute()) {
        logTaskActivity($mysqli, $user_id, $insertStmt->insert_id, 'task_duplicated', 'Task duplicated');
        return ['success' => true, 'message' => 'Task duplicated.'];
    }
    return ['success' => false, 'message' => 'Unable to duplicate task.'];
}

function updateTaskStatusHandler($mysqli, $user_id, $post) {
    ensureTaskTablesExist($mysqli);
    $taskId = !empty($post['task_id']) ? (int) $post['task_id'] : 0;
    $status = $post['status'] ?? 'Completed';
    $completed = ($status === 'Completed') ? 1 : 0;
    $stmt = safePrepare($mysqli, 'UPDATE tasks SET status = ?, completed = ?, completed_at = IF(? = 1, CURRENT_TIMESTAMP, NULL), updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ? AND is_deleted = 0');
    $stmt->bind_param('siiii', $status, $completed, $completed, $taskId, $user_id);
    if ($stmt->execute()) {
        logTaskActivity($mysqli, $user_id, $taskId, 'task_status_updated', 'Task status updated');
        return ['success' => true, 'message' => 'Task status updated.'];
    }
    return ['success' => false, 'message' => 'Unable to update task status.'];
}

function bulkTaskActionHandler($mysqli, $user_id, $post) {
    ensureTaskTablesExist($mysqli);
    $bulkAction = $post['bulk_action'] ?? '';
    $taskIds = array_filter(array_map('intval', explode(',', $post['task_ids'] ?? '')));
    if (empty($taskIds)) {
        return ['success' => false, 'message' => 'Select at least one task.'];
    }

    $placeholders = implode(',', array_fill(0, count($taskIds), '?'));
    $types = str_repeat('i', count($taskIds));
    $params = $taskIds;

    if ($bulkAction === 'complete') {
        $stmt = safePrepare($mysqli, "UPDATE tasks SET status = 'Completed', completed = 1, completed_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP WHERE id IN ($placeholders) AND user_id = ? AND is_deleted = 0");
        $stmt->bind_param($types . 'i', ...array_merge($params, [$user_id]));
        $stmt->execute();
        return ['success' => true, 'message' => 'Selected tasks completed.'];
    }

    if ($bulkAction === 'delete') {
        $stmt = safePrepare($mysqli, "UPDATE tasks SET is_deleted = 1, updated_at = CURRENT_TIMESTAMP WHERE id IN ($placeholders) AND user_id = ?");
        $stmt->bind_param($types . 'i', ...array_merge($params, [$user_id]));
        $stmt->execute();
        return ['success' => true, 'message' => 'Selected tasks deleted.'];
    }

    if ($bulkAction === 'category') {
        $categoryId = !empty($post['category_id']) ? (int) $post['category_id'] : null;
        $stmt = safePrepare($mysqli, "UPDATE tasks SET category_id = ? WHERE id IN ($placeholders) AND user_id = ? AND is_deleted = 0");
        $stmt->bind_param('i' . $types . 'i', $categoryId, ...array_merge($params, [$user_id]));
        $stmt->execute();
        return ['success' => true, 'message' => 'Selected tasks updated.'];
    }

    return ['success' => false, 'message' => 'Unsupported bulk action.'];
}

function saveCategoryHandler($mysqli, $user_id, $post) {
    ensureTaskTablesExist($mysqli);
    $name = trim($post['name'] ?? '');
    if ($name === '') {
        return ['success' => false, 'message' => 'Category name is required.'];
    }

    $categoryId = !empty($post['category_id']) ? (int) $post['category_id'] : 0;
    $color = $post['color'] ?? '#6366f1';
    $icon = $post['icon'] ?? 'task';

    if ($categoryId > 0) {
        $stmt = safePrepare($mysqli, 'UPDATE task_categories SET name = ?, color = ?, icon = ? WHERE id = ? AND user_id = ? AND is_deleted = 0');
        $stmt->bind_param('sssii', $name, $color, $icon, $categoryId, $user_id);
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Category updated.'];
        }
        return ['success' => false, 'message' => 'Unable to update category.'];
    }

    $stmt = safePrepare($mysqli, 'INSERT INTO task_categories (user_id, name, color, icon) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('isss', $user_id, $name, $color, $icon);
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Category created.'];
    }
    return ['success' => false, 'message' => 'Unable to create category.'];
}

function getCategoryHandler($mysqli, $user_id, $post) {
    ensureTaskTablesExist($mysqli);
    $categoryId = !empty($post['category_id']) ? (int) $post['category_id'] : 0;
    $stmt = safePrepare($mysqli, 'SELECT id, name, color, icon FROM task_categories WHERE id = ? AND user_id = ? AND is_deleted = 0');
    $stmt->bind_param('ii', $categoryId, $user_id);
    $stmt->execute();
    $category = $stmt->get_result()->fetch_assoc();

    if ($category) {
        return ['success' => true, 'category' => $category];
    }

    return ['success' => false, 'message' => 'Category not found.'];
}

function getCategoriesHandler($mysqli, $user_id, $post) {
    return ['success' => true, 'categories' => getTaskCategories($mysqli, $user_id)];
}

function deleteCategoryHandler($mysqli, $user_id, $post) {
    ensureTaskTablesExist($mysqli);
    $categoryId = !empty($post['category_id']) ? (int) $post['category_id'] : 0;
    if ($categoryId === 0) {
        $categoryId = !empty($post['task_id']) ? (int) $post['task_id'] : 0;
    }
    $stmt = safePrepare($mysqli, 'UPDATE task_categories SET is_deleted = 1 WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $categoryId, $user_id);
    if ($stmt->execute()) {
        $updateTasks = safePrepare($mysqli, 'UPDATE tasks SET category_id = NULL WHERE category_id = ? AND user_id = ?');
        $updateTasks->bind_param('ii', $categoryId, $user_id);
        $updateTasks->execute();
        return ['success' => true, 'message' => 'Category deleted.'];
    }
    return ['success' => false, 'message' => 'Unable to delete category.'];
}

/* â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
   Phase 4 â€” Smart Notes
   â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

function ensureNoteTablesExist($mysqli) {
    $statements = [
        "CREATE TABLE IF NOT EXISTS note_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            color VARCHAR(20) DEFAULT '#6366f1',
            is_deleted TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_note_category_user (user_id, name),
            INDEX idx_nc_user_id (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS notes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            content TEXT DEFAULT NULL,
            category_id INT DEFAULT NULL,
            is_pinned TINYINT(1) DEFAULT 0,
            is_archived TINYINT(1) DEFAULT 0,
            is_deleted TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES note_categories(id) ON DELETE SET NULL,
            INDEX idx_notes_user (user_id, is_deleted),
            INDEX idx_notes_pinned (user_id, is_pinned),
            INDEX idx_notes_category (category_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS note_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            note_id INT NOT NULL,
            user_id INT NOT NULL,
            filename VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            file_size INT DEFAULT 0,
            mime_type VARCHAR(100) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_note_images_note (note_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    foreach ($statements as $statement) {
        if (!$mysqli->query($statement)) {
            logError("Note table creation failed: " . $mysqli->error . " | Query: " . substr($statement, 0, 200), 'SQL_ERROR');
        }
    }
}

function logNoteActivity($mysqli, $user_id, $note_id, $action, $description) {
    ensureNoteTablesExist($mysqli);
    if (tableExists($mysqli, 'activity_logs')) {
        $stmt = safePrepare($mysqli, "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description, ip_address, user_agent) VALUES (?, ?, 'note', ?, ?, ?, ?)");
        if ($stmt) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $stmt->bind_param('isisss', $user_id, $action, $note_id, $description, $ip, $ua);
            $stmt->execute();
        }
    }
}

function getNoteCategories($mysqli, $user_id) {
    ensureNoteTablesExist($mysqli);
    $stmt = safePrepare($mysqli, "SELECT id, name, color FROM note_categories WHERE user_id = ? AND is_deleted = 0 ORDER BY name ASC");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    return $categories;
}

function getNoteStats($mysqli, $user_id) {
    ensureNoteTablesExist($mysqli);
    $stmt = safePrepare($mysqli, "SELECT COUNT(*) AS total, SUM(CASE WHEN is_pinned = 1 THEN 1 ELSE 0 END) AS pinned, SUM(CASE WHEN is_archived = 1 THEN 1 ELSE 0 END) AS archived, SUM(CASE WHEN is_deleted = 1 THEN 1 ELSE 0 END) AS deleted FROM notes WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return [
        'total' => (int) ($row['total'] ?? 0),
        'pinned' => (int) ($row['pinned'] ?? 0),
        'archived' => (int) ($row['archived'] ?? 0),
        'deleted' => (int) ($row['deleted'] ?? 0)
    ];
}

function getNotesForList($mysqli, $user_id, $filters) {
    ensureNoteTablesExist($mysqli);

    $conditions = ['n.user_id = ?'];
    $params = [$user_id];
    $types = 'i';

    $show_deleted = !empty($filters['show_deleted']);
    $show_archived = !empty($filters['show_archived']);

    if ($show_deleted) {
        $conditions[] = 'n.is_deleted = 1';
    } else {
        $conditions[] = 'n.is_deleted = 0';
        if (!$show_archived) {
            $conditions[] = 'n.is_archived = 0';
        }
    }

    if (!empty($filters['search'])) {
        $search = '%' . $filters['search'] . '%';
        $conditions[] = '(n.title LIKE ? OR n.content LIKE ?)';
        $params[] = $search;
        $params[] = $search;
        $types .= 'ss';
    }

    if (!empty($filters['category'])) {
        $conditions[] = 'n.category_id = ?';
        $params[] = (int) $filters['category'];
        $types .= 'i';
    }

    $whereSql = implode(' AND ', $conditions);

    $countStmt = safePrepare($mysqli, "SELECT COUNT(*) AS total FROM notes n WHERE $whereSql");
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $total = (int) ($countStmt->get_result()->fetch_assoc()['total'] ?? 0);

    $perPage = 12;
    $page = max(1, (int) ($filters['page'] ?? 1));
    $offset = ($page - 1) * $perPage;
    $totalPages = max(1, (int) ceil($total / $perPage));

    $stmt = safePrepare($mysqli, "SELECT n.*, nc.name AS category_name, nc.color AS category_color FROM notes n LEFT JOIN note_categories nc ON nc.id = n.category_id WHERE $whereSql ORDER BY n.is_pinned DESC, n.updated_at DESC LIMIT ? OFFSET ?");
    $bindTypes = $types . 'ii';
    $bindParams = array_merge($params, [$perPage, $offset]);
    $stmt->bind_param($bindTypes, ...$bindParams);
    $stmt->execute();
    $result = $stmt->get_result();

    $notes = [];
    while ($row = $result->fetch_assoc()) {
        $notes[] = $row;
    }

    return [
        'notes' => $notes,
        'total' => $total,
        'total_pages' => $totalPages,
        'page' => $page,
        'per_page' => $perPage
    ];
}

function buildNotePaginationUrl($filters, $page) {
    $params = [];
    foreach ($filters as $key => $value) {
        if ($value === '' || $value === null || $value === false || $value === 0) {
            continue;
        }
        if ($key === 'page') continue;
        $params[] = urlencode($key) . '=' . urlencode((string) $value);
    }
    $params[] = 'page=' . (int) $page;
    return '?' . implode('&', $params);
}

function saveNoteHandler($mysqli, $user_id, $post) {
    ensureNoteTablesExist($mysqli);
    $title = trim($post['title'] ?? '');
    if ($title === '') {
        return ['success' => false, 'message' => 'Note title is required.'];
    }

    $noteId = !empty($post['note_id']) ? (int) $post['note_id'] : 0;
    $content = $post['content'] ?? '';
    $categoryId = !empty($post['category_id']) ? (int) $post['category_id'] : null;
    $isPinned = !empty($post['is_pinned']) ? 1 : 0;
    $isArchived = !empty($post['is_archived']) ? 1 : 0;

    if ($categoryId !== null) {
        $catStmt = safePrepare($mysqli, 'SELECT id FROM note_categories WHERE id = ? AND user_id = ? AND is_deleted = 0');
        $catStmt->bind_param('ii', $categoryId, $user_id);
        $catStmt->execute();
        if ($catStmt->get_result()->num_rows === 0) {
            $categoryId = null;
        }
    }

    if ($noteId > 0) {
        $stmt = safePrepare($mysqli, "UPDATE notes SET title = ?, content = ?, category_id = ?, is_pinned = ?, is_archived = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ? AND is_deleted = 0");
        $stmt->bind_param('ssiiiii', $title, $content, $categoryId, $isPinned, $isArchived, $noteId, $user_id);
        if ($stmt->execute()) {
            logNoteActivity($mysqli, $user_id, $noteId, 'note_updated', 'Note updated: ' . $title);
            return ['success' => true, 'message' => 'Note updated successfully.'];
        }
        return ['success' => false, 'message' => 'Unable to update note.'];
    }

    $stmt = safePrepare($mysqli, "INSERT INTO notes (user_id, title, content, category_id, is_pinned, is_archived) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('issiii', $user_id, $title, $content, $categoryId, $isPinned, $isArchived);
    if ($stmt->execute()) {
        $newId = $stmt->insert_id;
        logNoteActivity($mysqli, $user_id, $newId, 'note_created', 'Note created: ' . $title);
        return ['success' => true, 'message' => 'Note created successfully.', 'note_id' => $newId];
    }
    return ['success' => false, 'message' => 'Unable to create note.'];
}

function getNoteHandler($mysqli, $user_id, $post) {
    ensureNoteTablesExist($mysqli);
    $noteId = !empty($post['note_id']) ? (int) $post['note_id'] : 0;
    $stmt = safePrepare($mysqli, 'SELECT n.*, nc.name AS category_name, nc.color AS category_color FROM notes n LEFT JOIN note_categories nc ON nc.id = n.category_id WHERE n.id = ? AND n.user_id = ? AND n.is_deleted = 0');
    $stmt->bind_param('ii', $noteId, $user_id);
    $stmt->execute();
    $note = $stmt->get_result()->fetch_assoc();

    if ($note) {
        $imgStmt = safePrepare($mysqli, 'SELECT * FROM note_images WHERE note_id = ? AND user_id = ? ORDER BY created_at ASC');
        $imgStmt->bind_param('ii', $noteId, $user_id);
        $imgStmt->execute();
        $imgResult = $imgStmt->get_result();
        $images = [];
        while ($img = $imgResult->fetch_assoc()) {
            $images[] = $img;
        }
        $note['images'] = $images;
        return ['success' => true, 'note' => $note];
    }
    return ['success' => false, 'message' => 'Note not found.'];
}

function deleteNoteHandler($mysqli, $user_id, $post) {
    ensureNoteTablesExist($mysqli);
    $noteId = !empty($post['note_id']) ? (int) $post['note_id'] : 0;
    $stmt = safePrepare($mysqli, 'UPDATE notes SET is_deleted = 1, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $noteId, $user_id);
    if ($stmt->execute()) {
        logNoteActivity($mysqli, $user_id, $noteId, 'note_deleted', 'Note moved to trash');
        return ['success' => true, 'message' => 'Note moved to trash.'];
    }
    return ['success' => false, 'message' => 'Unable to delete note.'];
}

function restoreNoteHandler($mysqli, $user_id, $post) {
    ensureNoteTablesExist($mysqli);
    $noteId = !empty($post['note_id']) ? (int) $post['note_id'] : 0;
    $stmt = safePrepare($mysqli, 'UPDATE notes SET is_deleted = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $noteId, $user_id);
    if ($stmt->execute()) {
        logNoteActivity($mysqli, $user_id, $noteId, 'note_restored', 'Note restored');
        return ['success' => true, 'message' => 'Note restored.'];
    }
    return ['success' => false, 'message' => 'Unable to restore note.'];
}

function permanentDeleteNoteHandler($mysqli, $user_id, $post) {
    ensureNoteTablesExist($mysqli);
    $noteId = !empty($post['note_id']) ? (int) $post['note_id'] : 0;

    $imgStmt = safePrepare($mysqli, 'SELECT filename FROM note_images WHERE note_id = ? AND user_id = ?');
    $imgStmt->bind_param('ii', $noteId, $user_id);
    $imgStmt->execute();
    $imgResult = $imgStmt->get_result();
    while ($img = $imgResult->fetch_assoc()) {
        $filepath = UPLOAD_DIR . 'notes/' . $img['filename'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    $delImg = safePrepare($mysqli, 'DELETE FROM note_images WHERE note_id = ? AND user_id = ?');
    $delImg->bind_param('ii', $noteId, $user_id);
    $delImg->execute();

    $stmt = safePrepare($mysqli, 'DELETE FROM notes WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $noteId, $user_id);
    if ($stmt->execute()) {
        logNoteActivity($mysqli, $user_id, $noteId, 'note_permanently_deleted', 'Note permanently deleted');
        return ['success' => true, 'message' => 'Note permanently deleted.'];
    }
    return ['success' => false, 'message' => 'Unable to permanently delete note.'];
}

function toggleNotePinHandler($mysqli, $user_id, $post) {
    ensureNoteTablesExist($mysqli);
    $noteId = !empty($post['note_id']) ? (int) $post['note_id'] : 0;
    $stmt = safePrepare($mysqli, 'UPDATE notes SET is_pinned = IF(is_pinned = 1, 0, 1), updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ? AND is_deleted = 0');
    $stmt->bind_param('ii', $noteId, $user_id);
    if ($stmt->execute()) {
        $check = safePrepare($mysqli, 'SELECT is_pinned FROM notes WHERE id = ? AND user_id = ?');
        $check->bind_param('ii', $noteId, $user_id);
        $check->execute();
        $row = $check->get_result()->fetch_assoc();
        $pinned = $row ? (int) $row['is_pinned'] : 0;
        return ['success' => true, 'message' => $pinned ? 'Note pinned.' : 'Note unpinned.', 'is_pinned' => $pinned];
    }
    return ['success' => false, 'message' => 'Unable to update pin status.'];
}

function toggleNoteArchiveHandler($mysqli, $user_id, $post) {
    ensureNoteTablesExist($mysqli);
    $noteId = !empty($post['note_id']) ? (int) $post['note_id'] : 0;
    $stmt = safePrepare($mysqli, 'UPDATE notes SET is_archived = IF(is_archived = 1, 0, 1), updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ? AND is_deleted = 0');
    $stmt->bind_param('ii', $noteId, $user_id);
    if ($stmt->execute()) {
        $check = safePrepare($mysqli, 'SELECT is_archived FROM notes WHERE id = ? AND user_id = ?');
        $check->bind_param('ii', $noteId, $user_id);
        $check->execute();
        $row = $check->get_result()->fetch_assoc();
        $archived = $row ? (int) $row['is_archived'] : 0;
        return ['success' => true, 'message' => $archived ? 'Note archived.' : 'Note unarchived.', 'is_archived' => $archived];
    }
    return ['success' => false, 'message' => 'Unable to update archive status.'];
}

function duplicateNoteHandler($mysqli, $user_id, $post) {
    ensureNoteTablesExist($mysqli);
    $noteId = !empty($post['note_id']) ? (int) $post['note_id'] : 0;
    $stmt = safePrepare($mysqli, 'SELECT * FROM notes WHERE id = ? AND user_id = ? AND is_deleted = 0');
    $stmt->bind_param('ii', $noteId, $user_id);
    $stmt->execute();
    $note = $stmt->get_result()->fetch_assoc();

    if (!$note) {
        return ['success' => false, 'message' => 'Note not found.'];
    }

    $dupTitle = $note['title'] . ' (Copy)';
    $ins = safePrepare($mysqli, 'INSERT INTO notes (user_id, title, content, category_id, is_pinned, is_archived) VALUES (?, ?, ?, ?, 0, 0)');
    $ins->bind_param('issi', $user_id, $dupTitle, $note['content'], $note['category_id']);
    if ($ins->execute()) {
        logNoteActivity($mysqli, $user_id, $ins->insert_id, 'note_duplicated', 'Note duplicated');
        return ['success' => true, 'message' => 'Note duplicated.'];
    }
    return ['success' => false, 'message' => 'Unable to duplicate note.'];
}

function bulkNoteActionHandler($mysqli, $user_id, $post) {
    ensureNoteTablesExist($mysqli);
    $bulkAction = $post['bulk_action'] ?? '';
    $noteIds = array_filter(array_map('intval', explode(',', $post['note_ids'] ?? '')));
    if (empty($noteIds)) {
        return ['success' => false, 'message' => 'Select at least one note.'];
    }

    $placeholders = implode(',', array_fill(0, count($noteIds), '?'));
    $types = str_repeat('i', count($noteIds));
    $params = $noteIds;

    if ($bulkAction === 'delete') {
        $stmt = safePrepare($mysqli, "UPDATE notes SET is_deleted = 1, updated_at = CURRENT_TIMESTAMP WHERE id IN ($placeholders) AND user_id = ?");
        $stmt->bind_param($types . 'i', ...array_merge($params, [$user_id]));
        $stmt->execute();
        return ['success' => true, 'message' => 'Selected notes deleted.'];
    }

    if ($bulkAction === 'archive') {
        $stmt = safePrepare($mysqli, "UPDATE notes SET is_archived = 1, updated_at = CURRENT_TIMESTAMP WHERE id IN ($placeholders) AND user_id = ?");
        $stmt->bind_param($types . 'i', ...array_merge($params, [$user_id]));
        $stmt->execute();
        return ['success' => true, 'message' => 'Selected notes archived.'];
    }

    if ($bulkAction === 'category') {
        $categoryId = !empty($post['category_id']) ? (int) $post['category_id'] : null;
        $stmt = safePrepare($mysqli, "UPDATE notes SET category_id = ? WHERE id IN ($placeholders) AND user_id = ?");
        $stmt->bind_param('i' . $types . 'i', $categoryId, ...array_merge($params, [$user_id]));
        $stmt->execute();
        return ['success' => true, 'message' => 'Selected notes updated.'];
    }

    return ['success' => false, 'message' => 'Unsupported bulk action.'];
}

function saveNoteCategoryHandler($mysqli, $user_id, $post) {
    ensureNoteTablesExist($mysqli);
    $name = trim($post['name'] ?? '');
    if ($name === '') {
        return ['success' => false, 'message' => 'Category name is required.'];
    }

    $categoryId = !empty($post['category_id']) ? (int) $post['category_id'] : 0;
    $color = $post['color'] ?? '#6366f1';

    if ($categoryId > 0) {
        $stmt = safePrepare($mysqli, 'UPDATE note_categories SET name = ?, color = ? WHERE id = ? AND user_id = ? AND is_deleted = 0');
        $stmt->bind_param('ssii', $name, $color, $categoryId, $user_id);
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Category updated.'];
        }
        return ['success' => false, 'message' => 'Unable to update category.'];
    }

    $stmt = safePrepare($mysqli, 'INSERT INTO note_categories (user_id, name, color) VALUES (?, ?, ?)');
    $stmt->bind_param('iss', $user_id, $name, $color);
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Category created.'];
    }
    return ['success' => false, 'message' => 'Unable to create category.'];
}

function deleteNoteCategoryHandler($mysqli, $user_id, $post) {
    ensureNoteTablesExist($mysqli);
    $categoryId = !empty($post['category_id']) ? (int) $post['category_id'] : 0;
    $stmt = safePrepare($mysqli, 'UPDATE note_categories SET is_deleted = 1 WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $categoryId, $user_id);
    if ($stmt->execute()) {
        $update = safePrepare($mysqli, 'UPDATE notes SET category_id = NULL WHERE category_id = ? AND user_id = ?');
        $update->bind_param('ii', $categoryId, $user_id);
        $update->execute();
        return ['success' => true, 'message' => 'Category deleted.'];
    }
    return ['success' => false, 'message' => 'Unable to delete category.'];
}

function getNoteCategoriesHandler($mysqli, $user_id, $post) {
    return ['success' => true, 'categories' => getNoteCategories($mysqli, $user_id)];
}

function uploadNoteImageHandler($mysqli, $user_id, $post) {
    ensureNoteTablesExist($mysqli);
    $noteId = !empty($post['note_id']) ? (int) $post['note_id'] : 0;
    if ($noteId === 0) {
        return ['success' => false, 'message' => 'Note ID required for image upload.'];
    }

    $verify = safePrepare($mysqli, 'SELECT id FROM notes WHERE id = ? AND user_id = ? AND is_deleted = 0');
    $verify->bind_param('ii', $noteId, $user_id);
    $verify->execute();
    if ($verify->get_result()->num_rows === 0) {
        return ['success' => false, 'message' => 'Note not found.'];
    }

    if (!isset($_FILES['note_image']) || $_FILES['note_image']['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error.'];
    }

    $file = $_FILES['note_image'];
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 5 * 1024 * 1024;

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return ['success' => false, 'message' => 'File type not allowed. Use: ' . implode(', ', $allowed)];
    }
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File too large. Max 5MB.'];
    }

    $uploadDir = UPLOAD_DIR . 'notes/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $filepath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'message' => 'Failed to save uploaded file.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filepath);
    finfo_close($finfo);

    $stmt = safePrepare($mysqli, 'INSERT INTO note_images (note_id, user_id, filename, original_name, file_size, mime_type) VALUES (?, ?, ?, ?, ?, ?)');
    $originalName = $file['name'];
    $fileSize = $file['size'];
    $stmt->bind_param('iiisss', $noteId, $user_id, $filename, $originalName, $fileSize, $mimeType);
    if ($stmt->execute()) {
        $url = UPLOAD_URL . 'notes/' . $filename;
        return ['success' => true, 'message' => 'Image uploaded.', 'image' => ['id' => $stmt->insert_id, 'filename' => $filename, 'url' => $url, 'original_name' => $originalName]];
    }
    return ['success' => false, 'message' => 'Failed to record image.'];
}

function deleteNoteImageHandler($mysqli, $user_id, $post) {
    ensureNoteTablesExist($mysqli);
    $imageId = !empty($post['image_id']) ? (int) $post['image_id'] : 0;

    $stmt = safePrepare($mysqli, 'SELECT filename FROM note_images WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $imageId, $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) {
        return ['success' => false, 'message' => 'Image not found.'];
    }

    $filepath = UPLOAD_DIR . 'notes/' . $row['filename'];
    if (file_exists($filepath)) {
        unlink($filepath);
    }

    $del = safePrepare($mysqli, 'DELETE FROM note_images WHERE id = ? AND user_id = ?');
    $del->bind_param('ii', $imageId, $user_id);
    if ($del->execute()) {
        return ['success' => true, 'message' => 'Image deleted.'];
    }
    return ['success' => false, 'message' => 'Unable to delete image.'];
}

/* â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
   Phase 5 â€” Expense Manager
   â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

function ensureExpenseTablesExist($mysqli) {
    $statements = [
        "CREATE TABLE IF NOT EXISTS expense_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            color VARCHAR(20) DEFAULT '#6366f1',
            type ENUM('expense', 'income') DEFAULT 'expense',
            is_deleted TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_exp_cat_user (user_id, name, type),
            INDEX idx_ec_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS expenses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            type ENUM('expense', 'income') DEFAULT 'expense',
            category_id INT DEFAULT NULL,
            transaction_date DATE NOT NULL,
            notes TEXT DEFAULT NULL,
            is_recurring TINYINT(1) DEFAULT 0,
            recurring_period VARCHAR(20) DEFAULT NULL,
            is_deleted TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES expense_categories(id) ON DELETE SET NULL,
            INDEX idx_exp_user_date (user_id, transaction_date),
            INDEX idx_exp_user_type (user_id, type),
            INDEX idx_exp_user_deleted (user_id, is_deleted)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS budgets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            category_id INT DEFAULT NULL,
            amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            period ENUM('monthly', 'weekly', 'yearly') DEFAULT 'monthly',
            start_date DATE NOT NULL,
            end_date DATE DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES expense_categories(id) ON DELETE SET NULL,
            INDEX idx_budget_user (user_id, is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    foreach ($statements as $statement) {
        if (!$mysqli->query($statement)) {
            logError("Expense table creation failed: " . $mysqli->error . " | Query: " . substr($statement, 0, 200), 'SQL_ERROR');
        }
    }
}

function logExpenseActivity($mysqli, $user_id, $entity_id, $action, $description) {
    if (tableExists($mysqli, 'activity_logs')) {
        $stmt = safePrepare($mysqli, "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description, ip_address, user_agent) VALUES (?, ?, 'expense', ?, ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $stmt->bind_param('isisss', $user_id, $action, $entity_id, $description, $ip, $ua);
        $stmt->execute();
    }
}

function getExpenseCategories($mysqli, $user_id, $type = '') {
    ensureExpenseTablesExist($mysqli);
    if ($type) {
        $stmt = safePrepare($mysqli, "SELECT id, name, color, type FROM expense_categories WHERE user_id = ? AND is_deleted = 0 AND type = ? ORDER BY name ASC");
        $stmt->bind_param('is', $user_id, $type);
    } else {
        $stmt = safePrepare($mysqli, "SELECT id, name, color, type FROM expense_categories WHERE user_id = ? AND is_deleted = 0 ORDER BY name ASC");
        $stmt->bind_param('i', $user_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    return $categories;
}

function getExpenseStats($mysqli, $user_id) {
    ensureExpenseTablesExist($mysqli);
    $stmt = safePrepare($mysqli, "SELECT type, COUNT(*) AS count, COALESCE(SUM(amount), 0) AS total FROM expenses WHERE user_id = ? AND is_deleted = 0 AND MONTH(transaction_date) = MONTH(CURDATE()) AND YEAR(transaction_date) = YEAR(CURDATE()) GROUP BY type");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $stats = ['total_income' => 0, 'total_expense' => 0, 'balance' => 0, 'income_count' => 0, 'expense_count' => 0];
    while ($row = $result->fetch_assoc()) {
        if ($row['type'] === 'income') {
            $stats['total_income'] = (float) $row['total'];
            $stats['income_count'] = (int) $row['count'];
        } else {
            $stats['total_expense'] = (float) $row['total'];
            $stats['expense_count'] = (int) $row['count'];
        }
    }
    $stats['balance'] = $stats['total_income'] - $stats['total_expense'];
    return $stats;
}

function getExpensesForList($mysqli, $user_id, $filters) {
    ensureExpenseTablesExist($mysqli);

    $conditions = ['e.user_id = ?', 'e.is_deleted = 0'];
    $params = [$user_id];
    $types = 'i';

    if (!empty($filters['search'])) {
        $search = '%' . $filters['search'] . '%';
        $conditions[] = '(e.title LIKE ? OR e.notes LIKE ?)';
        $params[] = $search;
        $params[] = $search;
        $types .= 'ss';
    }
    if (!empty($filters['type'])) {
        $conditions[] = 'e.type = ?';
        $params[] = $filters['type'];
        $types .= 's';
    }
    if (!empty($filters['category'])) {
        $conditions[] = 'e.category_id = ?';
        $params[] = (int) $filters['category'];
        $types .= 'i';
    }
    if (!empty($filters['date_from'])) {
        $conditions[] = 'e.transaction_date >= ?';
        $params[] = $filters['date_from'];
        $types .= 's';
    }
    if (!empty($filters['date_to'])) {
        $conditions[] = 'e.transaction_date <= ?';
        $params[] = $filters['date_to'];
        $types .= 's';
    }

    $whereSql = implode(' AND ', $conditions);

    $countStmt = safePrepare($mysqli, "SELECT COUNT(*) AS total FROM expenses e WHERE $whereSql");
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $total = (int) ($countStmt->get_result()->fetch_assoc()['total'] ?? 0);

    $perPage = 15;
    $page = max(1, (int) ($filters['page'] ?? 1));
    $offset = ($page - 1) * $perPage;
    $totalPages = max(1, (int) ceil($total / $perPage));

    $stmt = safePrepare($mysqli, "SELECT e.*, ec.name AS category_name, ec.color AS category_color FROM expenses e LEFT JOIN expense_categories ec ON ec.id = e.category_id WHERE $whereSql ORDER BY e.transaction_date DESC, e.created_at DESC LIMIT ? OFFSET ?");
    $bindTypes = $types . 'ii';
    $bindParams = array_merge($params, [$perPage, $offset]);
    $stmt->bind_param($bindTypes, ...$bindParams);
    $stmt->execute();
    $result = $stmt->get_result();

    $expenses = [];
    while ($row = $result->fetch_assoc()) {
        $expenses[] = $row;
    }

    return [
        'expenses' => $expenses,
        'total' => $total,
        'total_pages' => $totalPages,
        'page' => $page,
        'per_page' => $perPage
    ];
}

function buildExpensePaginationUrl($filters, $page) {
    $params = [];
    foreach ($filters as $key => $value) {
        if ($value === '' || $value === null || $value === false || $value === 0) continue;
        if ($key === 'page') continue;
        $params[] = urlencode($key) . '=' . urlencode((string) $value);
    }
    $params[] = 'page=' . (int) $page;
    return '?' . implode('&', $params);
}

function saveExpenseHandler($mysqli, $user_id, $post) {
    ensureExpenseTablesExist($mysqli);
    $title = trim($post['title'] ?? '');
    if ($title === '') {
        return ['success' => false, 'message' => 'Title is required.'];
    }
    $amount = filter_var($post['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
    if ($amount === false || $amount <= 0) {
        return ['success' => false, 'message' => 'Valid amount is required.'];
    }

    $expenseId = !empty($post['expense_id']) ? (int) $post['expense_id'] : 0;
    $type = ($post['type'] ?? 'expense') === 'income' ? 'income' : 'expense';
    $categoryId = !empty($post['category_id']) ? (int) $post['category_id'] : null;
    $transactionDate = !empty($post['transaction_date']) ? $post['transaction_date'] : date('Y-m-d');
    $notes = trim($post['notes'] ?? '');
    $isRecurring = !empty($post['is_recurring']) ? 1 : 0;
    $recurringPeriod = $post['recurring_period'] ?? null;

    if ($categoryId !== null) {
        $catStmt = safePrepare($mysqli, 'SELECT id FROM expense_categories WHERE id = ? AND user_id = ? AND is_deleted = 0');
        $catStmt->bind_param('ii', $categoryId, $user_id);
        $catStmt->execute();
        if ($catStmt->get_result()->num_rows === 0) {
            $categoryId = null;
        }
    }

    if ($expenseId > 0) {
        $stmt = safePrepare($mysqli, "UPDATE expenses SET title = ?, amount = ?, type = ?, category_id = ?, transaction_date = ?, notes = ?, is_recurring = ?, recurring_period = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ? AND is_deleted = 0");
        $stmt->bind_param('sdsissisii', $title, $amount, $type, $categoryId, $transactionDate, $notes, $isRecurring, $recurringPeriod, $expenseId, $user_id);
        if ($stmt->execute()) {
            logExpenseActivity($mysqli, $user_id, $expenseId, 'expense_updated', 'Transaction updated: ' . $title);
            return ['success' => true, 'message' => 'Transaction updated.'];
        }
        return ['success' => false, 'message' => 'Unable to update transaction.'];
    }

    $stmt = safePrepare($mysqli, "INSERT INTO expenses (user_id, title, amount, type, category_id, transaction_date, notes, is_recurring, recurring_period) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('isdsissis', $user_id, $title, $amount, $type, $categoryId, $transactionDate, $notes, $isRecurring, $recurringPeriod);
    if ($stmt->execute()) {
        $newId = $stmt->insert_id;
        logExpenseActivity($mysqli, $user_id, $newId, 'expense_created', 'Transaction created: ' . $title);
        return ['success' => true, 'message' => 'Transaction created.', 'expense_id' => $newId];
    }
    return ['success' => false, 'message' => 'Unable to create transaction.'];
}

function getExpenseHandler($mysqli, $user_id, $post) {
    ensureExpenseTablesExist($mysqli);
    $expenseId = !empty($post['expense_id']) ? (int) $post['expense_id'] : 0;
    $stmt = safePrepare($mysqli, 'SELECT e.*, ec.name AS category_name, ec.color AS category_color FROM expenses e LEFT JOIN expense_categories ec ON ec.id = e.category_id WHERE e.id = ? AND e.user_id = ? AND e.is_deleted = 0');
    $stmt->bind_param('ii', $expenseId, $user_id);
    $stmt->execute();
    $expense = $stmt->get_result()->fetch_assoc();
    if ($expense) {
        return ['success' => true, 'expense' => $expense];
    }
    return ['success' => false, 'message' => 'Transaction not found.'];
}

function deleteExpenseHandler($mysqli, $user_id, $post) {
    ensureExpenseTablesExist($mysqli);
    $expenseId = !empty($post['expense_id']) ? (int) $post['expense_id'] : 0;
    $stmt = safePrepare($mysqli, 'UPDATE expenses SET is_deleted = 1, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $expenseId, $user_id);
    if ($stmt->execute()) {
        logExpenseActivity($mysqli, $user_id, $expenseId, 'expense_deleted', 'Transaction deleted');
        return ['success' => true, 'message' => 'Transaction deleted.'];
    }
    return ['success' => false, 'message' => 'Unable to delete transaction.'];
}

function saveExpenseCategoryHandler($mysqli, $user_id, $post) {
    ensureExpenseTablesExist($mysqli);
    $name = trim($post['name'] ?? '');
    if ($name === '') {
        return ['success' => false, 'message' => 'Category name is required.'];
    }
    $categoryId = !empty($post['category_id']) ? (int) $post['category_id'] : 0;
    $color = $post['color'] ?? '#6366f1';
    $type = ($post['type'] ?? 'expense') === 'income' ? 'income' : 'expense';

    if ($categoryId > 0) {
        $stmt = safePrepare($mysqli, 'UPDATE expense_categories SET name = ?, color = ?, type = ? WHERE id = ? AND user_id = ? AND is_deleted = 0');
        $stmt->bind_param('sssii', $name, $color, $type, $categoryId, $user_id);
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Category updated.'];
        }
        return ['success' => false, 'message' => 'Unable to update category.'];
    }

    $stmt = safePrepare($mysqli, 'INSERT INTO expense_categories (user_id, name, color, type) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('isss', $user_id, $name, $color, $type);
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Category created.'];
    }
    return ['success' => false, 'message' => 'Unable to create category.'];
}

function deleteExpenseCategoryHandler($mysqli, $user_id, $post) {
    ensureExpenseTablesExist($mysqli);
    $categoryId = !empty($post['category_id']) ? (int) $post['category_id'] : 0;
    $stmt = safePrepare($mysqli, 'UPDATE expense_categories SET is_deleted = 1 WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $categoryId, $user_id);
    if ($stmt->execute()) {
        $update = safePrepare($mysqli, 'UPDATE expenses SET category_id = NULL WHERE category_id = ? AND user_id = ?');
        $update->bind_param('ii', $categoryId, $user_id);
        $update->execute();
        return ['success' => true, 'message' => 'Category deleted.'];
    }
    return ['success' => false, 'message' => 'Unable to delete category.'];
}

function getExpenseCategoriesHandler($mysqli, $user_id, $post) {
    $type = $post['type'] ?? '';
    return ['success' => true, 'categories' => getExpenseCategories($mysqli, $user_id, $type)];
}

function getExpenseChartDataHandler($mysqli, $user_id, $post) {
    ensureExpenseTablesExist($mysqli);
    $months = (int) ($post['months'] ?? 6);
    $labels = [];
    $income = [];
    $expense = [];

    $stmt = safePrepare($mysqli, "SELECT DATE_FORMAT(transaction_date, '%b %Y') AS month_label, type, SUM(amount) AS total FROM expenses WHERE user_id = ? AND is_deleted = 0 AND transaction_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH) GROUP BY YEAR(transaction_date), MONTH(transaction_date), type ORDER BY MIN(transaction_date) ASC");
    $stmt->bind_param('ii', $user_id, $months);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $label = $row['month_label'];
        if (!isset($data[$label])) {
            $data[$label] = ['income' => 0, 'expense' => 0];
        }
        $data[$label][$row['type']] = (float) $row['total'];
    }

    if (empty($data)) {
        for ($i = $months - 1; $i >= 0; $i--) {
            $label = date('M Y', strtotime("-$i months"));
            $labels[] = $label;
            $income[] = 0;
            $expense[] = 0;
        }
    } else {
        foreach ($data as $label => $vals) {
            $labels[] = $label;
            $income[] = $vals['income'];
            $expense[] = $vals['expense'];
        }
    }

    return ['success' => true, 'chart' => ['labels' => $labels, 'income' => $income, 'expense' => $expense]];
}

function getExpenseCategoryBreakdownHandler($mysqli, $user_id, $post) {
    ensureExpenseTablesExist($mysqli);
    $month = $post['month'] ?? date('m');
    $year = $post['year'] ?? date('Y');
    $type = ($post['type'] ?? 'expense') === 'income' ? 'income' : 'expense';

    $stmt = safePrepare($mysqli, "SELECT ec.name, ec.color, COALESCE(SUM(e.amount), 0) AS total FROM expenses e LEFT JOIN expense_categories ec ON ec.id = e.category_id WHERE e.user_id = ? AND e.is_deleted = 0 AND e.type = ? AND MONTH(e.transaction_date) = ? AND YEAR(e.transaction_date) = ? GROUP BY e.category_id ORDER BY total DESC");
    $stmt->bind_param('issi', $user_id, $type, $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    return ['success' => true, 'categories' => $categories];
}

function saveBudgetHandler($mysqli, $user_id, $post) {
    ensureExpenseTablesExist($mysqli);
    $amount = filter_var($post['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
    if ($amount === false || $amount <= 0) {
        return ['success' => false, 'message' => 'Valid budget amount is required.'];
    }
    $budgetId = !empty($post['budget_id']) ? (int) $post['budget_id'] : 0;
    $categoryId = !empty($post['category_id']) ? (int) $post['category_id'] : null;
    $period = $post['period'] ?? 'monthly';
    $startDate = !empty($post['start_date']) ? $post['start_date'] : date('Y-m-d');
    $endDate = !empty($post['end_date']) ? $post['end_date'] : null;

    if ($budgetId > 0) {
        $stmt = safePrepare($mysqli, "UPDATE budgets SET category_id = ?, amount = ?, period = ?, start_date = ?, end_date = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?");
        $stmt->bind_param('idsssii', $categoryId, $amount, $period, $startDate, $endDate, $budgetId, $user_id);
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Budget updated.'];
        }
        return ['success' => false, 'message' => 'Unable to update budget.'];
    }

    $stmt = safePrepare($mysqli, "INSERT INTO budgets (user_id, category_id, amount, period, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iidsss', $user_id, $categoryId, $amount, $period, $startDate, $endDate);
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Budget created.'];
    }
    return ['success' => false, 'message' => 'Unable to create budget.'];
}

function getBudgetsHandler($mysqli, $user_id, $post) {
    ensureExpenseTablesExist($mysqli);
    $stmt = safePrepare($mysqli, "SELECT b.*, ec.name AS category_name, ec.color AS category_color FROM budgets b LEFT JOIN expense_categories ec ON ec.id = b.category_id WHERE b.user_id = ? AND b.is_active = 1 ORDER BY b.created_at DESC");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $budgets = [];
    while ($row = $result->fetch_assoc()) {
        $spent = 0;
        if ($row['category_id']) {
            $expStmt = safePrepare($mysqli, "SELECT COALESCE(SUM(amount), 0) AS spent FROM expenses WHERE user_id = ? AND category_id = ? AND type = 'expense' AND is_deleted = 0 AND transaction_date >= ? AND transaction_date <= COALESCE(?, CURDATE())");
            $expStmt->bind_param('iiss', $user_id, $row['category_id'], $row['start_date'], $row['end_date']);
            $expStmt->execute();
            $spent = (float) ($expStmt->get_result()->fetch_assoc()['spent'] ?? 0);
        } else {
            $expStmt = safePrepare($mysqli, "SELECT COALESCE(SUM(amount), 0) AS spent FROM expenses WHERE user_id = ? AND type = 'expense' AND is_deleted = 0 AND transaction_date >= ? AND transaction_date <= COALESCE(?, CURDATE())");
            $expStmt->bind_param('iss', $user_id, $row['start_date'], $row['end_date']);
            $expStmt->execute();
            $spent = (float) ($expStmt->get_result()->fetch_assoc()['spent'] ?? 0);
        }
        $row['spent'] = $spent;
        $row['remaining'] = (float) $row['amount'] - $spent;
        $row['percentage'] = (float) $row['amount'] > 0 ? min(100, round(($spent / (float) $row['amount']) * 100)) : 0;
        $budgets[] = $row;
    }
    return ['success' => true, 'budgets' => $budgets];
}

function deleteBudgetHandler($mysqli, $user_id, $post) {
    ensureExpenseTablesExist($mysqli);
    $budgetId = !empty($post['budget_id']) ? (int) $post['budget_id'] : 0;
    $stmt = safePrepare($mysqli, "UPDATE budgets SET is_active = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?");
    $stmt->bind_param('ii', $budgetId, $user_id);
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Budget deleted.'];
    }
    return ['success' => false, 'message' => 'Unable to delete budget.'];
}

function getBudgetHandler($mysqli, $user_id, $post) {
    ensureExpenseTablesExist($mysqli);
    $budgetId = !empty($post['budget_id']) ? (int) $post['budget_id'] : 0;
    if ($budgetId <= 0) {
        return ['success' => false, 'message' => 'Invalid budget ID.'];
    }
    $stmt = safePrepare($mysqli, "SELECT b.*, ec.name AS category_name FROM budgets b LEFT JOIN expense_categories ec ON ec.id = b.category_id WHERE b.id = ? AND b.user_id = ?");
    $stmt->bind_param('ii', $budgetId, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $budget = $result->fetch_assoc();
    if (!$budget) {
        return ['success' => false, 'message' => 'Budget not found.'];
    }
    return ['success' => true, 'budget' => $budget];
}

function exportExpensesCsvHandler($mysqli, $user_id, $post) {
    ensureExpenseTablesExist($mysqli);
    $conditions = ['user_id = ?', 'is_deleted = 0'];
    $params = [$user_id];
    $types = 'i';

    if (!empty($post['date_from'])) {
        $conditions[] = 'transaction_date >= ?';
        $params[] = $post['date_from'];
        $types .= 's';
    }
    if (!empty($post['date_to'])) {
        $conditions[] = 'transaction_date <= ?';
        $params[] = $post['date_to'];
        $types .= 's';
    }

    $whereSql = implode(' AND ', $conditions);
    $stmt = safePrepare($mysqli, "SELECT e.title, e.amount, e.type, ec.name AS category, e.transaction_date, e.notes FROM expenses e LEFT JOIN expense_categories ec ON ec.id = e.category_id WHERE $whereSql ORDER BY transaction_date DESC");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename=expenses_export_' . date('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($output, ['Title', 'Amount', 'Type', 'Category', 'Date', 'Notes']);

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['title'],
            number_format($row['amount'], 2),
            ucfirst($row['type']),
            $row['category'] ?? 'Uncategorized',
            $row['transaction_date'],
            $row['notes'] ?? ''
        ]);
    }

    fclose($output);
    exit;
}

/* â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
   Phase 6 â€” Document Vault
   â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

function ensureDocumentTablesExist($mysqli) {
    $statements = [
        "CREATE TABLE IF NOT EXISTS document_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            color VARCHAR(20) DEFAULT '#6366f1',
            is_deleted TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_doc_cat_user (user_id, name),
            INDEX idx_dc_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS documents (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            filename VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            file_size INT DEFAULT 0,
            mime_type VARCHAR(100) DEFAULT NULL,
            category_id INT DEFAULT NULL,
            expiry_date DATE DEFAULT NULL,
            reminder_date DATE DEFAULT NULL,
            is_important TINYINT(1) DEFAULT 0,
            is_deleted TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES document_categories(id) ON DELETE SET NULL,
            INDEX idx_doc_user (user_id, is_deleted),
            INDEX idx_doc_expiry (user_id, expiry_date),
            INDEX idx_doc_category (category_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    foreach ($statements as $s) {
        if (!$mysqli->query($s)) {
            logError("Document table creation failed: " . $mysqli->error . " | Query: " . substr($s, 0, 200), 'SQL_ERROR');
        }
    }
}

function logDocumentActivity($mysqli, $user_id, $entity_id, $action, $description) {
    if (tableExists($mysqli, 'activity_logs')) {
        $stmt = safePrepare($mysqli, "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description, ip_address, user_agent) VALUES (?, ?, 'document', ?, ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $stmt->bind_param('isisss', $user_id, $action, $entity_id, $description, $ip, $ua);
        $stmt->execute();
    }
}

function getDocumentCategories($mysqli, $user_id) {
    ensureDocumentTablesExist($mysqli);
    $stmt = safePrepare($mysqli, "SELECT id, name, color FROM document_categories WHERE user_id = ? AND is_deleted = 0 ORDER BY name ASC");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cats = [];
    while ($row = $result->fetch_assoc()) { $cats[] = $row; }
    return $cats;
}

function getDocumentStats($mysqli, $user_id) {
    ensureDocumentTablesExist($mysqli);
    $stmt = safePrepare($mysqli, "SELECT COUNT(*) AS total, SUM(CASE WHEN is_important = 1 THEN 1 ELSE 0 END) AS important, SUM(CASE WHEN expiry_date IS NOT NULL AND expiry_date < CURDATE() THEN 1 ELSE 0 END) AS expired, SUM(CASE WHEN expiry_date IS NOT NULL AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS expiring_soon FROM documents WHERE user_id = ? AND is_deleted = 0");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return [
        'total' => (int) ($row['total'] ?? 0),
        'important' => (int) ($row['important'] ?? 0),
        'expired' => (int) ($row['expired'] ?? 0),
        'expiring_soon' => (int) ($row['expiring_soon'] ?? 0)
    ];
}

function getDocumentsForList($mysqli, $user_id, $filters) {
    ensureDocumentTablesExist($mysqli);
    $conditions = ['d.user_id = ?', 'd.is_deleted = 0'];
    $params = [$user_id];
    $types = 'i';

    if (!empty($filters['search'])) {
        $search = '%' . $filters['search'] . '%';
        $conditions[] = '(d.title LIKE ? OR d.description LIKE ? OR d.original_name LIKE ?)';
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $types .= 'sss';
    }
    if (!empty($filters['category'])) {
        $conditions[] = 'd.category_id = ?';
        $params[] = (int) $filters['category'];
        $types .= 'i';
    }
    if (!empty($filters['expiring'])) {
        $conditions[] = 'd.expiry_date IS NOT NULL AND d.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)';
    }
    if (!empty($filters['expired'])) {
        $conditions[] = 'd.expiry_date IS NOT NULL AND d.expiry_date < CURDATE()';
    }

    $whereSql = implode(' AND ', $conditions);
    $countStmt = safePrepare($mysqli, "SELECT COUNT(*) AS total FROM documents d WHERE $whereSql");
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $total = (int) ($countStmt->get_result()->fetch_assoc()['total'] ?? 0);

    $perPage = 12;
    $page = max(1, (int) ($filters['page'] ?? 1));
    $offset = ($page - 1) * $perPage;
    $totalPages = max(1, (int) ceil($total / $perPage));

    $stmt = safePrepare($mysqli, "SELECT d.*, dc.name AS category_name, dc.color AS category_color FROM documents d LEFT JOIN document_categories dc ON dc.id = d.category_id WHERE $whereSql ORDER BY d.created_at DESC LIMIT ? OFFSET ?");
    $bindTypes = $types . 'ii';
    $bindParams = array_merge($params, [$perPage, $offset]);
    $stmt->bind_param($bindTypes, ...$bindParams);
    $stmt->execute();
    $result = $stmt->get_result();
    $docs = [];
    while ($row = $result->fetch_assoc()) { $docs[] = $row; }

    return ['documents' => $docs, 'total' => $total, 'total_pages' => $totalPages, 'page' => $page, 'per_page' => $perPage];
}

function buildDocumentPaginationUrl($filters, $page) {
    $params = [];
    foreach ($filters as $key => $value) {
        if ($value === '' || $value === null || $value === false || $value === 0) continue;
        if ($key === 'page') continue;
        $params[] = urlencode($key) . '=' . urlencode((string) $value);
    }
    $params[] = 'page=' . (int) $page;
    return '?' . implode('&', $params);
}

function uploadDocumentHandler($mysqli, $user_id, $post) {
    ensureDocumentTablesExist($mysqli);
    $title = trim($post['title'] ?? '');
    if ($title === '') {
        return ['success' => false, 'message' => 'Title is required.'];
    }

    if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error.'];
    }

    $file = $_FILES['document'];
    $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'xls', 'xlsx', 'txt'];
    $maxSize = 10 * 1024 * 1024;

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return ['success' => false, 'message' => 'File type not allowed. Allowed: ' . implode(', ', $allowed)];
    }
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File too large. Max 10MB.'];
    }

    $uploadDir = UPLOAD_DIR . 'documents/';
    if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }

    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $filepath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'message' => 'Failed to save file.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filepath);
    finfo_close($finfo);

    $description = trim($post['description'] ?? '');
    $categoryId = !empty($post['category_id']) ? (int) $post['category_id'] : null;
    $expiryDate = !empty($post['expiry_date']) ? $post['expiry_date'] : null;
    $reminderDate = !empty($post['reminder_date']) ? $post['reminder_date'] : null;
    $isImportant = !empty($post['is_important']) ? 1 : 0;

    $stmt = safePrepare($mysqli, "INSERT INTO documents (user_id, title, description, filename, original_name, file_size, mime_type, category_id, expiry_date, reminder_date, is_important) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $originalName = $file['name'];
    $fileSize = $file['size'];
    $stmt->bind_param('issssiisssi', $user_id, $title, $description, $filename, $originalName, $fileSize, $mimeType, $categoryId, $expiryDate, $reminderDate, $isImportant);
    if ($stmt->execute()) {
        logDocumentActivity($mysqli, $user_id, $stmt->insert_id, 'document_uploaded', 'Document uploaded: ' . $title);
        return ['success' => true, 'message' => 'Document uploaded.', 'document_id' => $stmt->insert_id];
    }
    return ['success' => false, 'message' => 'Failed to save document.'];
}

function getDocumentHandler($mysqli, $user_id, $post) {
    ensureDocumentTablesExist($mysqli);
    $docId = !empty($post['document_id']) ? (int) $post['document_id'] : 0;
    $stmt = safePrepare($mysqli, 'SELECT d.*, dc.name AS category_name, dc.color AS category_color FROM documents d LEFT JOIN document_categories dc ON dc.id = d.category_id WHERE d.id = ? AND d.user_id = ? AND d.is_deleted = 0');
    $stmt->bind_param('ii', $docId, $user_id);
    $stmt->execute();
    $doc = $stmt->get_result()->fetch_assoc();
    if ($doc) {
        return ['success' => true, 'document' => $doc];
    }
    return ['success' => false, 'message' => 'Document not found.'];
}

function deleteDocumentHandler($mysqli, $user_id, $post) {
    ensureDocumentTablesExist($mysqli);
    $docId = !empty($post['document_id']) ? (int) $post['document_id'] : 0;
    $stmt = safePrepare($mysqli, 'UPDATE documents SET is_deleted = 1, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $docId, $user_id);
    if ($stmt->execute()) {
        logDocumentActivity($mysqli, $user_id, $docId, 'document_deleted', 'Document moved to trash');
        return ['success' => true, 'message' => 'Document deleted.'];
    }
    return ['success' => false, 'message' => 'Unable to delete document.'];
}

function permanentDeleteDocumentHandler($mysqli, $user_id, $post) {
    ensureDocumentTablesExist($mysqli);
    $docId = !empty($post['document_id']) ? (int) $post['document_id'] : 0;

    $stmt = safePrepare($mysqli, 'SELECT filename FROM documents WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $docId, $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) {
        $filepath = UPLOAD_DIR . 'documents/' . $row['filename'];
        if (file_exists($filepath)) { unlink($filepath); }
    }

    $del = safePrepare($mysqli, 'DELETE FROM documents WHERE id = ? AND user_id = ?');
    $del->bind_param('ii', $docId, $user_id);
    if ($del->execute()) {
        logDocumentActivity($mysqli, $user_id, $docId, 'document_permanently_deleted', 'Document permanently deleted');
        return ['success' => true, 'message' => 'Document permanently deleted.'];
    }
    return ['success' => false, 'message' => 'Unable to delete document.'];
}

function updateDocumentHandler($mysqli, $user_id, $post) {
    ensureDocumentTablesExist($mysqli);
    $docId = !empty($post['document_id']) ? (int) $post['document_id'] : 0;
    $title = trim($post['title'] ?? '');
    if ($title === '') {
        return ['success' => false, 'message' => 'Title is required.'];
    }
    $description = trim($post['description'] ?? '');
    $categoryId = !empty($post['category_id']) ? (int) $post['category_id'] : null;
    $expiryDate = !empty($post['expiry_date']) ? $post['expiry_date'] : null;
    $reminderDate = !empty($post['reminder_date']) ? $post['reminder_date'] : null;
    $isImportant = !empty($post['is_important']) ? 1 : 0;

    $stmt = safePrepare($mysqli, "UPDATE documents SET title = ?, description = ?, category_id = ?, expiry_date = ?, reminder_date = ?, is_important = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ? AND is_deleted = 0");
    $stmt->bind_param('ssissiii', $title, $description, $categoryId, $expiryDate, $reminderDate, $isImportant, $docId, $user_id);
    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Document updated.'];
    }
    return ['success' => false, 'message' => 'Unable to update document.'];
}

function saveDocumentCategoryHandler($mysqli, $user_id, $post) {
    ensureDocumentTablesExist($mysqli);
    $name = trim($post['name'] ?? '');
    if ($name === '') { return ['success' => false, 'message' => 'Category name is required.']; }
    $categoryId = !empty($post['category_id']) ? (int) $post['category_id'] : 0;
    $color = $post['color'] ?? '#6366f1';

    if ($categoryId > 0) {
        $stmt = safePrepare($mysqli, 'UPDATE document_categories SET name = ?, color = ? WHERE id = ? AND user_id = ? AND is_deleted = 0');
        $stmt->bind_param('ssii', $name, $color, $categoryId, $user_id);
        if ($stmt->execute()) { return ['success' => true, 'message' => 'Category updated.']; }
        return ['success' => false, 'message' => 'Unable to update category.'];
    }

    $stmt = safePrepare($mysqli, 'INSERT INTO document_categories (user_id, name, color) VALUES (?, ?, ?)');
    $stmt->bind_param('iss', $user_id, $name, $color);
    if ($stmt->execute()) { return ['success' => true, 'message' => 'Category created.']; }
    return ['success' => false, 'message' => 'Unable to create category.'];
}

function deleteDocumentCategoryHandler($mysqli, $user_id, $post) {
    ensureDocumentTablesExist($mysqli);
    $categoryId = !empty($post['category_id']) ? (int) $post['category_id'] : 0;
    $stmt = safePrepare($mysqli, 'UPDATE document_categories SET is_deleted = 1 WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $categoryId, $user_id);
    if ($stmt->execute()) {
        $update = safePrepare($mysqli, 'UPDATE documents SET category_id = NULL WHERE category_id = ? AND user_id = ?');
        $update->bind_param('ii', $categoryId, $user_id);
        $update->execute();
        return ['success' => true, 'message' => 'Category deleted.'];
    }
    return ['success' => false, 'message' => 'Unable to delete category.'];
}

function getDocumentCategoriesHandler($mysqli, $user_id, $post) {
    return ['success' => true, 'categories' => getDocumentCategories($mysqli, $user_id)];
}

/* â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
   Phase 7 â€” Borrow & Lend
   â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

function ensureBorrowTablesExist($mysqli) {
    $statements = [
        "CREATE TABLE IF NOT EXISTS borrow_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            type ENUM('borrowed', 'lent') NOT NULL,
            item_type ENUM('money', 'item') DEFAULT 'item',
            amount DECIMAL(12,2) DEFAULT 0.00,
            person_name VARCHAR(150) NOT NULL,
            person_contact VARCHAR(255) DEFAULT NULL,
            borrow_date DATE NOT NULL,
            return_date DATE DEFAULT NULL,
            actual_return_date DATE DEFAULT NULL,
            status ENUM('pending', 'returned', 'overdue') DEFAULT 'pending',
            is_deleted TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_borrow_user (user_id, is_deleted),
            INDEX idx_borrow_status (user_id, status),
            INDEX idx_borrow_return (user_id, return_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    foreach ($statements as $s) {
        if (!$mysqli->query($s)) {
            logError("Borrow table creation failed: " . $mysqli->error . " | Query: " . substr($s, 0, 200), 'SQL_ERROR');
        }
    }
}

function logBorrowActivity($mysqli, $user_id, $entity_id, $action, $description) {
    if (tableExists($mysqli, 'activity_logs')) {
        $stmt = safePrepare($mysqli, "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description, ip_address, user_agent) VALUES (?, ?, 'borrow', ?, ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $stmt->bind_param('isisss', $user_id, $action, $entity_id, $description, $ip, $ua);
        $stmt->execute();
    }
}

function getBorrowStats($mysqli, $user_id) {
    ensureBorrowTablesExist($mysqli);
    $stmt = safePrepare($mysqli, "SELECT COUNT(*) AS total, SUM(CASE WHEN status = 'pending' AND type = 'borrowed' THEN 1 ELSE 0 END) AS pending_borrowed, SUM(CASE WHEN status = 'pending' AND type = 'lent' THEN 1 ELSE 0 END) AS pending_lent, SUM(CASE WHEN status = 'pending' AND return_date < CURDATE() THEN 1 ELSE 0 END) AS overdue, SUM(CASE WHEN status = 'pending' AND type = 'lent' THEN amount ELSE 0 END) AS total_lent, SUM(CASE WHEN status = 'pending' AND type = 'borrowed' THEN amount ELSE 0 END) AS total_borrowed FROM borrow_items WHERE user_id = ? AND is_deleted = 0");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return [
        'total' => (int) ($row['total'] ?? 0),
        'pending_borrowed' => (int) ($row['pending_borrowed'] ?? 0),
        'pending_lent' => (int) ($row['pending_lent'] ?? 0),
        'overdue' => (int) ($row['overdue'] ?? 0),
        'total_lent' => (float) ($row['total_lent'] ?? 0),
        'total_borrowed' => (float) ($row['total_borrowed'] ?? 0)
    ];
}

function getBorrowItemsForList($mysqli, $user_id, $filters) {
    ensureBorrowTablesExist($mysqli);
    $conditions = ['user_id = ?', 'is_deleted = 0'];
    $params = [$user_id];
    $types = 'i';

    if (!empty($filters['search'])) {
        $search = '%' . $filters['search'] . '%';
        $conditions[] = '(title LIKE ? OR person_name LIKE ? OR description LIKE ?)';
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $types .= 'sss';
    }
    if (!empty($filters['type'])) {
        $conditions[] = 'type = ?';
        $params[] = $filters['type'];
        $types .= 's';
    }
    if (!empty($filters['status'])) {
        if ($filters['status'] === 'overdue') {
            $conditions[] = "status = 'pending' AND return_date < CURDATE()";
        } else {
            $conditions[] = 'status = ?';
            $params[] = $filters['status'];
            $types .= 's';
        }
    }

    $whereSql = implode(' AND ', $conditions);
    $countStmt = safePrepare($mysqli, "SELECT COUNT(*) AS total FROM borrow_items WHERE $whereSql");
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $total = (int) ($countStmt->get_result()->fetch_assoc()['total'] ?? 0);

    $perPage = 15;
    $page = max(1, (int) ($filters['page'] ?? 1));
    $offset = ($page - 1) * $perPage;
    $totalPages = max(1, (int) ceil($total / $perPage));

    $stmt = safePrepare($mysqli, "SELECT * FROM borrow_items WHERE $whereSql ORDER BY is_deleted ASC, FIELD(status, 'overdue', 'pending', 'returned') ASC, borrow_date DESC LIMIT ? OFFSET ?");
    $bindTypes = $types . 'ii';
    $bindParams = array_merge($params, [$perPage, $offset]);
    $stmt->bind_param($bindTypes, ...$bindParams);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        if ($row['status'] === 'pending' && $row['return_date'] && $row['return_date'] < date('Y-m-d')) {
            $row['status'] = 'overdue';
        }
        $items[] = $row;
    }

    return ['items' => $items, 'total' => $total, 'total_pages' => $totalPages, 'page' => $page, 'per_page' => $perPage];
}

function buildBorrowPaginationUrl($filters, $page) {
    $params = [];
    foreach ($filters as $key => $value) {
        if ($value === '' || $value === null || $value === false || $value === 0) continue;
        if ($key === 'page') continue;
        $params[] = urlencode($key) . '=' . urlencode((string) $value);
    }
    $params[] = 'page=' . (int) $page;
    return '?' . implode('&', $params);
}

function saveBorrowHandler($mysqli, $user_id, $post) {
    ensureBorrowTablesExist($mysqli);
    $title = trim($post['title'] ?? '');
    if ($title === '') { return ['success' => false, 'message' => 'Title is required.']; }
    $personName = trim($post['person_name'] ?? '');
    if ($personName === '') { return ['success' => false, 'message' => 'Person name is required.']; }

    $borrowId = !empty($post['borrow_id']) ? (int) $post['borrow_id'] : 0;
    $type = ($post['type'] ?? 'borrowed') === 'lent' ? 'lent' : 'borrowed';
    $itemType = ($post['item_type'] ?? 'item') === 'money' ? 'money' : 'item';
    $amount = filter_var($post['amount'] ?? 0, FILTER_VALIDATE_FLOAT) ?: 0;
    $description = trim($post['description'] ?? '');
    $personContact = trim($post['person_contact'] ?? '');
    $borrowDate = !empty($post['borrow_date']) ? $post['borrow_date'] : date('Y-m-d');
    $returnDate = !empty($post['return_date']) ? $post['return_date'] : null;

    if ($borrowId > 0) {
        $stmt = safePrepare($mysqli, "UPDATE borrow_items SET title = ?, description = ?, type = ?, item_type = ?, amount = ?, person_name = ?, person_contact = ?, borrow_date = ?, return_date = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ? AND is_deleted = 0");
        $stmt->bind_param('ssssdssssii', $title, $description, $type, $itemType, $amount, $personName, $personContact, $borrowDate, $returnDate, $borrowId, $user_id);
        if ($stmt->execute()) {
            logBorrowActivity($mysqli, $user_id, $borrowId, 'borrow_updated', 'Updated: ' . $title);
            return ['success' => true, 'message' => 'Record updated.'];
        }
        return ['success' => false, 'message' => 'Unable to update record.'];
    }

    $stmt = safePrepare($mysqli, "INSERT INTO borrow_items (user_id, title, description, type, item_type, amount, person_name, person_contact, borrow_date, return_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('issssdssss', $user_id, $title, $description, $type, $itemType, $amount, $personName, $personContact, $borrowDate, $returnDate);
    if ($stmt->execute()) {
        $newId = $stmt->insert_id;
        logBorrowActivity($mysqli, $user_id, $newId, 'borrow_created', 'Created: ' . $title);
        return ['success' => true, 'message' => 'Record created.', 'borrow_id' => $newId];
    }
    return ['success' => false, 'message' => 'Unable to create record.'];
}

function getBorrowHandler($mysqli, $user_id, $post) {
    ensureBorrowTablesExist($mysqli);
    $borrowId = !empty($post['borrow_id']) ? (int) $post['borrow_id'] : 0;
    $stmt = safePrepare($mysqli, 'SELECT * FROM borrow_items WHERE id = ? AND user_id = ? AND is_deleted = 0');
    $stmt->bind_param('ii', $borrowId, $user_id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    if ($item) {
        if ($item['status'] === 'pending' && $item['return_date'] && $item['return_date'] < date('Y-m-d')) {
            $item['status'] = 'overdue';
        }
        return ['success' => true, 'item' => $item];
    }
    return ['success' => false, 'message' => 'Record not found.'];
}

function deleteBorrowHandler($mysqli, $user_id, $post) {
    ensureBorrowTablesExist($mysqli);
    $borrowId = !empty($post['borrow_id']) ? (int) $post['borrow_id'] : 0;
    $stmt = safePrepare($mysqli, 'UPDATE borrow_items SET is_deleted = 1, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $borrowId, $user_id);
    if ($stmt->execute()) {
        logBorrowActivity($mysqli, $user_id, $borrowId, 'borrow_deleted', 'Record deleted');
        return ['success' => true, 'message' => 'Record deleted.'];
    }
    return ['success' => false, 'message' => 'Unable to delete record.'];
}

function markReturnedHandler($mysqli, $user_id, $post) {
    ensureBorrowTablesExist($mysqli);
    $borrowId = !empty($post['borrow_id']) ? (int) $post['borrow_id'] : 0;
    $stmt = safePrepare($mysqli, "UPDATE borrow_items SET status = 'returned', actual_return_date = CURDATE(), updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ? AND is_deleted = 0");
    $stmt->bind_param('ii', $borrowId, $user_id);
    if ($stmt->execute()) {
        logBorrowActivity($mysqli, $user_id, $borrowId, 'borrow_returned', 'Item marked as returned');
        return ['success' => true, 'message' => 'Marked as returned.'];
    }
    return ['success' => false, 'message' => 'Unable to update status.'];
}

/* â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
   Phase 8 â€” Habits, Goals, Shopping
   â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

function ensureHabitGoalShoppingTablesExist($mysqli) {
    $statements = [
        "CREATE TABLE IF NOT EXISTS habits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(150) NOT NULL,
            description TEXT DEFAULT NULL,
            frequency ENUM('daily', 'weekly', 'monthly') DEFAULT 'daily',
            target_count INT DEFAULT 1,
            color VARCHAR(20) DEFAULT '#6366f1',
            icon VARCHAR(50) DEFAULT NULL,
            is_active TINYINT(1) DEFAULT 1,
            is_deleted TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_habits_user (user_id, is_deleted)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS habit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            habit_id INT NOT NULL,
            log_date DATE NOT NULL,
            count_value INT DEFAULT 1,
            notes TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (habit_id) REFERENCES habits(id) ON DELETE CASCADE,
            UNIQUE KEY unique_habit_log (habit_id, log_date),
            INDEX idx_hl_user_date (user_id, log_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS goals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            category VARCHAR(100) DEFAULT NULL,
            target_value DECIMAL(12,2) DEFAULT 0,
            current_value DECIMAL(12,2) DEFAULT 0,
            unit VARCHAR(50) DEFAULT NULL,
            start_date DATE NOT NULL,
            due_date DATE DEFAULT NULL,
            completed_date DATE DEFAULT NULL,
            status ENUM('active', 'completed', 'abandoned') DEFAULT 'active',
            is_deleted TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_goals_user (user_id, is_deleted, status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS shopping (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            quantity INT DEFAULT 1,
            estimated_price DECIMAL(10,2) DEFAULT 0.00,
            actual_price DECIMAL(10,2) DEFAULT 0.00,
            category VARCHAR(100) DEFAULT NULL,
            notes TEXT DEFAULT NULL,
            is_completed TINYINT(1) DEFAULT 0,
            is_deleted TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_shop_user (user_id, is_deleted)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    foreach ($statements as $s) {
        if (!$mysqli->query($s)) {
            logError("Habit/Goal/Shopping table creation failed: " . $mysqli->error . " | Query: " . substr($s, 0, 200), 'SQL_ERROR');
        }
    }
}

function logHabitActivity($mysqli, $user_id, $entity_id, $action, $description) {
    if (tableExists($mysqli, 'activity_logs')) {
        $stmt = safePrepare($mysqli, "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description, ip_address, user_agent) VALUES (?, ?, 'habit', ?, ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $stmt->bind_param('isisss', $user_id, $action, $entity_id, $description, $ip, $ua);
        $stmt->execute();
    }
}

// â”€â”€ Habits â”€â”€

function getHabitsForList($mysqli, $user_id, $filters) {
    ensureHabitGoalShoppingTablesExist($mysqli);
    $conditions = ['user_id = ?', 'is_deleted = 0'];
    $params = [$user_id];
    $types = 'i';

    if (!empty($filters['search'])) {
        $search = '%' . $filters['search'] . '%';
        $conditions[] = '(name LIKE ? OR description LIKE ?)';
        $params[] = $search;
        $params[] = $search;
        $types .= 'ss';
    }

    $whereSql = implode(' AND ', $conditions);
    $countStmt = safePrepare($mysqli, "SELECT COUNT(*) AS total FROM habits WHERE $whereSql");
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $total = (int) ($countStmt->get_result()->fetch_assoc()['total'] ?? 0);

    $perPage = 20;
    $page = max(1, (int) ($filters['page'] ?? 1));
    $offset = ($page - 1) * $perPage;
    $totalPages = max(1, (int) ceil($total / $perPage));

    $stmt = safePrepare($mysqli, "SELECT h.*, (SELECT COUNT(*) FROM habit_logs hl WHERE hl.habit_id = h.id AND hl.log_date = CURDATE()) AS logged_today, (SELECT COUNT(*) FROM habit_logs hl WHERE hl.habit_id = h.id AND hl.log_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)) AS logged_week FROM habits h WHERE $whereSql ORDER BY h.name ASC LIMIT ? OFFSET ?");
    $bindTypes = $types . 'ii';
    $bindParams = array_merge($params, [$perPage, $offset]);
    $stmt->bind_param($bindTypes, ...$bindParams);
    $stmt->execute();
    $result = $stmt->get_result();
    $habits = [];
    while ($row = $result->fetch_assoc()) { $habits[] = $row; }

    return ['items' => $habits, 'total' => $total, 'total_pages' => $totalPages, 'page' => $page];
}

function getHabitStats($mysqli, $user_id) {
    ensureHabitGoalShoppingTablesExist($mysqli);
    $stmt = safePrepare($mysqli, "SELECT COUNT(*) AS total, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active FROM habits WHERE user_id = ? AND is_deleted = 0");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $totalHabits = (int) ($row['total'] ?? 0);
    $activeHabits = (int) ($row['active'] ?? 0);

    $logStmt = safePrepare($mysqli, "SELECT COUNT(*) AS total_today FROM habit_logs WHERE user_id = ? AND log_date = CURDATE()");
    $logStmt->bind_param('i', $user_id);
    $logStmt->execute();
    $logRow = $logStmt->get_result()->fetch_assoc();

    $weekStmt = safePrepare($mysqli, "SELECT COUNT(DISTINCT habit_id) AS unique_week FROM habit_logs WHERE user_id = ? AND log_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
    $weekStmt->bind_param('i', $user_id);
    $weekStmt->execute();
    $weekRow = $weekStmt->get_result()->fetch_assoc();

    $streakStmt = safePrepare($mysqli, "SELECT MAX(streak_len) AS best_streak FROM (SELECT COUNT(*) AS streak_len FROM (SELECT log_date, DATE_SUB(log_date, INTERVAL ROW_NUMBER() OVER (ORDER BY log_date) DAY) AS grp FROM habit_logs WHERE user_id = ? AND log_date >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)) t GROUP BY grp) s");
    $streakStmt->bind_param('i', $user_id);
    $streakStmt->execute();
    $streakRow = $streakStmt->get_result()->fetch_assoc();

    return [
        'total' => $totalHabits,
        'active' => $activeHabits,
        'logged_today' => (int) ($logRow['total_today'] ?? 0),
        'unique_this_week' => (int) ($weekRow['unique_week'] ?? 0),
        'best_streak' => (int) ($streakRow['best_streak'] ?? 0)
    ];
}

function saveHabitHandler($mysqli, $user_id, $post) {
    ensureHabitGoalShoppingTablesExist($mysqli);
    $name = trim($post['name'] ?? '');
    if ($name === '') { return ['success' => false, 'message' => 'Habit name is required.']; }

    $habitId = !empty($post['habit_id']) ? (int) $post['habit_id'] : 0;
    $description = trim($post['description'] ?? '');
    $frequency = $post['frequency'] ?? 'daily';
    $targetCount = max(1, (int) ($post['target_count'] ?? 1));
    $color = $post['color'] ?? '#6366f1';

    if ($habitId > 0) {
        $stmt = safePrepare($mysqli, "UPDATE habits SET name = ?, description = ?, frequency = ?, target_count = ?, color = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ? AND is_deleted = 0");
        $stmt->bind_param('sssisisi', $name, $description, $frequency, $targetCount, $color, $habitId, $user_id);
        if ($stmt->execute()) { return ['success' => true, 'message' => 'Habit updated.']; }
        return ['success' => false, 'message' => 'Unable to update habit.'];
    }

    $stmt = safePrepare($mysqli, "INSERT INTO habits (user_id, name, description, frequency, target_count, color) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('isssis', $user_id, $name, $description, $frequency, $targetCount, $color);
    if ($stmt->execute()) {
        logHabitActivity($mysqli, $user_id, $stmt->insert_id, 'habit_created', 'Habit created: ' . $name);
        return ['success' => true, 'message' => 'Habit created.', 'habit_id' => $stmt->insert_id];
    }
    return ['success' => false, 'message' => 'Unable to create habit.'];
}

function getHabitHandler($mysqli, $user_id, $post) {
    ensureHabitGoalShoppingTablesExist($mysqli);
    $habitId = !empty($post['habit_id']) ? (int) $post['habit_id'] : 0;
    if ($habitId <= 0) { return ['success' => false, 'message' => 'Invalid habit ID.']; }
    $stmt = safePrepare($mysqli, 'SELECT * FROM habits WHERE id = ? AND user_id = ? AND is_deleted = 0');
    $stmt->bind_param('ii', $habitId, $user_id);
    $stmt->execute();
    $habit = $stmt->get_result()->fetch_assoc();
    if (!$habit) { return ['success' => false, 'message' => 'Habit not found.']; }
    return ['success' => true, 'habit' => $habit];
}

function logHabitHandler($mysqli, $user_id, $post) {
    ensureHabitGoalShoppingTablesExist($mysqli);
    $habitId = !empty($post['habit_id']) ? (int) $post['habit_id'] : 0;
    $logDate = !empty($post['log_date']) ? $post['log_date'] : date('Y-m-d');
    $countValue = max(1, (int) ($post['count_value'] ?? 1));
    $notes = trim($post['notes'] ?? '');

    $check = safePrepare($mysqli, 'SELECT id, target_count FROM habits WHERE id = ? AND user_id = ? AND is_deleted = 0');
    $check->bind_param('ii', $habitId, $user_id);
    $check->execute();
    $habit = $check->get_result()->fetch_assoc();
    if (!$habit) { return ['success' => false, 'message' => 'Habit not found.']; }

    $existing = safePrepare($mysqli, 'SELECT id, count_value FROM habit_logs WHERE habit_id = ? AND log_date = ?');
    $existing->bind_param('is', $habitId, $logDate);
    $existing->execute();
    $existingRow = $existing->get_result()->fetch_assoc();

    if ($existingRow) {
        $newCount = $existingRow['count_value'] + $countValue;
        $stmt = safePrepare($mysqli, "UPDATE habit_logs SET count_value = ?, notes = ? WHERE id = ?");
        $stmt->bind_param('isi', $newCount, $notes, $existingRow['id']);
        $stmt->execute();
    } else {
        $stmt = safePrepare($mysqli, "INSERT INTO habit_logs (user_id, habit_id, log_date, count_value, notes) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('iisis', $user_id, $habitId, $logDate, $countValue, $notes);
        $stmt->execute();
    }

    return ['success' => true, 'message' => 'Habit logged.'];
}

function deleteHabitHandler($mysqli, $user_id, $post) {
    ensureHabitGoalShoppingTablesExist($mysqli);
    $habitId = !empty($post['habit_id']) ? (int) $post['habit_id'] : 0;
    $stmt = safePrepare($mysqli, 'UPDATE habits SET is_deleted = 1, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $habitId, $user_id);
    if ($stmt->execute()) { return ['success' => true, 'message' => 'Habit deleted.']; }
    return ['success' => false, 'message' => 'Unable to delete habit.'];
}

function getHabitChartDataHandler($mysqli, $user_id, $post) {
    ensureHabitGoalShoppingTablesExist($mysqli);
    $labels = [];
    $values = [];
    for ($i = 6; $i >= 0; $i--) {
        $day = date('Y-m-d', strtotime("-$i days"));
        $labels[] = date('D', strtotime($day));
        $stmt = safePrepare($mysqli, "SELECT COALESCE(SUM(count_value), 0) AS total FROM habit_logs WHERE user_id = ? AND log_date = ?");
        $stmt->bind_param('is', $user_id, $day);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $values[] = (int) ($row['total'] ?? 0);
    }
    return ['success' => true, 'chart' => ['labels' => $labels, 'values' => $values]];
}

// â”€â”€ Goals â”€â”€

function getGoalsForList($mysqli, $user_id, $filters) {
    ensureHabitGoalShoppingTablesExist($mysqli);
    $conditions = ['user_id = ?', 'is_deleted = 0'];
    $params = [$user_id];
    $types = 'i';

    if (!empty($filters['search'])) {
        $search = '%' . $filters['search'] . '%';
        $conditions[] = '(title LIKE ? OR description LIKE ?)';
        $params[] = $search;
        $params[] = $search;
        $types .= 'ss';
    }
    if (!empty($filters['status'])) {
        $conditions[] = 'status = ?';
        $params[] = $filters['status'];
        $types .= 's';
    }

    $whereSql = implode(' AND ', $conditions);
    $countStmt = safePrepare($mysqli, "SELECT COUNT(*) AS total FROM goals WHERE $whereSql");
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $total = (int) ($countStmt->get_result()->fetch_assoc()['total'] ?? 0);

    $perPage = 15;
    $page = max(1, (int) ($filters['page'] ?? 1));
    $offset = ($page - 1) * $perPage;
    $totalPages = max(1, (int) ceil($total / $perPage));

    $stmt = safePrepare($mysqli, "SELECT * FROM goals WHERE $whereSql ORDER BY FIELD(status, 'active', 'completed', 'abandoned'), due_date ASC LIMIT ? OFFSET ?");
    $bindTypes = $types . 'ii';
    $bindParams = array_merge($params, [$perPage, $offset]);
    $stmt->bind_param($bindTypes, ...$bindParams);
    $stmt->execute();
    $result = $stmt->get_result();
    $goals = [];
    while ($row = $result->fetch_assoc()) { $goals[] = $row; }

    return ['items' => $goals, 'total' => $total, 'total_pages' => $totalPages, 'page' => $page];
}

function getGoalStats($mysqli, $user_id) {
    ensureHabitGoalShoppingTablesExist($mysqli);
    $stmt = safePrepare($mysqli, "SELECT COUNT(*) AS total, SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active, SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed, SUM(CASE WHEN status = 'abandoned' THEN 1 ELSE 0 END) AS abandoned FROM goals WHERE user_id = ? AND is_deleted = 0");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return [
        'total' => (int) ($row['total'] ?? 0),
        'active' => (int) ($row['active'] ?? 0),
        'completed' => (int) ($row['completed'] ?? 0),
        'abandoned' => (int) ($row['abandoned'] ?? 0)
    ];
}

function saveGoalHandler($mysqli, $user_id, $post) {
    ensureHabitGoalShoppingTablesExist($mysqli);
    $title = trim($post['title'] ?? '');
    if ($title === '') { return ['success' => false, 'message' => 'Goal title is required.']; }

    $goalId = !empty($post['goal_id']) ? (int) $post['goal_id'] : 0;
    $description = trim($post['description'] ?? '');
    $category = trim($post['category'] ?? '');
    $targetValue = filter_var($post['target_value'] ?? 0, FILTER_VALIDATE_FLOAT) ?: 0;
    $currentValue = filter_var($post['current_value'] ?? 0, FILTER_VALIDATE_FLOAT) ?: 0;
    $unit = trim($post['unit'] ?? '');
    $startDate = !empty($post['start_date']) ? $post['start_date'] : date('Y-m-d');
    $dueDate = !empty($post['due_date']) ? $post['due_date'] : null;
    $status = $post['status'] ?? 'active';
    $completedDate = null;

    if ($status === 'completed') {
        $completedDate = date('Y-m-d');
        $currentValue = $targetValue;
    }

    if ($goalId > 0) {
        $stmt = safePrepare($mysqli, "UPDATE goals SET title = ?, description = ?, category = ?, target_value = ?, current_value = ?, unit = ?, start_date = ?, due_date = ?, completed_date = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ? AND is_deleted = 0");
        $stmt->bind_param('sssddsssssii', $title, $description, $category, $targetValue, $currentValue, $unit, $startDate, $dueDate, $completedDate, $status, $goalId, $user_id);
        if ($stmt->execute()) { return ['success' => true, 'message' => 'Goal updated.']; }
        return ['success' => false, 'message' => 'Unable to update goal.'];
    }

    $stmt = safePrepare($mysqli, "INSERT INTO goals (user_id, title, description, category, target_value, current_value, unit, start_date, due_date, completed_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('isssddsssss', $user_id, $title, $description, $category, $targetValue, $currentValue, $unit, $startDate, $dueDate, $completedDate, $status);
    if ($stmt->execute()) {
        logHabitActivity($mysqli, $user_id, $stmt->insert_id, 'goal_created', 'Goal created: ' . $title);
        return ['success' => true, 'message' => 'Goal created.', 'goal_id' => $stmt->insert_id];
    }
    return ['success' => false, 'message' => 'Unable to create goal.'];
}

function updateGoalProgressHandler($mysqli, $user_id, $post) {
    ensureHabitGoalShoppingTablesExist($mysqli);
    $goalId = !empty($post['goal_id']) ? (int) $post['goal_id'] : 0;
    $currentValue = filter_var($post['current_value'] ?? 0, FILTER_VALIDATE_FLOAT) ?: 0;

    $check = safePrepare($mysqli, "SELECT target_value FROM goals WHERE id = ? AND user_id = ? AND is_deleted = 0 AND status = 'active'");
    $check->bind_param('ii', $goalId, $user_id);
    $check->execute();
    $goal = $check->get_result()->fetch_assoc();
    if (!$goal) { return ['success' => false, 'message' => 'Goal not found or not active.']; }

    $newStatus = 'active';
    $completedDate = null;
    if ($currentValue >= (float) $goal['target_value']) {
        $newStatus = 'completed';
        $completedDate = date('Y-m-d');
        $currentValue = (float) $goal['target_value'];
    }

    $stmt = safePrepare($mysqli, "UPDATE goals SET current_value = ?, status = ?, completed_date = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?");
    $stmt->bind_param('dssii', $currentValue, $newStatus, $completedDate, $goalId, $user_id);
    if ($stmt->execute()) {
        $msg = $newStatus === 'completed' ? 'Goal completed!' : 'Progress updated.';
        return ['success' => true, 'message' => $msg, 'completed' => $newStatus === 'completed'];
    }
    return ['success' => false, 'message' => 'Unable to update progress.'];
}

function deleteGoalHandler($mysqli, $user_id, $post) {
    ensureHabitGoalShoppingTablesExist($mysqli);
    $goalId = !empty($post['goal_id']) ? (int) $post['goal_id'] : 0;
    $stmt = safePrepare($mysqli, 'UPDATE goals SET is_deleted = 1, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $goalId, $user_id);
    if ($stmt->execute()) { return ['success' => true, 'message' => 'Goal deleted.']; }
    return ['success' => false, 'message' => 'Unable to delete goal.'];
}

// â”€â”€ Shopping â”€â”€

function getShoppingForList($mysqli, $user_id, $filters) {
    ensureHabitGoalShoppingTablesExist($mysqli);
    $conditions = ['user_id = ?', 'is_deleted = 0'];
    $params = [$user_id];
    $types = 'i';

    if (!empty($filters['search'])) {
        $search = '%' . $filters['search'] . '%';
        $conditions[] = '(name LIKE ? OR notes LIKE ?)';
        $params[] = $search;
        $params[] = $search;
        $types .= 'ss';
    }
    if (!empty($filters['completed'])) {
        $conditions[] = 'is_completed = 1';
    } else {
        $conditions[] = 'is_completed = 0';
    }

    $whereSql = implode(' AND ', $conditions);
    $countStmt = safePrepare($mysqli, "SELECT COUNT(*) AS total FROM shopping WHERE $whereSql");
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $total = (int) ($countStmt->get_result()->fetch_assoc()['total'] ?? 0);

    $perPage = 20;
    $page = max(1, (int) ($filters['page'] ?? 1));
    $offset = ($page - 1) * $perPage;
    $totalPages = max(1, (int) ceil($total / $perPage));

    $stmt = safePrepare($mysqli, "SELECT * FROM shopping WHERE $whereSql ORDER BY is_completed ASC, created_at DESC LIMIT ? OFFSET ?");
    $bindTypes = $types . 'ii';
    $bindParams = array_merge($params, [$perPage, $offset]);
    $stmt->bind_param($bindTypes, ...$bindParams);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) { $items[] = $row; }

    return ['items' => $items, 'total' => $total, 'total_pages' => $totalPages, 'page' => $page];
}

function getShoppingStats($mysqli, $user_id) {
    ensureHabitGoalShoppingTablesExist($mysqli);
    $stmt = safePrepare($mysqli, "SELECT COUNT(*) AS total, SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) AS completed, SUM(CASE WHEN is_completed = 0 THEN estimated_price * quantity ELSE 0 END) AS estimated_total, SUM(CASE WHEN is_completed = 1 THEN actual_price * quantity ELSE 0 END) AS actual_total FROM shopping WHERE user_id = ? AND is_deleted = 0");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return [
        'total' => (int) ($row['total'] ?? 0),
        'completed' => (int) ($row['completed'] ?? 0),
        'pending' => (int) ($row['total'] ?? 0) - (int) ($row['completed'] ?? 0),
        'estimated_total' => (float) ($row['estimated_total'] ?? 0),
        'actual_total' => (float) ($row['actual_total'] ?? 0)
    ];
}

function saveShoppingHandler($mysqli, $user_id, $post) {
    ensureHabitGoalShoppingTablesExist($mysqli);
    $name = trim($post['name'] ?? '');
    if ($name === '') { return ['success' => false, 'message' => 'Item name is required.']; }

    $itemId = !empty($post['item_id']) ? (int) $post['item_id'] : 0;
    $quantity = max(1, (int) ($post['quantity'] ?? 1));
    $estimatedPrice = filter_var($post['estimated_price'] ?? 0, FILTER_VALIDATE_FLOAT) ?: 0;
    $actualPrice = filter_var($post['actual_price'] ?? 0, FILTER_VALIDATE_FLOAT) ?: 0;
    $category = trim($post['category'] ?? '');
    $notes = trim($post['notes'] ?? '');

    if ($itemId > 0) {
        $stmt = safePrepare($mysqli, "UPDATE shopping SET name = ?, quantity = ?, estimated_price = ?, actual_price = ?, category = ?, notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ? AND is_deleted = 0");
        $stmt->bind_param('sidssisi', $name, $quantity, $estimatedPrice, $actualPrice, $category, $notes, $itemId, $user_id);
        if ($stmt->execute()) { return ['success' => true, 'message' => 'Item updated.']; }
        return ['success' => false, 'message' => 'Unable to update item.'];
    }

    $stmt = safePrepare($mysqli, "INSERT INTO shopping (user_id, name, quantity, estimated_price, actual_price, category, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('isiddss', $user_id, $name, $quantity, $estimatedPrice, $actualPrice, $category, $notes);
    if ($stmt->execute()) {
        logHabitActivity($mysqli, $user_id, $stmt->insert_id, 'shopping_created', 'Shopping item added: ' . $name);
        return ['success' => true, 'message' => 'Item added.', 'item_id' => $stmt->insert_id];
    }
    return ['success' => false, 'message' => 'Unable to add item.'];
}

function toggleShoppingCompleteHandler($mysqli, $user_id, $post) {
    ensureHabitGoalShoppingTablesExist($mysqli);
    $itemId = !empty($post['item_id']) ? (int) $post['item_id'] : 0;
    $actualPrice = filter_var($post['actual_price'] ?? 0, FILTER_VALIDATE_FLOAT) ?: 0;

    $stmt = safePrepare($mysqli, "UPDATE shopping SET is_completed = IF(is_completed = 1, 0, 1), actual_price = IF(? > 0, ?, actual_price), updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ? AND is_deleted = 0");
    $stmt->bind_param('ddii', $actualPrice, $actualPrice, $itemId, $user_id);
    if ($stmt->execute()) { return ['success' => true, 'message' => 'Item updated.']; }
    return ['success' => false, 'message' => 'Unable to update item.'];
}

function deleteShoppingHandler($mysqli, $user_id, $post) {
    ensureHabitGoalShoppingTablesExist($mysqli);
    $itemId = !empty($post['item_id']) ? (int) $post['item_id'] : 0;
    $stmt = safePrepare($mysqli, 'UPDATE shopping SET is_deleted = 1, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $itemId, $user_id);
    if ($stmt->execute()) { return ['success' => true, 'message' => 'Item deleted.']; }
    return ['success' => false, 'message' => 'Unable to delete item.'];
}

function clearCompletedShoppingHandler($mysqli, $user_id, $post) {
    ensureHabitGoalShoppingTablesExist($mysqli);
    $stmt = safePrepare($mysqli, 'UPDATE shopping SET is_deleted = 1 WHERE user_id = ? AND is_completed = 1 AND is_deleted = 0');
    $stmt->bind_param('i', $user_id);
    if ($stmt->execute()) { return ['success' => true, 'message' => 'Completed items cleared.']; }
    return ['success' => false, 'message' => 'Unable to clear items.'];
}

function getShoppingItemHandler($mysqli, $user_id, $post) {
    ensureHabitGoalShoppingTablesExist($mysqli);
    $itemId = !empty($post['item_id']) ? (int) $post['item_id'] : 0;
    if ($itemId <= 0) { return ['success' => false, 'message' => 'Invalid item ID.']; }
    $stmt = safePrepare($mysqli, "SELECT * FROM shopping WHERE id = ? AND user_id = ? AND is_deleted = 0");
    $stmt->bind_param('ii', $itemId, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    if (!$item) { return ['success' => false, 'message' => 'Item not found.']; }
    return ['success' => true, 'item' => $item];
}

/* â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
   Phase 9 â€” Admin Panel
   â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

function ensureAdminTablesExist($mysqli) {
    $statements = [
        "CREATE TABLE IF NOT EXISTS feedback (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            category ENUM('bug', 'feature', 'improvement', 'other') DEFAULT 'other',
            status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
            admin_reply TEXT DEFAULT NULL,
            admin_replied_at TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_feedback_status (status),
            INDEX idx_feedback_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS site_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT DEFAULT NULL,
            setting_type VARCHAR(20) DEFAULT 'string',
            description TEXT DEFAULT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    foreach ($statements as $s) {
        if (!$mysqli->query($s)) {
            logError("Admin table creation failed: " . $mysqli->error . " | Query: " . substr($s, 0, 200), 'SQL_ERROR');
        }
    }

    $defaults = [
        ['site_name', 'TaskNest', 'string', 'Site name'],
        ['site_description', 'All-in-one Personal Life Management System', 'string', 'Site description'],
        ['maintenance_mode', '0', 'boolean', 'Enable maintenance mode'],
        ['allow_registration', '1', 'boolean', 'Allow new user registrations'],
        ['items_per_page', '20', 'number', 'Default items per page']
    ];
    foreach ($defaults as $d) {
        $check = safePrepare($mysqli, "INSERT IGNORE INTO site_settings (setting_key, setting_value, setting_type, description) VALUES (?, ?, ?, ?)");
        $check->bind_param('ssss', $d[0], $d[1], $d[2], $d[3]);
        $check->execute();
    }
}

function getAdminStats($mysqli) {
    ensureAdminTablesExist($mysqli);
    $stats = ['total_users' => 0, 'active_users' => 0, 'total_tasks' => 0, 'total_notes' => 0, 'total_expenses' => 0, 'total_documents' => 0, 'open_feedback' => 0];

    $stmt = safePrepare($mysqli, "SELECT COUNT(*) AS total, SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active FROM users");
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stats['total_users'] = (int) ($row['total'] ?? 0);
    $stats['active_users'] = (int) ($row['active'] ?? 0);

    if (tableExists($mysqli, 'tasks')) {
        $stmt = safePrepare($mysqli, "SELECT COUNT(*) AS total FROM tasks WHERE is_deleted = 0");
        $stmt->execute();
        $stats['total_tasks'] = (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    }
    if (tableExists($mysqli, 'notes')) {
        $stmt = safePrepare($mysqli, "SELECT COUNT(*) AS total FROM notes WHERE is_deleted = 0");
        $stmt->execute();
        $stats['total_notes'] = (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    }
    if (tableExists($mysqli, 'expenses')) {
        $stmt = safePrepare($mysqli, "SELECT COUNT(*) AS total FROM expenses WHERE is_deleted = 0");
        $stmt->execute();
        $stats['total_expenses'] = (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    }
    if (tableExists($mysqli, 'documents')) {
        $stmt = safePrepare($mysqli, "SELECT COUNT(*) AS total FROM documents WHERE is_deleted = 0");
        $stmt->execute();
        $stats['total_documents'] = (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    }
    if (tableExists($mysqli, 'feedback')) {
        $stmt = safePrepare($mysqli, "SELECT COUNT(*) AS total FROM feedback WHERE status IN ('open', 'in_progress')");
        $stmt->execute();
        $stats['open_feedback'] = (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    }

    return $stats;
}

function getAdminUsers($mysqli, $filters) {
    $conditions = [];
    $params = [];
    $types = '';

    if (!empty($filters['search'])) {
        $search = '%' . $filters['search'] . '%';
        $conditions[] = '(username LIKE ? OR email LIKE ? OR first_name LIKE ?)';
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $types .= 'sss';
    }

    $whereSql = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $perPage = 20;
    $page = max(1, (int) ($filters['page'] ?? 1));
    $offset = ($page - 1) * $perPage;

    $countStmt = safePrepare($mysqli, "SELECT COUNT(*) AS total FROM users $whereSql");
    if ($types) $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $total = (int) ($countStmt->get_result()->fetch_assoc()['total'] ?? 0);
    $totalPages = max(1, (int) ceil($total / $perPage));

    $stmt = safePrepare($mysqli, "SELECT id, username, email, first_name, last_name, is_active, created_at FROM users $whereSql ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $bindTypes = $types . 'ii';
    $bindParams = array_merge($params, [$perPage, $offset]);
    $stmt->bind_param($bindTypes, ...$bindParams);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = [];
    while ($row = $result->fetch_assoc()) { $users[] = $row; }

    return ['users' => $users, 'total' => $total, 'total_pages' => $totalPages, 'page' => $page];
}

function toggleUserStatusHandler($mysqli, $post) {
    $userId = !empty($post['user_id']) ? (int) $post['user_id'] : 0;
    $stmt = safePrepare($mysqli, "UPDATE users SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?");
    $stmt->bind_param('i', $userId);
    if ($stmt->execute()) { return ['success' => true, 'message' => 'User status updated.']; }
    return ['success' => false, 'message' => 'Unable to update user.'];
}

function deleteUserHandler($mysqli, $post) {
    $userId = !empty($post['user_id']) ? (int) $post['user_id'] : 0;
    $stmt = safePrepare($mysqli, "UPDATE users SET is_active = 0 WHERE id = ?");
    $stmt->bind_param('i', $userId);
    if ($stmt->execute()) { return ['success' => true, 'message' => 'User deactivated.']; }
    return ['success' => false, 'message' => 'Unable to deactivate user.'];
}

function getAdminActivityLog($mysqli, $filters) {
    ensureAdminTablesExist($mysqli);
    $perPage = 30;
    $page = max(1, (int) ($filters['page'] ?? 1));
    $offset = ($page - 1) * $perPage;

    $stmt = safePrepare($mysqli, "SELECT al.*, u.username FROM activity_logs al LEFT JOIN users u ON u.id = al.user_id ORDER BY al.created_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('ii', $perPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $logs = [];
    while ($row = $result->fetch_assoc()) { $logs[] = $row; }

    $countStmt = safePrepare($mysqli, "SELECT COUNT(*) AS total FROM activity_logs");
    $countStmt->execute();
    $total = (int) ($countStmt->get_result()->fetch_assoc()['total'] ?? 0);

    return ['logs' => $logs, 'total' => $total, 'total_pages' => max(1, (int) ceil($total / $perPage)), 'page' => $page];
}

function getFeedbackList($mysqli, $filters) {
    ensureAdminTablesExist($mysqli);
    $conditions = [];
    $params = [];
    $types = '';

    if (!empty($filters['status'])) {
        $conditions[] = 'f.status = ?';
        $params[] = $filters['status'];
        $types .= 's';
    }

    $whereSql = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

    $perPage = 20;
    $page = max(1, (int) ($filters['page'] ?? 1));
    $offset = ($page - 1) * $perPage;

    $stmt = safePrepare($mysqli, "SELECT f.*, u.username, u.email FROM feedback f LEFT JOIN users u ON u.id = f.user_id $whereSql ORDER BY FIELD(f.status, 'open', 'in_progress', 'resolved', 'closed'), f.created_at DESC LIMIT ? OFFSET ?");
    $bindTypes = $types . 'ii';
    $bindParams = array_merge($params, [$perPage, $offset]);
    $stmt->bind_param($bindTypes, ...$bindParams);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) { $items[] = $row; }

    return ['feedback' => $items, 'total' => count($items), 'page' => $page];
}

function replyFeedbackHandler($mysqli, $post) {
    ensureAdminTablesExist($mysqli);
    $feedbackId = !empty($post['feedback_id']) ? (int) $post['feedback_id'] : 0;
    $reply = trim($post['reply'] ?? '');
    $status = $post['status'] ?? 'in_progress';
    if ($reply === '') { return ['success' => false, 'message' => 'Reply is required.']; }

    $stmt = safePrepare($mysqli, "UPDATE feedback SET admin_reply = ?, admin_replied_at = CURRENT_TIMESTAMP, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->bind_param('ssi', $reply, $status, $feedbackId);
    if ($stmt->execute()) { return ['success' => true, 'message' => 'Reply sent.']; }
    return ['success' => false, 'message' => 'Unable to send reply.'];
}

function submitFeedbackHandler($mysqli, $user_id, $post) {
    ensureAdminTablesExist($mysqli);
    $subject = trim($post['subject'] ?? '');
    $message = trim($post['message'] ?? '');
    if ($subject === '' || $message === '') { return ['success' => false, 'message' => 'Subject and message are required.']; }
    $category = $post['category'] ?? 'other';

    $stmt = safePrepare($mysqli, "INSERT INTO feedback (user_id, subject, message, category) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isss', $user_id, $subject, $message, $category);
    if ($stmt->execute()) { return ['success' => true, 'message' => 'Feedback submitted.']; }
    return ['success' => false, 'message' => 'Unable to submit feedback.'];
}

function getSiteSettings($mysqli) {
    ensureAdminTablesExist($mysqli);
    $stmt = safePrepare($mysqli, "SELECT * FROM site_settings ORDER BY id ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    $settings = [];
    while ($row = $result->fetch_assoc()) { $settings[] = $row; }
    return $settings;
}

function saveSiteSettingsHandler($mysqli, $post) {
    ensureAdminTablesExist($mysqli);
    $settings = $post['settings'] ?? [];
    foreach ($settings as $key => $value) {
        $stmt = safePrepare($mysqli, "UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->bind_param('ss', $value, $key);
        $stmt->execute();
    }
    return ['success' => true, 'message' => 'Settings saved.'];
}

function getRegistrationStatus($mysqli) {
    ensureAdminTablesExist($mysqli);
    $stmt = safePrepare($mysqli, "SELECT setting_value FROM site_settings WHERE setting_key = 'allow_registration'");
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ? $row['setting_value'] === '1' : true;
}
