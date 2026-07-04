# TaskNest - Complete Project Summary

## Overview

TaskNest is a full-stack, server-rendered web application for personal life management. It is a traditional PHP multi-page application where each URL maps to a `.php` file that renders a full HTML page. The application runs on XAMPP (Apache + MySQL + PHP) and is accessed via browser.

**Tech Stack:** PHP 8+ (procedural), MySQL (InnoDB, utf8mb4), HTML5, CSS3 (custom properties), Vanilla JavaScript, Chart.js (CDN)

---

## Project Structure

```
TaskNest/
├── config/
│   └── db.php                          # DB connection, constants, error handlers
├── database/
│   └── tasknest.sql                    # Complete MySQL schema (25+ tables)
├── includes/
│   ├── auth.php                        # Auth class: register, login, logout, CSRF, password reset
│   ├── functions.php                   # 50+ utility/handler functions (3312 lines)
│   ├── header.php                      # HTML head, CSS loading, navbar/sidebar includes
│   ├── footer.php                      # Script loading, layout closing
│   ├── navbar.php                      # Top navigation bar
│   ├── sidebar.php                     # Side navigation (197 lines, shows admin link conditionally)
│   └── mail.php                        # Email helper using PHP mail()
├── modules/
│   ├── tasks/
│   │   ├── index.php                   # Tasks page view
│   │   └── tasks.php                   # Tasks POST handler
│   ├── notes/
│   │   ├── index.php
│   │   └── notes.php
│   ├── expenses/
│   │   ├── index.php
│   │   └── expenses.php
│   ├── documents/
│   │   ├── index.php
│   │   └── documents.php
│   ├── habits/
│   │   ├── index.php
│   │   └── habits.php
│   ├── goals/
│   │   ├── index.php
│   │   └── goals.php
│   ├── shopping/
│   │   ├── index.php
│   │   └── shopping.php
│   ├── borrow/
│   │   ├── index.php
│   │   └── borrow.php
│   └── admin/
│       ├── index.php                   # Admin panel view (256 lines)
│       └── admin.php                   # Admin POST handler (32 lines)
├── assets/
│   ├── css/ (17 files)
│   │   ├── variables.css, reset.css, styles.css, responsive.css
│   │   ├── dashboard.css, components.css, theme.css, landing.css
│   │   ├── tasks.css, notes.css, expenses.css, documents.css
│   │   ├── habits.css, goals.css, shopping.css, borrow.css, admin.css
│   └── js/ (14 files)
│       ├── main.js, theme.js, sidebar.js, toast.js, modal.js
│       ├── dashboard.js, tasks.js, notes.js, expenses.js, documents.js
│       ├── habits.js, goals.js, shopping.js, borrow.js
├── uploads/
│   └── avatars/                        # User profile pictures
├── logs/                               # Application error logs
├── index.php                           # Landing page for unauthenticated users
├── login.php                           # Login form
├── register.php                        # Registration form
├── forgot-password.php                 # Password reset request
├── reset-password.php                  # Password reset with token
├── logout.php                          # Session destruction
├── dashboard.php                       # Main dashboard (214 lines)
├── profile.php                         # User profile view
├── settings.php                        # User settings/preferences
├── tasks.php                           # Tasks entry point (POST router + module include)
├── notes.php                           # Notes entry point
├── expenses.php                        # Expenses entry point (+ CSV export)
├── documents.php                       # Documents entry point
├── habits.php                          # Habits entry point
├── goals.php                           # Goals entry point
├── shopping.php                        # Shopping entry point
├── borrow.php                          # Borrow/Lend entry point
├── admin.php                           # Admin panel entry point
├── .htaccess                           # Apache security (directory blocking, headers)
├── .gitignore                          # Git ignore rules
└── README.md                           # Project documentation
```

**File counts:**
- 25 root-level PHP files
- 18 module PHP files (9 modules x 2 files each)
- 7 include PHP files
- 1 config PHP file
- 17 CSS files
- 14 JavaScript files
- 1 SQL schema file
- 1 documentation file
- **Total: ~84 files**

---

## Authentication System

**File:** `includes/auth.php` (362 lines)

The `Auth` class handles all authentication operations:

### Session Security
- Strict mode enabled
- Cookies only (no URL session IDs)
- HttpOnly, Secure, SameSite=Lax cookies
- 30-minute session timeout
- Session regeneration on login

### Registration (`register()`)
- Username: 3-50 characters, unique
- Email: valid format, unique
- Password: 8+ characters, must contain uppercase letter and number
- Password hashing: Argon2id (65536 memory cost, 4 time cost, 3 threads)
- Auto-creates settings row for new user
- Logs registration activity

### Login (`login()`)
- Validates email format and password presence
- Checks `is_active` status
- Uses `password_verify()` against Argon2id hash
- Regenerates session ID on success
- Generates CSRF token
- Optional "remember me" (7 days) with token in `sessions` table + HttpOnly cookie
- Logs login activity

### Password Reset
- Token-based via `password_resets` table
- 1-hour token expiry
- Sends reset link via email
- Validates token, updates password, marks token as used

### CSRF Protection
- `generateCsrfToken()`: creates 64-char hex token in session
- `verifyCsrfToken()`: compares submitted token against session value
- Used on all POST forms

---

## Database Schema

**File:** `database/tasknest.sql` (408 lines)

All tables use InnoDB engine with utf8mb4_unicode_ci collation.

### Core Tables
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `users` | User accounts | id, username, email, password_hash, first_name, last_name, avatar_url, phone, bio, timezone, theme, is_active, role (user/admin) |
| `password_resets` | Password reset tokens | id, user_id, token, expires_at, is_used |
| `sessions` | Remember-me tokens | id, user_id, session_token, remember_token, expires_at |
| `settings` | User preferences | user_id, notifications_enabled, two_factor_enabled, language, date_format, items_per_page |
| `activity_logs` | Audit trail | user_id, action, entity_type, entity_id, description, ip_address, user_agent |
| `notifications` | User notifications | user_id, title, message, is_read |
| `calendar_events` | Calendar entries | user_id, title, event_date, description |

### Module Tables
| Table | Purpose |
|-------|---------|
| `task_categories` | Task categories with color/icon |
| `tasks` | Tasks with status, priority, due_date, reminder |
| `task_activity_logs` | Task-specific activity |
| `note_categories` | Note categories |
| `notes` | Notes with pin/archive/trash |
| `note_images` | Images attached to notes |
| `expense_categories` | Expense/income categories |
| `expenses` | Transactions with type, amount, recurring |
| `budgets` | Budget limits per category/period |
| `document_categories` | Document categories |
| `documents` | File uploads with metadata |
| `borrow_items` | Borrowed/lent tracking |
| `habits` | Habit definitions |
| `habit_logs` | Daily habit completions |
| `goals` | Goals with target/current values |
| `shopping` | Shopping list items |

### Dynamic Tables (created by PHP)
- `feedback` - User feedback with admin replies
- `site_settings` - Key/value site configuration (site_name, maintenance_mode, allow_registration, etc.)

### Key Design Patterns
- Soft deletes (`is_deleted` column) on all module tables
- Foreign keys with `ON DELETE CASCADE` or `ON DELETE SET NULL`
- Composite indexes for common query patterns
- Timestamps on all tables (created_at, updated_at)

---

## Modules

### 1. Tasks Module
**Files:** `modules/tasks/index.php`, `modules/tasks/tasks.php`

- Full CRUD operations
- Categories with custom colors
- Priority levels (Low, Medium, High, Urgent)
- Statuses (Pending, In Progress, Completed)
- Due dates with reminders
- 4 views: List, Grid, Kanban, Table
- Bulk actions (complete, delete, change status)
- Task activity logging

### 2. Smart Notes Module
**Files:** `modules/notes/index.php`, `modules/notes/notes.php`

- Rich text content
- Categories with colors
- Pin important notes
- Archive old notes
- Soft delete (trash)
- Image attachments
- Full-text search

### 3. Expense Manager Module
**Files:** `modules/expenses/index.php`, `modules/expenses/expenses.php`

- Income and expense tracking
- Custom categories with colors
- Budget management (monthly/weekly/yearly)
- Charts (spending by category, trends)
- CSV export
- Recurring transactions

### 4. Document Vault Module
**Files:** `modules/documents/index.php`, `modules/documents/documents.php`

- Secure file upload (PDF, images, docs)
- File type and size validation
- Categories
- Expiry date tracking
- Reminder dates
- Important flag
- Preview and download

### 5. Habits Module
**Files:** `modules/habits/index.php`, `modules/habits/habits.php`

- Daily/weekly/monthly frequency
- Target count per period
- Streak visualization
- Weekly progress charts
- Color coding

### 6. Goals Module
**Files:** `modules/goals/index.php`, `modules/goals/goals.php`

- Target values with current progress
- Categories
- Start/due dates
- Status tracking (active, completed, abandoned)
- Completion percentage

### 7. Shopping Module
**Files:** `modules/shopping/index.php`, `modules/shopping/shopping.php`

- Item name and quantity
- Estimated vs actual price
- Categories
- Mark as complete
- Notes

### 8. Borrow & Lend Module
**Files:** `modules/borrow/index.php`, `modules/borrow/borrow.php`

- Track borrowed or lent items/money
- Person name and contact
- Borrow and return dates
- Status tracking (pending, returned, overdue)
- Overdue alerts

### 9. Admin Panel
**Files:** `modules/admin/index.php`, `modules/admin/admin.php`

- **Dashboard tab:** User registration chart, module usage chart
- **Users tab:** Searchable user list, activate/deactivate toggle
- **Activity Log tab:** Paginated audit trail
- **Feedback tab:** User feedback with admin replies, status management
- **Settings tab:** Dynamic site configuration (site_name, maintenance_mode, allow_registration, items_per_page)

---

## Dashboard

**File:** `dashboard.php` (214 lines)

- Welcome hero with quick action buttons
- 10 stat cards (tasks, completed, pending, notes, monthly expense, documents, habits, goals, shopping, borrow)
- 3 charts: Expense trends (6-month line), Task completion (doughnut), Habit progress (weekly bar)
- Mini calendar with event counts
- Recent activity timeline
- Upcoming reminders
- 6 quick action buttons

---

## Security

### Application Level
- Prepared statements for all SQL queries (no string concatenation)
- CSRF token verification on all POST requests
- XSS protection via `htmlspecialchars()` with `ENT_QUOTES`
- Argon2id password hashing
- Session security (HttpOnly, Secure, SameSite, 30-min timeout)
- File upload validation (type, size, MIME)
- Role-based access control for admin features
- Soft deletes (no data actually removed)

### Server Level (.htaccess)
- Directory browsing disabled (`Options -Indexes`)
- Protected directories: `config/`, `database/`, `logs/`
- PHP execution blocked in `uploads/`
- Security headers: X-Content-Type-Options, X-Frame-Options, X-XSS-Protection, Referrer-Policy
- Server signature disabled
- PHP settings: display_errors off, expose_php off, 10M upload limit, 30s execution limit

### Database
- All queries use prepared statements with bound parameters
- Foreign key constraints enforce referential integrity
- CASCADE deletes prevent orphaned records
- UTF8MB4 charset for full Unicode support

---

## Configuration

**File:** `config/db.php` (105 lines)

| Constant | Value | Purpose |
|----------|-------|---------|
| DB_HOST | localhost | MySQL host |
| DB_USER | root | MySQL user |
| DB_PASS | (empty) | MySQL password |
| DB_NAME | tasknest | Database name |
| DB_PORT | 3306 | MySQL port |
| SITE_URL | http://localhost/TaskNest | Application URL |
| SESSION_TIMEOUT | 1800 (30 min) | Session expiry |
| REMEMBER_ME_DURATION | 604800 (7 days) | Remember me cookie |
| MAX_UPLOAD_SIZE | 5242880 (5MB) | Max file upload |
| DEBUG | true | Debug mode |

---

## Frontend Architecture

### CSS (17 files)
- `variables.css` - CSS custom properties (colors, spacing, typography)
- `reset.css` - CSS reset and normalization
- `styles.css` - Main component styles
- `responsive.css` - Mobile breakpoints
- Module-specific: dashboard, tasks, notes, expenses, documents, habits, goals, shopping, borrow, admin
- `theme.css` - Dark/light theme support
- `landing.css` - Landing page styles
- `components.css` - Reusable component styles

### JavaScript (14 files)
- `main.js` - Core utilities, namespace (`window.TaskNest`), sidebar toggle
- `theme.js` - Dark/light theme toggle with localStorage persistence
- `sidebar.js` - Sidebar collapse/expand
- `toast.js` - Toast notification system
- `modal.js` - Modal dialog management
- Module-specific: dashboard, tasks, notes, expenses, documents, habits, goals, shopping, borrow

### Key Frontend Patterns
- Global `TaskNest` namespace for JS modules
- CSS custom properties for theming
- Vanilla JS (no frameworks, no jQuery)
- Chart.js via CDN for data visualization
- IntersectionObserver for scroll animations (landing page)
- Event delegation for dynamic content

---

## Architectural Patterns

### Request Flow
```
Browser Request
    → .htaccess (security checks, URL rewriting)
    → Root PHP file (e.g., tasks.php)
    → config/db.php (database connection)
    → includes/auth.php (session/auth check)
    → POST handler (if applicable)
    → modules/<feature>/index.php (view)
    → includes/header.php + footer.php (layout)
    → HTML Response
```

### Module Pattern
Each module follows the same structure:
1. Root-level entry point (e.g., `tasks.php`) - handles routing and POST
2. Module view (`modules/tasks/index.php`) - HTML/template
3. Module handler (`modules/tasks/tasks.php`) - POST processing
4. Functions in `includes/functions.php` - business logic

### Data Access
- All database access via MySQLi prepared statements
- `safePrepare()` wrapper prevents "bind_param() on bool" errors
- `_NullStmt` / `_NullResult` stub objects for failed queries
- Centralized in `includes/functions.php`

### Error Handling
- Custom error handler logs to `logs/errors.log`
- Custom exception handler with debug mode support
- Database connection errors caught and logged
- Graceful degradation in production mode

---

## Setup & Installation

1. Place files in web server document root (e.g., `C:\xampp\htdocs\TaskNest\`)
2. Import `database/tasknest.sql` into MySQL
3. Edit `config/db.php` with database credentials
4. Ensure `uploads/` and `logs/` directories are writable
5. Access `http://localhost/TaskNest/` in browser
6. Register a new user or set up admin access via database

---

## Browser Support

| Browser | Minimum Version |
|---------|-----------------|
| Chrome | 90+ |
| Firefox | 88+ |
| Safari | 14+ |
| Edge | 90+ |

---

## License

MIT License
