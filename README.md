# TaskNest

All-in-one Personal Life Management System built with PHP 8+, MySQL, vanilla JavaScript, and custom CSS.

![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=flat&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-Vanilla-F7DF1E?style=flat&logo=javascript&logoColor=black)
![License](https://img.shields.io/badge/License-MIT-green)

---

## Table of Contents

- [Features](#features)
- [Screenshots](#screenshots)
- [Requirements](#requirements)
- [Installation](#installation)
- [Project Structure](#project-structure)
- [Tech Stack](#tech-stack)
- [Database Schema](#database-schema)
- [Security Features](#security-features)
- [Configuration](#configuration)
- [Browser Support](#browser-support)
- [Contributing](#contributing)
- [License](#license)
- [Author](#author)

---

## Features

| Module | Description |
|--------|-------------|
| **Authentication** | Register, login, logout, forgot/reset password, remember me |
| **Dashboard** | Overview stats, charts, calendar, activity feed, reminders |
| **Tasks** | Full CRUD, categories, priorities, statuses, 4 views (list/grid/kanban/table), bulk actions |
| **Smart Notes** | Rich text editor, categories, pin/archive/trash, image uploads, search |
| **Expense Manager** | Income/expense tracking, budgets, charts, CSV export, category breakdown |
| **Document Vault** | Secure file upload (PDF/images/docs), preview, download, expiry reminders |
| **Borrow & Lend** | Track items/money borrowed or lent, return status, overdue alerts |
| **Habits** | Daily/weekly/monthly tracking, streak visualization, weekly charts |
| **Goals** | Progress tracking, target values, completion status |
| **Shopping List** | Add items, quantities, prices, categories, mark complete |
| **Admin Panel** | User management, activity logs, feedback system, site settings |

---

## Screenshots

> Add screenshots of your application here after deployment.

---

## Requirements

- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.2+
- XAMPP / WAMP / LAMP stack or equivalent
- Modern web browser (Chrome 90+, Firefox 88+, Safari 14+, Edge 90+)

---

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/YOUR_USERNAME/TaskNest.git
```

Place the `TaskNest` folder in your web server's document root (e.g., `C:\xampp\htdocs\`).

### 2. Create Database

Open phpMyAdmin (`http://localhost/phpmyadmin`) and import the schema:

**Option A: Import via phpMyAdmin**

1. Open phpMyAdmin
2. Click Import tab
3. Choose `database/tasknest.sql`
4. Click Go

**Option B: Import via command line**

```bash
mysql -u root -p < database/tasknest.sql
```

### 3. Configure Database

Edit `config/db.php` with your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tasknest');
```

### 4. Set Up Admin User

Create an admin account through the application or your database tool, then assign the `admin` role to that user.

If you need to create a password hash for the database, generate it locally with PHP and keep it private.

### 5. Access the Application

Navigate to `http://localhost/TaskNest/` in your browser.

---

## Project Structure

```
TaskNest/
|-- config/
|   |-- db.php                    # Database connection & configuration
|
|-- database/
|   |-- tasknest.sql              # MySQL database schema
|
|-- includes/
|   |-- auth.php                  # Authentication class
|   |-- functions.php             # 50+ utility functions
|   |-- header.php                # HTML head & layout
|   |-- footer.php                # Scripts & layout close
|   |-- navbar.php                # Top navigation
|   |-- sidebar.php               # Side navigation
|   |-- mail.php                  # Email helper
|
|-- modules/
|   |-- tasks/                    # Tasks CRUD
|   |-- notes/                    # Smart Notes
|   |-- expenses/                 # Expense Manager
|   |-- documents/                # Document Vault
|   |-- borrow/                   # Borrow & Lend
|   |-- habits/                   # Habit Tracker
|   |-- goals/                    # Goal Tracker
|   |-- shopping/                 # Shopping List
|   |-- admin/                    # Admin Panel
|
|-- assets/
|   |-- css/                      # 17 Stylesheets
|   |-- js/                       # 14 JavaScript files
|   |-- images/                   # Logo & static images
|
|-- uploads/                      # User uploads (avatars)
|-- logs/                         # Application logs
|
|-- index.php                     # Landing page
|-- login.php                     # Login
|-- register.php                  # Registration
|-- forgot-password.php           # Password reset request
|-- reset-password.php            # Password reset
|-- logout.php                    # Logout handler
|-- dashboard.php                 # Main dashboard
|-- profile.php                   # User profile
|-- settings.php                  # User settings
|-- tasks.php                     # Tasks entry point
|-- notes.php                     # Notes entry point
|-- expenses.php                  # Expenses entry point
|-- documents.php                 # Documents entry point
|-- habits.php                    # Habits entry point
|-- goals.php                     # Goals entry point
|-- shopping.php                  # Shopping entry point
|-- borrow.php                    # Borrow entry point
|-- admin.php                     # Admin entry point
|
|-- .htaccess                     # Apache security rules
|-- .gitignore                    # Git ignore rules
|-- README.md                     # This file
```

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| **Backend** | PHP 8+ (procedural with prepared statements) |
| **Database** | MySQL with InnoDB, utf8mb4 |
| **Frontend** | HTML5, CSS3 (custom properties), Vanilla JavaScript |
| **Charts** | Chart.js (CDN) |
| **Security** | CSRF tokens, XSS protection, Argon2id passwords, HttpOnly cookies |

---

## Database Schema

The application uses 25+ tables with InnoDB engine and utf8mb4_unicode_ci collation.

### Core Tables

| Table | Purpose |
|-------|---------|
| users | User accounts with roles (user/admin) |
| password_resets | Password reset tokens |
| sessions | Remember-me tokens |
| settings | User preferences |
| activity_logs | Audit trail |
| notifications | User notifications |
| calendar_events | Calendar entries |

### Module Tables

| Module | Tables |
|--------|--------|
| Tasks | tasks, task_categories, task_activity_logs |
| Notes | notes, note_categories, note_images |
| Expenses | expenses, expense_categories, budgets |
| Documents | documents, document_categories |
| Habits | habits, habit_logs |
| Goals | goals |
| Shopping | shopping |
| Borrow | borrow_items |
| Admin | feedback, site_settings (dynamic) |

---

## Security Features

### Application Level

- Prepared statements for all SQL queries
- CSRF token verification on all POST requests
- XSS protection via `htmlspecialchars()` with `ENT_QUOTES`
- Argon2id password hashing (65536 memory cost, 4 time cost, 3 threads)
- Session security (HttpOnly, Secure, SameSite, 30-min timeout)
- File upload validation (type, size, MIME)
- Role-based access control for admin features
- Soft deletes (no data actually removed)

### Server Level (.htaccess)

- Directory browsing disabled
- Protected directories: config/, database/, logs/
- PHP execution blocked in uploads/
- Security headers enabled
- Server signature disabled

---

## Configuration

| Constant | Default | Description |
|----------|---------|-------------|
| DB_HOST | localhost | MySQL host |
| DB_USER | root | MySQL username |
| DB_PASS | (empty) | MySQL password |
| DB_NAME | tasknest | Database name |
| SITE_URL | http://localhost/TaskNest | Application URL |
| SESSION_TIMEOUT | 1800 | Session timeout (30 min) |
| REMEMBER_ME_DURATION | 604800 | Remember me (7 days) |
| MAX_UPLOAD_SIZE | 5242880 | Max upload (5MB) |
| DEBUG | true | Debug mode |

---

## Browser Support

| Browser | Minimum Version |
|---------|-----------------|
| Chrome | 90+ |
| Firefox | 88+ |
| Safari | 14+ |
| Edge | 90+ |

---

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---


## Author

**Your Name** - Chetan Pawar

**Email** -  chetanpawar8125@email.com

Project Link: [https://github.com/Chetan-Pawar18706/TaskNest](https://github.com/Chetan-Pawar18706/TaskNest)
