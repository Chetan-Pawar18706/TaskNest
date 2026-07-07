<?php
/**
 * TaskNest - Authentication & Security Module
 */

require_once __DIR__ . '/mail.php';

class Auth {
    private $mysqli;
    private $user_id;
    private $user_data;
    
    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
        $this->initSession();
    }
    
    /**
     * Initialize session with security measures
     */
    private function initSession() {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.use_strict_mode', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', !DEBUG); // Only HTTPS in production
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);
            
            session_start();
        }
        
        // Check if user is logged in
        if (isset($_SESSION['user_id'])) {
            $this->user_id = $_SESSION['user_id'];
            $this->loadUser();
        }
    }
    
    /**
     * Load user data from database
     */
    private function loadUser() {
        if (!$this->user_id) return false;
        
        $stmt = $this->mysqli->prepare("SELECT id, username, email, first_name, last_name, avatar_url, theme, role FROM users WHERE id = ? AND is_active = 1");
        if (!$stmt) return false;
        $stmt->bind_param("i", $this->user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $this->user_data = $result->fetch_assoc();
            return true;
        }
        
        return false;
    }
    
    /**
     * Register a new user
     */
    public function register($username, $email, $password, $confirm_password, $first_name = '', $last_name = '') {
        $errors = [];
        
        // Validation
        if (strlen($username) < 3 || strlen($username) > 50) {
            $errors[] = 'Username must be 3-50 characters';
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address';
        }
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }
        
        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }
        
        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain uppercase letter and number';
        }
        
        // Check if username/email exists
        $stmt = $this->mysqli->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Username or email already registered';
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Insert user
        $password_hash = password_hash($password, PASSWORD_ARGON2ID, ['memory_cost' => 65536, 'time_cost' => 4, 'threads' => 3]);
        
        $stmt = $this->mysqli->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $email, $password_hash, $first_name, $last_name);
        
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            
            // Create settings for new user
            $stmt_settings = $this->mysqli->prepare("INSERT INTO settings (user_id) VALUES (?)");
            $stmt_settings->bind_param("i", $user_id);
            $stmt_settings->execute();
            
            // Log activity
            $this->logActivity($user_id, 'user_registered', 'user', $user_id, 'New user registration');
            
            return ['success' => true, 'message' => 'Registration successful. Please login.'];
        }
        
        return ['success' => false, 'errors' => ['Registration failed. Please try again.']];
    }
    
    /**
     * Login user
     */
    public function login($email, $password, $remember_me = false) {
        $errors = [];
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required';
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Find user
        $stmt = $this->mysqli->prepare("SELECT id, password_hash, is_active FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows !== 1) {
            $errors[] = 'Invalid email or password';
            return ['success' => false, 'errors' => $errors];
        }
        
        $user = $result->fetch_assoc();
        
        if (!$user['is_active']) {
            $errors[] = 'This account has been deactivated';
            return ['success' => false, 'errors' => $errors];
        }
        
        if (!password_verify($password, $user['password_hash'])) {
            $errors[] = 'Invalid email or password';
            return ['success' => false, 'errors' => $errors];
        }
        
        // Regenerate session
        session_regenerate_id(true);
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['login_time'] = time();
        
        // Generate CSRF token
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        // Remember me functionality
        if ($remember_me) {
            $remember_token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', time() + REMEMBER_ME_DURATION);
            
            $stmt_remember = $this->mysqli->prepare("INSERT INTO sessions (user_id, remember_token, expires_at) VALUES (?, ?, ?)");
            $stmt_remember->bind_param("iss", $user['id'], $remember_token, $expires_at);
            $stmt_remember->execute();
            
            setcookie('remember_token', $remember_token, time() + REMEMBER_ME_DURATION, '/', '', !DEBUG, true);
        }
        
        // Load user data
        $this->user_id = $user['id'];
        $this->loadUser();
        
        // Log activity
        $this->logActivity($user['id'], 'user_login', 'user', $user['id'], 'User login');
        
        return ['success' => true, 'message' => 'Login successful'];
    }
    
    /**
     * Logout user
     */
    public function logout() {
        if ($this->isLoggedIn()) {
            $this->logActivity($this->user_id, 'user_logout', 'user', $this->user_id, 'User logout');
        }
        
        $_SESSION = [];
        session_destroy();
        
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', !DEBUG, true);
        }
        
        return true;
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($this->user_id) && !empty($this->user_data);
    }
    
    /**
     * Get current user ID
     */
    public function getUserId() {
        return $this->user_id ?? null;
    }
    
    /**
     * Get current user data
     */
    public function getUser() {
        return $this->user_data ?? null;
    }
    
    /**
     * Request password reset
     */
    public function requestPasswordReset($email) {
        $stmt = $this->mysqli->prepare("SELECT id FROM users WHERE email = ? AND is_active = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['success' => true, 'message' => 'If email exists, reset link has been sent'];
        }
        
        $user = $result->fetch_assoc();
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', time() + 3600); // 1 hour
        
        $stmt_reset = $this->mysqli->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt_reset->bind_param("iss", $user['id'], $token, $expires_at);
        
        if ($stmt_reset->execute()) {
            $reset_link = SITE_URL . '/auth/reset-password.php?token=' . urlencode($token);
            
            sendPasswordResetEmail($email, $reset_link);
            
            return ['success' => true, 'message' => 'If email exists, reset link has been sent'];
        }
        
        return ['success' => false, 'message' => 'Failed to process request'];
    }
    
    /**
     * Reset password with token
     */
    public function resetPassword($token, $new_password, $confirm_password) {
        $errors = [];
        
        if (strlen($new_password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }
        
        if (!preg_match('/[A-Z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
            $errors[] = 'Password must contain uppercase letter and number';
        }
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        // Validate token
        $stmt = $this->mysqli->prepare("
            SELECT user_id FROM password_resets 
            WHERE token = ? AND is_used = 0 AND expires_at > NOW()
        ");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $errors[] = 'Invalid or expired reset token';
            return ['success' => false, 'errors' => $errors];
        }
        
        $reset_record = $result->fetch_assoc();
        $user_id = $reset_record['user_id'];
        
        // Update password
        $password_hash = password_hash($new_password, PASSWORD_ARGON2ID, ['memory_cost' => 65536, 'time_cost' => 4, 'threads' => 3]);
        
        $stmt_update = $this->mysqli->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt_update->bind_param("si", $password_hash, $user_id);
        
        if ($stmt_update->execute()) {
            // Mark token as used
            $stmt_mark = $this->mysqli->prepare("UPDATE password_resets SET is_used = 1 WHERE token = ?");
            $stmt_mark->bind_param("s", $token);
            $stmt_mark->execute();
            
            $this->logActivity($user_id, 'password_reset', 'user', $user_id, 'Password reset successful');
            
            return ['success' => true, 'message' => 'Password reset successful. Please login.'];
        }
        
        return ['success' => false, 'errors' => ['Password reset failed']];
    }
    
    /**
     * Generate CSRF token
     */
    public function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    public function verifyCsrfToken($token) {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            return false;
        }
        return true;
    }
    
    /**
     * ==================== TWO-FACTOR AUTHENTICATION ====================
     */
    
    /**
     * Generate a new TOTP secret (Base32 encoded)
     */
    public function generateTwoFactorSecret() {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 32; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        return $secret;
    }
    
    /**
     * Generate TOTP URI for QR code (otpauth://)
     */
    public function getTwoFactorUri($secret) {
        $user = $this->getUser();
        $label = urlencode($user['email']);
        $issuer = urlencode(SITE_NAME);
        return "otpauth://totp/{$issuer}:{$label}?secret={$secret}&issuer={$issuer}&digits=6&period=30";
    }
    
    /**
     * Generate current TOTP code from secret
     */
    public function generateTotpCode($secret) {
        $time = floor(time() / 30);
        $secretKey = $this->base32Decode($secret);
        
        // Pack time into 8-byte buffer
        $timeBytes = pack('N*', 0) . pack('N*', $time);
        
        // HMAC-SHA1
        $hmac = hash_hmac('sha1', $timeBytes, $secretKey, true);
        
        // Dynamic truncation
        $offset = ord($hmac[19]) & 0x0f;
        $code = (
            ((ord($hmac[$offset]) & 0x7f) << 24) |
            ((ord($hmac[$offset + 1]) & 0xff) << 16) |
            ((ord($hmac[$offset + 2]) & 0xff) << 8) |
            (ord($hmac[$offset + 3]) & 0xff)
        ) % 1000000;
        
        return str_pad((string) $code, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Verify a TOTP code (checks current and ±1 time window)
     */
    public function verifyTotpCode($secret, $code) {
        $code = preg_replace('/\s+/', '', $code);
        if (strlen($code) !== 6 || !ctype_digit($code)) {
            return false;
        }
        
        // Check current time and ±1 window (90 seconds total)
        for ($i = -1; $i <= 1; $i++) {
            $time = floor(time() / 30) + $i;
            $secretKey = $this->base32Decode($secret);
            
            $timeBytes = pack('N*', 0) . pack('N*', $time);
            $hmac = hash_hmac('sha1', $timeBytes, $secretKey, true);
            
            $offset = ord($hmac[19]) & 0x0f;
            $expectedCode = (
                ((ord($hmac[$offset]) & 0x7f) << 24) |
                ((ord($hmac[$offset + 1]) & 0xff) << 16) |
                ((ord($hmac[$offset + 2]) & 0xff) << 8) |
                (ord($hmac[$offset + 3]) & 0xff)
            ) % 1000000;
            
            $expectedStr = str_pad((string) $expectedCode, 6, '0', STR_PAD_LEFT);
            if (hash_equals($expectedStr, $code)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Base32 decode
     */
    private function base32Decode($input) {
        $map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $input = strtoupper($input);
        $input = rtrim($input, '=');
        
        $buffer = 0;
        $bitsLeft = 0;
        $output = '';
        
        for ($i = 0, $len = strlen($input); $i < $len; $i++) {
            $val = strpos($map, $input[$i]);
            if ($val === false) continue;
            
            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;
            
            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xff);
            }
        }
        
        return $output;
    }
    
    /**
     * Generate backup codes (8 codes, each 8 chars)
     */
    public function generateBackupCodes() {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $code = bin2hex(random_bytes(4));
            $codes[] = $code;
        }
        return $codes;
    }
    
    /**
     * Hash backup codes for storage
     */
    public function hashBackupCodes($codes) {
        return array_map(fn($code) => password_hash($code, PASSWORD_DEFAULT), $codes);
    }
    
    /**
     * Verify a backup code
     */
    public function verifyBackupCode($userId, $code) {
        $stmt = $this->mysqli->prepare("SELECT two_factor_backup_codes FROM settings WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if (empty($result['two_factor_backup_codes'])) {
            return false;
        }
        
        $hashedCodes = json_decode($result['two_factor_backup_codes'], true);
        if (!is_array($hashedCodes)) {
            return false;
        }
        
        foreach ($hashedCodes as $index => $hashedCode) {
            if (password_verify($code, $hashedCode)) {
                // Remove used backup code
                unset($hashedCodes[$index]);
                $stmtUpdate = $this->mysqli->prepare("UPDATE settings SET two_factor_backup_codes = ? WHERE user_id = ?");
                $newCodes = json_encode(array_values($hashedCodes));
                $stmtUpdate->bind_param("si", $newCodes, $userId);
                $stmtUpdate->execute();
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if 2FA is enabled for user
     */
    public function isTwoFactorEnabled($userId) {
        $stmt = $this->mysqli->prepare("SELECT two_factor_enabled FROM settings WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return isset($result['two_factor_enabled']) && $result['two_factor_enabled'] == 1;
    }
    
    /**
     * Get 2FA secret for user
     */
    public function getTwoFactorSecret($userId) {
        $stmt = $this->mysqli->prepare("SELECT two_factor_secret FROM settings WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['two_factor_secret'] ?? null;
    }
    
    /**
     * Enable 2FA for user
     */
    public function enableTwoFactor($userId, $secret, $backupCodes) {
        $hashedCodes = $this->hashBackupCodes($backupCodes);
        $codesJson = json_encode($hashedCodes);
        
        $stmt = $this->mysqli->prepare("UPDATE settings SET two_factor_enabled = 1, two_factor_secret = ?, two_factor_backup_codes = ? WHERE user_id = ?");
        $stmt->bind_param("ssi", $secret, $codesJson, $userId);
        return $stmt->execute();
    }
    
    /**
     * Disable 2FA for user
     */
    public function disableTwoFactor($userId) {
        $stmt = $this->mysqli->prepare("UPDATE settings SET two_factor_enabled = 0, two_factor_secret = NULL, two_factor_backup_codes = NULL WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
    
    /**
     * Set 2FA pending verification in session
     */
    public function setTwoFactorPending($userId) {
        $_SESSION['two_factor_pending'] = true;
        $_SESSION['two_factor_user_id'] = $userId;
    }
    
    /**
     * Check if 2FA verification is pending
     */
    public function isTwoFactorPending() {
        return isset($_SESSION['two_factor_pending']) && $_SESSION['two_factor_pending'] === true;
    }
    
    /**
     * Complete 2FA verification
     */
    public function completeTwoFactorVerification() {
        unset($_SESSION['two_factor_pending']);
        $userId = $_SESSION['two_factor_user_id'] ?? null;
        unset($_SESSION['two_factor_user_id']);
        
        if ($userId) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['login_time'] = time();
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $this->user_id = $userId;
            $this->loadUser();
            return true;
        }
        
        return false;
    }
    
    /**
     * Log activity
     */
    public function logActivity($user_id, $action, $entity_type, $entity_id, $description) {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $stmt = $this->mysqli->prepare("
            INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ississs", $user_id, $action, $entity_type, $entity_id, $description, $ip_address, $user_agent);
        $stmt->execute();
    }
}

// Initialize auth
$auth = new Auth($mysqli);
