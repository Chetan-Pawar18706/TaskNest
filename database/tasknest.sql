-- TaskNest Database Schema
-- Combined schema for Phase 1, Phase 2, and Phase 3

-- CREATE DATABASE IF NOT EXISTS tasknest;
-- USE tasknest;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(120) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    avatar_url VARCHAR(255),
    phone VARCHAR(20),
    bio TEXT,
    timezone VARCHAR(50) DEFAULT 'UTC',
    theme ENUM('light', 'dark') DEFAULT 'light',
    is_active TINYINT(1) DEFAULT 1,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password Resets Table
CREATE TABLE IF NOT EXISTS password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    is_used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessions Table
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token CHAR(64) NOT NULL UNIQUE,
    remember_token CHAR(64) DEFAULT NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    last_activity DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_sessions_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_user_id(user_id),
    INDEX idx_session_token(session_token),
    INDEX idx_expires_at(expires_at)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- Settings Table
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    notifications_enabled TINYINT(1) DEFAULT 1,
    email_on_reminder TINYINT(1) DEFAULT 1,
    email_on_collaboration TINYINT(1) DEFAULT 1,
    email_on_digest TINYINT(1) DEFAULT 1,
    two_factor_enabled TINYINT(1) DEFAULT 0,
    two_factor_secret VARCHAR(255),
    two_factor_backup_codes TEXT DEFAULT NULL,
    language VARCHAR(10) DEFAULT 'en',
    date_format VARCHAR(20) DEFAULT 'Y-m-d',
    time_format VARCHAR(10) DEFAULT '24h',
    items_per_page INT DEFAULT 20,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Log Table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),
    INDEX idx_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Phase 2 additions
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notifications_user (user_id, is_read),
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS calendar_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    event_date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_calendar_user_date (user_id, event_date),
    CONSTRAINT fk_calendar_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE activity_logs
    ADD INDEX idx_activity_user_created (user_id, created_at DESC);

-- Phase 3: Tasks module
CREATE TABLE IF NOT EXISTS task_categories (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tasks (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS task_activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    task_id INT DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_task_activity (task_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create indexes for common queries
ALTER TABLE users ADD INDEX idx_active (is_active);
ALTER TABLE password_resets ADD INDEX idx_used (is_used);
ALTER TABLE sessions ADD INDEX idx_active_sessions (user_id, expires_at);

-- Phase 4: Notes module
CREATE TABLE IF NOT EXISTS note_categories (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notes (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS note_images (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Phase 5: Expenses module
CREATE TABLE IF NOT EXISTS expense_categories (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS expenses (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS budgets (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Phase 6: Documents module
CREATE TABLE IF NOT EXISTS document_categories (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS documents (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Phase 7: Borrow module
CREATE TABLE IF NOT EXISTS borrow_items (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Phase 8: Habits, Goals, Shopping
CREATE TABLE IF NOT EXISTS habits (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS habit_logs (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS goals (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS shopping (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- TaskNest - Password Manager Tables

CREATE TABLE IF NOT EXISTS password_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(20) DEFAULT '#6366f1',
    is_deleted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_pwd_cat_user (user_id, name),
    INDEX idx_pc_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS saved_passwords (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    username VARCHAR(255) DEFAULT NULL,
    encrypted_password TEXT NOT NULL,
    url VARCHAR(500) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    category_id INT DEFAULT NULL,
    is_favorite TINYINT(1) DEFAULT 0,
    is_deleted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES password_categories(id) ON DELETE SET NULL,
    INDEX idx_sp_user_id (user_id, is_deleted),
    INDEX idx_sp_category (category_id),
    INDEX idx_sp_favorite (user_id, is_favorite)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- TaskNest - Reminders System
-- Run this SQL to create the reminders table

CREATE TABLE IF NOT EXISTS reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    reminder_date DATE NOT NULL,
    reminder_time TIME NOT NULL,
    repeat_type ENUM('none', 'daily', 'weekly', 'monthly', 'yearly') DEFAULT 'none',
    repeat_days VARCHAR(50),
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    category VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    email_sent TINYINT(1) DEFAULT 0,
    last_notified DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_date (user_id, reminder_date, is_active),
    INDEX idx_reminder_time (reminder_date, reminder_time, is_active, email_sent)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Feedback Table
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    category ENUM('bug', 'feature', 'improvement', 'other') DEFAULT 'other',
    status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
    viewed_by_admin TINYINT(1) DEFAULT 0,
    admin_reply TEXT DEFAULT NULL,
    admin_replied_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_feedback_status (status),
    INDEX idx_feedback_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Site Settings Table
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    setting_type VARCHAR(20) DEFAULT 'string',
    description TEXT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- SEED DATA
-- ============================================================
-- Password for all demo users: Password1

-- USERS
INSERT INTO users (username, email, password_hash, first_name, last_name, avatar_url, phone, bio, timezone, theme, is_active, role) VALUES
('demo', 'demo@tasknest.local', '$2y$10$j3BqOJyqDqdYW2rBX9o9p.cXP4buE5QqkDjSMZfETRBVGHp8hap0q', 'Alex', 'Morgan', NULL, '+1-555-0101', 'Productivity enthusiast who loves staying organized.', 'America/New_York', 'light', 1, 'user'),
('jane', 'jane@tasknest.local', '$2y$10$j3BqOJyqDqdYW2rBX9o9p.cXP4buE5QqkDjSMZfETRBVGHp8hap0q', 'Jane', 'Cooper', NULL, '+1-555-0202', 'Freelance designer managing multiple projects.', 'America/Chicago', 'dark', 1, 'user'),
('admin', 'admin@tasknest.local', '$2y$10$j3BqOJyqDqdYW2rBX9o9p.cXP4buE5QqkDjSMZfETRBVGHp8hap0q', 'Admin', 'User', NULL, '+1-555-0000', 'System administrator.', 'UTC', 'light', 1, 'admin');

-- SETTINGS
INSERT INTO settings (user_id, notifications_enabled, email_on_reminder, email_on_collaboration, email_on_digest, two_factor_enabled, language, date_format, time_format, items_per_page) VALUES
(1, 1, 1, 0, 1, 0, 'en', 'Y-m-d', '24h', 20),
(2, 1, 1, 0, 0, 0, 'en', 'M/d/Y', '12h', 15),
(3, 1, 1, 1, 1, 0, 'en', 'Y-m-d', '24h', 20);

-- TASK CATEGORIES
INSERT INTO task_categories (user_id, name, color, icon) VALUES
(1, 'Work', '#3b82f6', 'task'),
(1, 'Personal', '#10b981', 'task'),
(1, 'Health', '#ef4444', 'task'),
(1, 'Learning', '#8b5cf6', 'task'),
(1, 'Finance', '#f59e0b', 'task');

-- TASKS
INSERT INTO tasks (user_id, title, description, status, priority, category_id, due_date, reminder_datetime, completed, completed_at) VALUES
(1, 'Finish quarterly report', 'Compile data from all departments and write the executive summary.', 'In Progress', 'High', 1, DATE_ADD(CURDATE(), INTERVAL 3 DAY), DATE_ADD(CURDATE(), INTERVAL 2 DAY), 0, NULL),
(1, 'Buy groceries', 'Milk, eggs, bread, chicken, vegetables, rice.', 'Pending', 'Medium', 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), NULL, 0, NULL),
(1, 'Morning jog', 'Run 5K in the park.', 'Completed', 'Low', 3, CURDATE(), NULL, 1, DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(1, 'Read PHP documentation', 'Study PDO and prepared statements chapter.', 'Pending', 'Medium', 4, DATE_ADD(CURDATE(), INTERVAL 7 DAY), NULL, 0, NULL),
(1, 'Pay electricity bill', 'Due by end of month. Check amount on portal.', 'Pending', 'High', 5, DATE_ADD(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 4 DAY), 0, NULL),
(1, 'Update project proposal', 'Add new budget section and timeline.', 'In Progress', 'High', 1, DATE_ADD(CURDATE(), INTERVAL 2 DAY), NULL, 0, NULL),
(1, 'Schedule dentist appointment', 'Call Dr. Smith office during business hours.', 'Pending', 'Low', 3, DATE_ADD(CURDATE(), INTERVAL 10 DAY), NULL, 0, NULL),
(1, 'Clean the apartment', 'Vacuum, mop floors, clean bathroom.', 'Pending', 'Medium', 2, DATE_ADD(CURDATE(), INTERVAL 2 DAY), NULL, 0, NULL),
(1, 'Submit tax documents', 'Gather all receipts and invoices for the accountant.', 'Completed', 'High', 5, DATE_SUB(CURDATE(), INTERVAL 1 DAY), NULL, 1, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 'Review pull requests', 'Check 3 open PRs on the team repository.', 'Pending', 'Medium', 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), NULL, 0, NULL),
(1, 'Plan weekend trip', 'Research hotels and activities for Saturday-Sunday.', 'Pending', 'Low', 2, DATE_ADD(CURDATE(), INTERVAL 4 DAY), NULL, 0, NULL),
(1, 'Fix login bug', 'Users getting 500 error on form submit.', 'Completed', 'High', 1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), NULL, 1, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 'Yoga class', 'Evening session at the community center.', 'Pending', 'Low', 3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), DATE_ADD(CURDATE(), INTERVAL 12 HOUR), 0, NULL),
(1, 'Write blog post', 'Draft article about productivity tools.', 'In Progress', 'Medium', 4, DATE_ADD(CURDATE(), INTERVAL 6 DAY), NULL, 0, NULL),
(1, 'Organize digital files', 'Sort downloads folder, archive old projects.', 'Pending', 'Low', 2, DATE_ADD(CURDATE(), INTERVAL 14 DAY), NULL, 0, NULL);

-- NOTE CATEGORIES
INSERT INTO note_categories (user_id, name, color) VALUES
(1, 'Ideas', '#3b82f6'),
(1, 'Meeting Notes', '#10b981'),
(1, 'Recipes', '#f59e0b'),
(1, 'Travel', '#8b5cf6');

-- NOTES
INSERT INTO notes (user_id, title, content, category_id, is_pinned, is_archived) VALUES
(1, 'App Feature Ideas', '## Feature Brainstorm\n- Dark mode toggle\n- Export to PDF\n- Collaborative editing\n- Keyboard shortcuts\n- Mobile responsive improvements', 1, 1, 0),
(1, 'Sprint Planning - Week 12', '## Attendees\n- Alex, Sarah, Mike\n\n## Decisions\n1. Prioritize bug fixes over new features\n2. Deploy hotfix by Wednesday\n3. Schedule code review for Thursday', 2, 0, 0),
(1, 'Pasta Carbonara Recipe', '## Ingredients\n- 200g spaghetti\n- 100g pancetta\n- 2 eggs\n- 50g parmesan\n- Black pepper\n\n## Steps\n1. Cook pasta al dente\n2. Fry pancetta until crispy\n3. Mix eggs with parmesan\n4. Combine everything off heat\n5. Season with pepper', 3, 0, 0),
(1, 'Tokyo Trip Notes', '## Must Visit\n- Shibuya Crossing\n- Senso-ji Temple\n- Akihabara\n- Tsukiji Fish Market\n- Mt. Fuji day trip\n\n## Budget\n- Flights: $800\n- Hotel: $120 (5 nights)\n- Daily: $50', 4, 1, 0),
(1, 'Book Recommendations', '- "Atomic Habits" by James Clear\n- "Deep Work" by Cal Newport\n- "The Pragmatic Programmer"\n- "Clean Code" by Robert Martin\n- "Thinking, Fast and Slow"', 1, 0, 0),
(1, 'Weekly Standup Template', '## What I did last week\n- \n\n## What I plan to do this week\n- \n\n## Blockers\n- ', 2, 0, 0),
(1, 'Old Meeting Notes', 'Discussion about Q2 roadmap and budget allocation.', 2, 0, 1);

-- EXPENSE CATEGORIES
INSERT INTO expense_categories (user_id, name, color, type) VALUES
(1, 'Salary', '#10b981', 'income'),
(1, 'Freelance', '#3b82f6', 'income'),
(1, 'Food & Dining', '#ef4444', 'expense'),
(1, 'Transportation', '#f59e0b', 'expense'),
(1, 'Entertainment', '#8b5cf6', 'expense'),
(1, 'Utilities', '#6366f1', 'expense'),
(1, 'Shopping', '#ec4899', 'expense'),
(1, 'Health', '#14b8a6', 'expense'),
(1, 'Education', '#0ea5e9', 'expense'),
(1, 'Investment', '#22c55e', 'income');

-- EXPENSES
INSERT INTO expenses (user_id, title, amount, type, category_id, transaction_date, notes, is_recurring, recurring_period) VALUES
(1, 'Monthly Salary', 5500.00, 'income', 1, DATE_FORMAT(CURDATE(), '%Y-%m-01'), 'June salary', 1, 'monthly'),
(1, 'Freelance Project', 1200.00, 'income', 2, DATE_ADD(CURDATE(), INTERVAL -5 DAY), 'Website redesign for client', 0, NULL),
(1, 'Dividend Payment', 85.50, 'income', 10, DATE_ADD(CURDATE(), INTERVAL -10 DAY), 'Quarterly dividend', 0, NULL),
(1, 'Grocery Store', 67.30, 'expense', 3, DATE_ADD(CURDATE(), INTERVAL -1 DAY), 'Weekly groceries', 0, NULL),
(1, 'Electric Bill', 120.00, 'expense', 6, DATE_ADD(CURDATE(), INTERVAL -3 DAY), 'Monthly electricity', 1, 'monthly'),
(1, 'Internet Bill', 59.99, 'expense', 6, DATE_ADD(CURDATE(), INTERVAL -3 DAY), 'Monthly broadband', 1, 'monthly'),
(1, 'Gas Station', 45.00, 'expense', 4, DATE_ADD(CURDATE(), INTERVAL -2 DAY), 'Full tank', 0, NULL),
(1, 'Netflix Subscription', 15.99, 'expense', 5, DATE_FORMAT(CURDATE(), '%Y-%m-01'), 'Monthly streaming', 1, 'monthly'),
(1, 'Restaurant Dinner', 42.50, 'expense', 3, DATE_ADD(CURDATE(), INTERVAL -4 DAY), 'Dinner with friends', 0, NULL),
(1, 'New Headphones', 89.99, 'expense', 7, DATE_ADD(CURDATE(), INTERVAL -6 DAY), 'Sony WH-1000XM4', 0, NULL),
(1, 'Gym Membership', 49.99, 'expense', 8, DATE_FORMAT(CURDATE(), '%Y-%m-01'), 'Monthly gym', 1, 'monthly'),
(1, 'Online Course', 29.99, 'expense', 9, DATE_ADD(CURDATE(), INTERVAL -8 DAY), 'Advanced PHP course on Udemy', 0, NULL),
(1, 'Coffee Shop', 5.75, 'expense', 3, DATE_ADD(CURDATE(), INTERVAL -1 DAY), 'Morning coffee', 0, NULL),
(1, 'Monthly Salary', 5500.00, 'income', 1, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01'), 'May salary', 1, 'monthly'),
(1, 'Freelance Work', 800.00, 'income', 2, DATE_SUB(CURDATE(), INTERVAL 15 DAY), 'Logo design', 0, NULL),
(1, 'Grocery Store', 210.45, 'expense', 3, DATE_SUB(CURDATE(), INTERVAL 12 DAY), 'Monthly groceries', 0, NULL),
(1, 'Electric Bill', 135.00, 'expense', 6, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-05'), 'Monthly electricity', 1, 'monthly'),
(1, 'Internet Bill', 59.99, 'expense', 6, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-05'), 'Monthly broadband', 1, 'monthly'),
(1, 'Car Insurance', 180.00, 'expense', 4, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-10'), 'Quarterly premium', 0, NULL),
(1, 'Movie Tickets', 32.00, 'expense', 5, DATE_SUB(CURDATE(), INTERVAL 18 DAY), 'Weekend movie', 0, NULL),
(1, 'Clothing Store', 125.00, 'expense', 7, DATE_SUB(CURDATE(), INTERVAL 20 DAY), 'Summer clothes', 0, NULL),
(1, 'Monthly Salary', 5500.00, 'income', 1, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m-01'), 'April salary', 1, 'monthly'),
(1, 'Electric Bill', 98.00, 'expense', 6, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m-05'), 'Lower usage', 1, 'monthly'),
(1, 'Internet Bill', 59.99, 'expense', 6, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 2 MONTH), '%Y-%m-05'), 'Monthly broadband', 1, 'monthly'),
(1, 'Grocery Store', 185.30, 'expense', 3, DATE_SUB(CURDATE(), INTERVAL 45 DAY), 'Monthly groceries', 0, NULL),
(1, 'Doctor Visit', 150.00, 'expense', 8, DATE_SUB(CURDATE(), INTERVAL 50 DAY), 'Annual checkup', 0, NULL),
(1, 'Book Purchase', 24.99, 'expense', 9, DATE_SUB(CURDATE(), INTERVAL 55 DAY), 'Clean Architecture', 0, NULL);

-- BUDGETS
INSERT INTO budgets (user_id, category_id, amount, period, start_date, end_date, is_active) VALUES
(1, 3, 400.00, 'monthly', DATE_FORMAT(CURDATE(), '%Y-%m-01'), NULL, 1),
(1, 4, 200.00, 'monthly', DATE_FORMAT(CURDATE(), '%Y-%m-01'), NULL, 1),
(1, 5, 100.00, 'monthly', DATE_FORMAT(CURDATE(), '%Y-%m-01'), NULL, 1),
(1, 6, 200.00, 'monthly', DATE_FORMAT(CURDATE(), '%Y-%m-01'), NULL, 1);

-- DOCUMENT CATEGORIES
INSERT INTO document_categories (user_id, name, color) VALUES
(1, 'Identity', '#3b82f6'),
(1, 'Finance', '#10b981'),
(1, 'Work', '#f59e0b'),
(1, 'Medical', '#ef4444');

-- DOCUMENTS
INSERT INTO documents (user_id, title, description, filename, original_name, file_size, mime_type, category_id, expiry_date, reminder_date, is_important) VALUES
(1, 'Passport', 'International passport for travel', 'a1b2c3d4e5f6.pdf', 'passport.pdf', 245000, 'application/pdf', 1, '2030-05-15', '2029-11-15', 1),
(1, 'Drivers License', 'State driving license', 'f6e5d4c3b2a1.jpg', 'drivers_license.jpg', 180000, 'image/jpeg', 1, '2027-03-20', '2027-01-20', 1),
(1, 'Tax Return 2025', 'Annual income tax return filing', 'g7h8i9j0k1l2.pdf', 'tax_return_2025.pdf', 520000, 'application/pdf', 2, NULL, NULL, 0),
(1, 'Employment Contract', 'Current employment agreement', 'm3n4o5p6q7r8.pdf', 'employment_contract.pdf', 340000, 'application/pdf', 3, NULL, NULL, 1),
(1, 'Vaccination Record', 'COVID and flu vaccination records', 's9t0u1v2w3x4.pdf', 'vaccination_record.pdf', 150000, 'application/pdf', 4, NULL, NULL, 0),
(1, 'Health Insurance Card', 'Current health insurance details', 'y5z6a7b8c9d0.jpg', 'health_insurance.jpg', 95000, 'image/jpeg', 4, '2026-12-31', '2026-11-30', 1);

-- BORROW ITEMS
INSERT INTO borrow_items (user_id, title, description, type, item_type, amount, person_name, person_contact, borrow_date, return_date, actual_return_date, status) VALUES
(1, 'Laptop charger', 'MacBook Pro USB-C charger', 'lent', 'item', 0.00, 'Mike Johnson', 'mike@email.com', DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 9 DAY), NULL, 'pending'),
(1, 'Emergency cash', 'Borrowed for car repair', 'borrowed', 'money', 500.00, 'Sarah Wilson', 'sarah@email.com', DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 20 DAY), NULL, 'pending'),
(1, 'Book - Clean Code', 'Programming best practices book', 'lent', 'item', 0.00, 'Tom Lee', 'tom@email.com', DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_SUB(CURDATE(), INTERVAL 1 DAY), NULL, 'pending'),
(1, 'Moving boxes', 'Borrowed for apartment move', 'borrowed', 'item', 0.00, 'Lisa Chen', 'lisa@email.com', DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_SUB(CURDATE(), INTERVAL 16 DAY), DATE_SUB(CURDATE(), INTERVAL 18 DAY), 'returned'),
(1, 'Small loan', 'Help with deposit payment', 'lent', 'money', 200.00, 'David Park', 'david@email.com', DATE_SUB(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 23 DAY), NULL, 'pending'),
(1, 'Power drill', 'Borrowed for home improvement project', 'borrowed', 'item', 0.00, 'Rachel Kim', 'rachel@email.com', DATE_SUB(CURDATE(), INTERVAL 3 DAY), DATE_ADD(CURDATE(), INTERVAL 4 DAY), NULL, 'pending');

-- HABITS
INSERT INTO habits (user_id, name, description, frequency, target_count, color, is_active) VALUES
(1, 'Morning Meditation', '10 minutes of mindfulness meditation', 'daily', 1, '#8b5cf6', 1),
(1, 'Exercise', 'At least 30 minutes of physical activity', 'daily', 1, '#ef4444', 1),
(1, 'Read 30 Pages', 'Read at least 30 pages of a book', 'daily', 1, '#3b82f6', 1),
(1, 'Drink 8 Glasses of Water', 'Stay hydrated throughout the day', 'daily', 8, '#06b6d4', 1),
(1, 'No Social Media', 'Avoid social media during work hours', 'daily', 1, '#f59e0b', 1),
(1, 'Practice Guitar', 'Play guitar for 20 minutes', 'daily', 1, '#ec4899', 1),
(1, 'Weekly Review', 'Review goals and plan the week ahead', 'weekly', 1, '#10b981', 1);

-- HABIT LOGS
INSERT INTO habit_logs (user_id, habit_id, log_date, count_value, notes) VALUES
(1, 1, CURDATE(), 1, 'Felt very focused after'),
(1, 2, CURDATE(), 1, '30 min run'),
(1, 4, CURDATE(), 6, 'Almost there'),
(1, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 1, NULL),
(1, 2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 1, 'Gym workout'),
(1, 3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 1, 'Read 35 pages'),
(1, 4, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 8, 'Met goal!'),
(1, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 1, NULL),
(1, 3, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 1, 'Finished chapter 5'),
(1, 4, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 7, NULL),
(1, 5, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 1, 'Stayed focused'),
(1, 1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 1, NULL),
(1, 2, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 1, 'Yoga session'),
(1, 3, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 1, NULL),
(1, 4, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 8, 'Met goal!'),
(1, 1, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 1, NULL),
(1, 2, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 1, 'Morning jog'),
(1, 4, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 5, NULL),
(1, 1, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 1, NULL),
(1, 3, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 1, 'Read 40 pages'),
(1, 4, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 8, 'Met goal!'),
(1, 1, DATE_SUB(CURDATE(), INTERVAL 6 DAY), 1, NULL),
(1, 2, DATE_SUB(CURDATE(), INTERVAL 6 DAY), 1, 'Cycling'),
(1, 3, DATE_SUB(CURDATE(), INTERVAL 6 DAY), 1, NULL),
(1, 4, DATE_SUB(CURDATE(), INTERVAL 6 DAY), 7, NULL);

-- GOALS
INSERT INTO goals (user_id, title, description, category, target_value, current_value, unit, start_date, due_date, completed_date, status) VALUES
(1, 'Run a Half Marathon', 'Complete a 21.1K half marathon race', 'Fitness', 21.1, 12.5, 'km', DATE_SUB(CURDATE(), INTERVAL 60 DAY), DATE_ADD(CURDATE(), INTERVAL 90 DAY), NULL, 'active'),
(1, 'Read 24 Books This Year', 'Average 2 books per month for the full year', 'Learning', 24, 11, 'books', '2026-01-01', '2026-12-31', NULL, 'active'),
(1, 'Save $5000 Emergency Fund', 'Build up emergency savings account', 'Finance', 5000, 3200, 'USD', DATE_SUB(CURDATE(), INTERVAL 90 DAY), DATE_ADD(CURDATE(), INTERVAL 180 DAY), NULL, 'active'),
(1, 'Learn Spanish', 'Reach conversational fluency level', 'Learning', 100, 35, 'lessons', DATE_SUB(CURDATE(), INTERVAL 30 DAY), DATE_ADD(CURDATE(), INTERVAL 335 DAY), NULL, 'active'),
(1, 'Lose 5kg', 'Reach target weight through diet and exercise', 'Health', 5, 2.3, 'kg', DATE_SUB(CURDATE(), INTERVAL 45 DAY), DATE_ADD(CURDATE(), INTERVAL 45 DAY), NULL, 'active'),
(1, 'Complete Online Certification', 'Finish the web development certification course', 'Learning', 100, 100, 'percent', DATE_SUB(CURDATE(), INTERVAL 60 DAY), DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'completed');

-- SHOPPING
INSERT INTO shopping (user_id, name, quantity, estimated_price, actual_price, category, notes, is_completed) VALUES
(1, 'Laptop Stand', 1, 45.00, 0.00, 'Electronics', 'Ergonomic aluminum stand for desk', 0),
(1, 'Running Shoes', 1, 120.00, 0.00, 'Sports', 'Size 10, preferred brand Nike', 0),
(1, 'Notebooks (3-pack)', 3, 12.99, 0.00, 'Office', 'Lined, A5 size', 0),
(1, 'Wireless Mouse', 1, 35.00, 29.99, 'Electronics', 'Logitech MX Master 3', 1),
(1, 'Coffee Beans', 2, 18.00, 16.50, 'Groceries', 'Dark roast, 500g each', 1),
(1, 'Desk Lamp', 1, 55.00, 0.00, 'Home', 'LED, adjustable brightness', 0),
(1, 'Protein Powder', 1, 45.00, 0.00, 'Health', 'Vanilla flavor, 2lb tub', 0),
(1, 'External SSD 1TB', 1, 89.99, 0.00, 'Electronics', 'For backups and file storage', 0);

-- PASSWORD CATEGORIES
INSERT INTO password_categories (user_id, name, color) VALUES
(1, 'Social Media', '#3b82f6'),
(1, 'Work', '#10b981'),
(1, 'Finance', '#f59e0b'),
(1, 'Shopping', '#ec4899');

-- SAVED PASSWORDS (placeholder encrypted data)
INSERT INTO saved_passwords (user_id, title, username, encrypted_password, url, notes, category_id, is_favorite) VALUES
(1, 'Gmail', 'alex.morgan@gmail.com', 'dGVzdF9lbmNyeXB0ZWRfcGFzc3dvcmRfZ21haWw=', 'https://mail.google.com', 'Primary email', 2, 1),
(1, 'GitHub', 'alexmorgan', 'dGVzdF9lbmNyeXB0ZWRfcGFzc3dvcmRfZ2l0aHVi', 'https://github.com', 'Personal account', 2, 1),
(1, 'Twitter / X', '@alex_morgan', 'dGVzdF9lbmNyeXB0ZWRfcGFzc3dvcmRfdHdpdHRlcg==', 'https://twitter.com', NULL, 1, 0),
(1, 'Instagram', 'alex.morgan', 'dGVzdF9lbmNyeXB0ZWRfcGFzc3dvcmRfaW5zdGFncmFt', 'https://instagram.com', NULL, 1, 0),
(1, 'Bank of America', 'alex.morgan', 'dGVzdF9lbmNyeXB0ZWRfcGFzc3dvcmRfYmFuaw==', 'https://bankofamerica.com', 'Checking account', 3, 1),
(1, 'Amazon', 'alex.morgan@gmail.com', 'dGVzdF9lbmNyeXB0ZWRfcGFzc3dvcmRfYW1hem9u', 'https://amazon.com', 'Prime member', 4, 0),
(1, 'Netflix', 'alex.morgan@gmail.com', 'dGVzdF9lbmNyeXB0ZWRfcGFzc3dvcmRfbmV0ZmxpeA==', 'https://netflix.com', 'Family plan', 1, 0),
(1, 'LinkedIn', 'alex-morgan', 'dGVzdF9lbmNyeXB0ZWRfcGFzc3dvcmRfbGlua2VkaW4=', 'https://linkedin.com', 'Professional profile', 2, 0);

-- CALENDAR EVENTS
INSERT INTO calendar_events (user_id, title, event_date, description) VALUES
(1, 'Team Standup', CURDATE(), 'Daily 9:30 AM team sync'),
(1, 'Dentist Appointment', DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'Regular checkup at Dr. Smith'),
(1, 'Project Deadline', DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'Submit final deliverables for Q2'),
(1, 'Birthday Party', DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'Sarah''s birthday celebration'),
(1, 'Gym - Leg Day', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 'Focus on squats and lunges'),
(1, 'Client Meeting', DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'Discuss new feature requirements'),
(1, 'Weekend Trip', DATE_ADD(CURDATE(), INTERVAL 12 DAY), 'Cabin rental in the mountains');

-- REMINDERS
INSERT INTO reminders (user_id, title, description, reminder_date, reminder_time, repeat_type, priority, category, is_active) VALUES
(1, 'Submit quarterly report', 'Final review before sending to management', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00:00', 'none', 'high', 'Work', 1),
(1, 'Pay electricity bill', 'Log in to utility portal and pay', DATE_ADD(CURDATE(), INTERVAL 4 DAY), '10:00:00', 'none', 'high', 'Finance', 1),
(1, 'Weekly planning session', 'Review goals and plan next week', DATE_ADD(CURDATE(), INTERVAL 6 DAY), '18:00:00', 'weekly', 'medium', 'Personal', 1),
(1, 'Call dentist office', 'Schedule next cleaning appointment', DATE_ADD(CURDATE(), INTERVAL 8 DAY), '11:00:00', 'none', 'low', 'Health', 1),
(1, 'Renew gym membership', 'Current membership expires soon', DATE_ADD(CURDATE(), INTERVAL 15 DAY), '09:00:00', 'none', 'medium', 'Health', 1),
(1, 'Team retrospective', 'Sprint 12 retrospective meeting', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', 'none', 'medium', 'Work', 1);

-- ACTIVITY LOGS
INSERT INTO activity_logs (user_id, action, entity_type, entity_id, description, ip_address, user_agent) VALUES
(1, 'user_login', 'user', 1, 'User login', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(1, 'task_created', 'task', 1, 'Task created: Finish quarterly report', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(1, 'task_created', 'task', 2, 'Task created: Buy groceries', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(1, 'note_created', 'note', 1, 'Note created: App Feature Ideas', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(1, 'expense_created', 'expense', 1, 'Transaction created: Monthly Salary', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(1, 'habit_created', 'habit', 1, 'Habit created: Morning Meditation', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(1, 'goal_created', 'goal', 1, 'Goal created: Run a Half Marathon', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(1, 'document_uploaded', 'document', 1, 'Document uploaded: Passport', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(1, 'task_status_updated', 'task', 3, 'Task status updated: Morning jog', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(1, 'borrow_created', 'borrow', 1, 'Created: Laptop charger', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(1, 'password_created', 'password', 1, 'Password created: Gmail', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'),
(1, 'shopping_created', 'shopping', 1, 'Shopping item added: Laptop Stand', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');

-- TASK ACTIVITY LOGS
INSERT INTO task_activity_logs (user_id, task_id, action, description, created_at) VALUES
(1, 1, 'created', 'Task created', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 1, 'status_changed', 'Status changed to In Progress', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 2, 'created', 'Task created', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(1, 3, 'created', 'Task created', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(1, 3, 'completed', 'Task marked as completed', DATE_SUB(NOW(), INTERVAL 2 HOUR)),
(1, 4, 'created', 'Task created', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1, 5, 'created', 'Task created', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 6, 'created', 'Task created', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 6, 'status_changed', 'Status changed to In Progress', DATE_SUB(NOW(), INTERVAL 12 HOUR)),
(1, 9, 'completed', 'Task marked as completed', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1, 12, 'completed', 'Task marked as completed', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1, 14, 'created', 'Task created', DATE_SUB(NOW(), INTERVAL 8 HOUR)),
(1, 14, 'status_changed', 'Status changed to In Progress', DATE_SUB(NOW(), INTERVAL 4 HOUR));

-- NOTE IMAGES
INSERT INTO note_images (note_id, user_id, filename, original_name, file_size, mime_type) VALUES
(1, 1, 'n1_sketch_dark_mode.png', 'dark_mode_mockup.png', 245000, 'image/png'),
(1, 1, 'n1_shortcuts_reference.png', 'keyboard_shortcuts.png', 180000, 'image/png'),
(3, 1, 'n3_ingredients_photo.jpg', 'ingredients.jpg', 320000, 'image/jpeg'),
(4, 1, 'n4_tokyo_map.png', 'tokyo_transit_map.png', 450000, 'image/png');

-- NOTIFICATIONS
INSERT INTO notifications (user_id, title, message, is_read) VALUES
(1, 'Welcome to TaskNest!', 'Your account has been set up. Start by creating your first task!', 1),
(1, 'Task Due Soon', 'Your task "Finish quarterly report" is due in 3 days.', 0),
(1, 'Habit Streak!', 'You''ve meditated 3 days in a row. Keep it up!', 0),
(1, 'Budget Alert', 'You''ve spent 67% of your Food & Dining budget this month.', 0),
(1, 'Goal Update', 'You''re 59% of the way to your half marathon goal!', 1);

-- FEEDBACK
INSERT INTO feedback (user_id, subject, message, category, status, viewed_by_admin) VALUES
(1, 'Feature Request: Export to PDF', 'It would be great if we could export notes and tasks to PDF format for sharing.', 'feature', 'open', 0),
(1, 'Bug: Chart not loading', 'The expense chart sometimes shows a loading spinner indefinitely on page refresh.', 'bug', 'in_progress', 1);

-- SITE SETTINGS
INSERT INTO site_settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'TaskNest', 'string', 'Site name'),
('site_description', 'All-in-one Personal Life Management System', 'string', 'Site description'),
('maintenance_mode', '0', 'boolean', 'Enable maintenance mode'),
('allow_registration', '1', 'boolean', 'Allow new user registrations'),
('items_per_page', '20', 'number', 'Default items per page')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
