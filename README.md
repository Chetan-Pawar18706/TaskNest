# TaskNest

All-in-one Personal Life Management System built with PHP 8+, MySQL, vanilla JavaScript, and custom CSS.

![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=flat&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-Vanilla-F7DF1E?style=flat&logo=javascript&logoColor=black)
![License](https://img.shields.io/badge/License-MIT-green)

---

## Table of Contents

- [Features](#features)
- [Demo](#demo)
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
| **Two-Factor Auth** | TOTP-based 2FA (Google Authenticator / Authy), QR code setup, backup codes |
| **Dashboard** | Overview stats, charts, calendar, activity feed, quick actions |
| **Tasks** | Full CRUD, categories, priorities, statuses, 4 views (list/grid/kanban/table), bulk actions |
| **Smart Notes** | Rich text, categories, pin/archive/trash, image uploads, search |
| **Expense Manager** | Income/expense tracking, budgets, charts (line/pie), CSV export |
| **Document Vault** | Secure file upload (PDF/images/docs), preview, download, expiry reminders |
| **Borrow & Lend** | Track items/money borrowed or lent, return status, overdue alerts |
| **Habits** | Daily/weekly/monthly tracking, streak visualization, weekly activity charts |
| **Goals** | Progress tracking with target values, completion status, categories |
| **Shopping List** | Add items, quantities, estimated vs actual prices, categories |
| **Events** | Calendar events with add/edit functionality |
| **Admin Panel** | User management, activity logs, feedback system, site settings |

### UI Features

- **Dark/Light Theme** — Toggle from navbar or settings, persisted to database
- **Responsive Design** — Mobile-first with collapsible sidebar
- **Filter Panels** — Toggleable filter forms on all module pages (hidden on mobile by default)
- **Default Profile Avatar** — SVG fallback for users without profile pictures
- **Toast Notifications** — Non-intrusive success/error messages
- **Confirm Modals** — For destructive actions (delete, etc.)

---

## Demo

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
git clone https://github.com/Chetan-Pawar18706/TaskNest.git
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
├── config/
│   └── db.php                          # DB connection, constants, error handlers
├── database/
│   └── tasknest.sql                    # Complete MySQL schema (25+ tables)
├── includes/
│   ├── auth.php                        # Auth class: register, login, 2FA, CSRF, password reset
│   ├── functions.php                   # 50+ utility/handler functions
│   ├── header.php                      # HTML head, CSS loading, navbar/sidebar includes
│   ├── footer.php                      # Script loading, layout closing, CSRF token
│   ├── navbar.php                      # Top navigation (theme toggle, search, user menu)
│   ├── sidebar.php                     # Side navigation (module links, admin link, profile)
│   └── mail.php                        # Email helper using PHP mail()
├── modules/
│   ├── tasks/                          # Tasks CRUD (index, add, edit, categories, handler)
│   ├── notes/                          # Smart Notes (index, add, edit, categories, handler)
│   ├── expenses/                       # Expense Manager (index, add, edit, categories, handler)
│   ├── documents/                      # Document Vault (index, upload, edit, categories, handler)
│   ├── borrow/                         # Borrow & Lend (index, add, edit, handler)
│   ├── habits/                         # Habit Tracker (index, add, edit, handler)
│   ├── goals/                          # Goal Tracker (index, add, edit, categories, handler)
│   ├── shopping/                       # Shopping List (index, add, edit, categories, handler)
│   ├── events/                         # Calendar Events (add, edit)
│   └── admin/                          # Admin Panel (dashboard, users, activity, feedback, settings)
├── assets/
│   ├── css/ (17 files)                 # Modular stylesheets per feature
│   ├── js/ (14 files)                  # Modular scripts per feature
│   └── images/                         # Logo, default-avatar.svg
├── uploads/                            # User uploads (avatars, documents, notes, tasks)
├── logs/                               # Application error logs
├── index.php                           # Landing page for unauthenticated users
├── login.php                           # Login with 2FA support
├── register.php                        # User registration
├── forgot-password.php                 # Password reset request
├── reset-password.php                  # Password reset with token
├── logout.php                          # Session destruction
├── dashboard.php                       # Main dashboard with stats and charts
├── profile.php                         # User profile view
├── settings.php                        # User settings (profile, theme, 2FA, notifications)
├── 2fa-setup.php                       # Two-factor auth setup (QR code, backup codes)
├── 2fa-verify.php                      # Two-factor auth verification (login)
├── tasks.php                           # Tasks entry point (POST handler → module view)
├── notes.php                           # Notes entry point
├── expenses.php                        # Expenses entry point (+ CSV export)
├── documents.php                       # Documents entry point
├── habits.php                          # Habits entry point
├── goals.php                           # Goals entry point
├── shopping.php                        # Shopping entry point
├── borrow.php                          # Borrow entry point
├── .htaccess                           # Apache security rules
├── .gitignore                          # Git ignore rules
├── README.md                           # This file
└── PROJECT_SUMMARY.md                  # Detailed technical summary
```

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| **Backend** | PHP 8+ (procedural with prepared statements) |
| **Database** | MySQL with InnoDB, utf8mb4_unicode_ci |
| **Frontend** | HTML5, CSS3 (custom properties), Vanilla JavaScript |
| **Charts** | Chart.js (CDN) |
| **2FA** | TOTP (RFC 6238) — pure PHP implementation, Google Charts QR API |
| **Security** | CSRF tokens, XSS protection, Argon2id passwords, HttpOnly cookies |

---

## Database Schema

The application uses 25+ tables with InnoDB engine and utf8mb4_unicode_ci collation.

### Core Tables

| Table | Purpose |
|-------|---------|
| users | User accounts with roles (user/admin), theme preference |
| password_resets | Password reset tokens |
| sessions | Remember-me tokens |
| settings | User preferences (notifications, 2FA enabled/secret/backup codes) |
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

- Prepared statements for all SQL queries (no string concatenation)
- CSRF token verification on all POST requests
- XSS protection via `htmlspecialchars()` with `ENT_QUOTES`
- Argon2id password hashing (65536 memory cost, 4 time cost, 3 threads)
- Session security (HttpOnly, Secure, SameSite, 30-min timeout)
- File upload validation (type, size, MIME)
- Role-based access control for admin features
- Soft deletes (no data actually removed)
- Two-Factor Authentication (TOTP) with backup codes

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

## License

MIT License

---

## Author

**Name** - Chetan Pawar

**Email** - chetanpawar8125@email.com

**Project Link**: [https://github.com/Chetan-Pawar18706/TaskNest](https://github.com/Chetan-Pawar18706/TaskNest)
#   T a s k N e s t  
 