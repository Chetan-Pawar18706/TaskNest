<?php
/**
 * TaskNest - Admin Setup Script
 * Run this once to add the role column and create an admin user.
 * DELETE THIS FILE after running it for security.
 */

require_once __DIR__ . '/../config/db.php';

echo "<h2>TaskNest Admin Setup</h2>";

// Add role column if it doesn't exist
$check_col = $mysqli->query("SHOW COLUMNS FROM users LIKE 'role'");
if ($check_col->num_rows === 0) {
    $mysqli->query("ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user' AFTER is_active");
    echo "<p>✓ Added 'role' column to users table.</p>";
} else {
    echo "<p>✓ 'role' column already exists.</p>";
}

// Admin credentials
$admin_username = 'admin';
$admin_email = 'admin@tasknest.com';
$admin_password = 'Admin@1234';
$admin_first_name = 'Admin';
$admin_last_name = 'User';

// Check if admin exists
$stmt = $mysqli->prepare("SELECT id, role FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $admin_username, $admin_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if ($user['role'] !== 'admin') {
        $update = $mysqli->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
        $update->bind_param("i", $user['id']);
        $update->execute();
        echo "<p>✓ Updated existing user '{$admin_username}' to admin role.</p>";
    } else {
        echo "<p>✓ User '{$admin_username}' is already an admin.</p>";
    }
} else {
    // Create new admin user
    $password_hash = password_hash($admin_password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3
    ]);

    $stmt = $mysqli->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name, role) VALUES (?, ?, ?, ?, ?, 'admin')");
    $stmt->bind_param("sssss", $admin_username, $admin_email, $password_hash, $admin_first_name, $admin_last_name);

    if ($stmt->execute()) {
        echo "<p>✓ Created admin user:</p>";
        echo "<ul>";
        echo "<li><strong>Username:</strong> {$admin_username}</li>";
        echo "<li><strong>Email:</strong> {$admin_email}</li>";
        echo "<li><strong>Password:</strong> {$admin_password}</li>";
        echo "</ul>";
    } else {
        echo "<p>✗ Failed to create admin user.</p>";
    }
}

echo "<hr>";
echo "<p><strong>⚠️ IMPORTANT:</strong> Delete this file after running it for security.</p>";
echo "<p><a href='" . SITE_URL . "/login.php'>Go to Login</a></p>";
?>
