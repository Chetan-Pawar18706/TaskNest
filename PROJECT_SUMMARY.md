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
│   ├── auth.php                        # Auth class: register, login, 2FA, CSRF, password reset
│   ├── functions.php                   # 50+ utility/handler functions
│   ├── header.php                      # HTML head, CSS loading with cache-busting
│   ├── footer.php                      # Script loading, layout closing, CSRF token
│   ├── navbar.php                      # Top navigation (theme toggle, search, user menu, avatar)
│   ├── sidebar.php                     # Side navigation (module links, admin, profile avatar)
│   └── mail.php                        # Email helper using PHP mail()
├── modules/
│   ├── tasks/
│   │   ├── index.php                   # Tasks list view with filter toggle
│   │   ├── tasks.php                   # Tasks POST/AJAX handler
│   │   ├── tasks-add.php               # Add task form
│   │   ├── tasks-edit.php              # Edit task form
│   │   └── tasks-categories.php        # Category management
│   ├── notes/
│   │   ├── index.php                   # Notes list view with filter toggle
│   │   ├── notes.php                   # Notes POST/AJAX handler
│   │   ├── notes-add.php               # Add note form
│   │   ├── notes-edit.php              # Edit note form
│   │   └── notes-categories.php        # Category management
│   ├── expenses/
│   │   ├── index.php                   # Expenses list with charts, filter toggle
│   │   ├── expenses.php                # Expenses POST/AJAX handler
│   │   ├── expenses-add.php            # Add transaction form
│   │   ├── expenses-edit.php           # Edit transaction form
│   │   └── expenses-categories.php     # Category management
│   ├── documents/
│   │   ├── index.php                   # Documents list with filter toggle
│   │   ├── documents.php               # Documents POST/AJAX handler
│   │   ├── documents-upload.php        # Upload form
│   │   ├── documents-edit.php          # Edit form
│   │   └── documents-categories.php    # Category management
│   ├── borrow/
│   │   ├── index.php                   # Borrow list with filter toggle
│   │   ├── borrow.php                  # Borrow POST/AJAX handler
│   │   ├── borrow-add.php              # Add form
│   │   └── borrow-edit.php             # Edit form
│   ├── habits/
│   │   ├── index.php                   # Habits grid with filter toggle, weekly chart
│   │   ├── habits.php                  # Habits POST/AJAX handler
│   │   ├── habits-add.php              # Add habit form
│   │   └── habits-edit.php             # Edit habit form
│   ├── goals/
│   │   ├── index.php                   # Goals list with progress bars, filter toggle
│   │   ├── goals.php                   # Goals POST/AJAX handler
│   │   ├── goals-add.php               # Add goal form
│   │   ├── goals-edit.php              # Edit goal form
│   │   └── goal-categories.php         # Category management
│   ├── shopping/
│   │   ├── index.php                   # Shopping list with filter toggle
│   │   ├── shopping.php                # Shopping POST/AJAX handler
│   │   ├── shopping-add.php            # Add item form
│   │   ├── shopping-edit.php           # Edit item form
│   │   └── shopping-categories.php     # Category management
│   ├── events/
│   │   ├── events-add.php              # Add event form
│   │   └── events-edit.php             # Edit event form
│   └── admin/
│       ├── index.php                   # Admin panel view (dashboard, users, activity, feedback, settings)
│       ├── admin.php                   # Admin POST handler (AJAX)
│       └── admin-root.php              # Admin entry point (auth check, routing)
├── assets/
│   ├── css/ (17 files)
│   │   ├── variables.css               # CSS custom properties (colors, spacing, typography)
│   │   ├── reset.css                   # CSS reset and normalization
│   │   ├── styles.css                  # Main component styles, layout, 2FA styles
│   │   ├── responsive.css              # Mobile breakpoints
│   │   ├── theme.css                   # Dark/light theme variables
│   │   ├── components.css              # Reusable component styles (modal, sidebar profile)
│   │   ├── landing.css                 # Landing page styles
│   │   ├── dashboard.css               # Dashboard-specific styles
│   │   ├── tasks.css                   # Tasks module + filter toggle button
│   │   ├── notes.css                   # Notes module
│   │   ├── expenses.css                # Expenses module
│   │   ├── documents.css               # Documents module
│   │   ├── habits.css                  # Habits module
│   │   ├── goals.css                   # Goals module
│   │   ├── shopping.css                # Shopping module
│   │   ├── borrow.css                  # Borrow module
│   │   └── admin.css                   # Admin panel
│   └── js/ (14 files)
│       ├── main.js                     # Core utilities, namespace
│       ├── theme.js                    # Dark/light theme toggle with DB persistence
│       ├── sidebar.js                  # Sidebar collapse/expand, click-outside close
│       ├── toast.js                    # Toast notification system
│       ├── modal.js                    # Modal dialog management
│       ├── dashboard.js                # Dashboard charts and interactions
│       ├── tasks.js                    # Tasks CRUD, bulk actions, views
│       ├── notes.js                    # Notes CRUD, pin/archive/delete
│       ├── expenses.js                 # Expenses CRUD, charts, budgets
│       ├── documents.js                # Documents CRUD, upload
│       ├── habits.js                   # Habits CRUD, log tracking
│       ├── goals.js                    # Goals CRUD, progress update
│       ├── shopping.js                 # Shopping CRUD, checkbox toggle
│       └── borrow.js                   # Borrow CRUD
├── uploads/
│   ├── avatars/                        # User profile pictures
│   ├── documents/                      # Uploaded documents
│   ├── notes/                          # Note attachments
│   ├── profile/                        # Profile pictures
│   └── tasks/                          # Task attachments
├── logs/
│   └── errors.log                      # Application error logs
├── index.php                           # Landing page
├── login.php                           # Login with 2FA redirect
├── register.php                        # Registration
├── forgot-password.php                 # Password reset request
├── reset-password.php                  # Password reset with token
├── logout.php                          # Session destruction
├── dashboard.php                       # Main dashboard
├── profile.php                         # User profile
├── settings.php                        # User settings (theme, 2FA, notifications)
├── 2fa-setup.php                       # Two-factor auth setup (QR code, backup codes)
├── 2fa-verify.php                      # Two-factor auth verification (login)
├── tasks.php                           # Tasks entry point
├── notes.php                           # Notes entry point
├── expenses.php                        # Expenses entry point
├── documents.php                       # Documents entry point
├── habits.php                          # Habits entry point
├── goals.php                           # Goals entry point
├── shopping.php                        # Shopping entry point
├── borrow.php                          # Borrow entry point
├── .htaccess                           # Apache security
├── .gitignore                          # Git ignore rules
├── README.md                           # Project documentation
└── PROJECT_SUMMARY.md                  # This file
```

**File counts:**
- 19 root-level PHP files
- 43 module PHP files (10 modules)
- 7 include PHP files
- 1 config PHP file
- 17 CSS files
- 14 JavaScript files
- 1 SQL schema file
- **Total: ~102 files**

---

## Authentication System

**File:** `includes/auth.php` (606 lines)

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

### Two-Factor Authentication (TOTP)
- `generateTwoFactorSecret()` — Generates Base32 secret key
- `getTwoFactorUri()` — Generates otpauth:// URI for QR code
- `generateTotpCode()` — Generates current 6-digit TOTP code (HMAC-SHA1)
- `verifyTotpCode()` — Verifies code with ±1 time window tolerance
- `generateBackupCodes()` — Generates 8 backup codes (hashed)
- `verifyBackupCode()` — Verifies and consumes a backup code
- `enableTwoFactor()` / `disableTwoFactor()` — Toggle 2FA
- `setTwoFactorPending()` / `isTwoFactorPending()` / `completeTwoFactorVerification()` — Login 2FA flow

### Password Reset
- Token-based via `password_resets` table
- 1-hour token expiry
- Sends reset link via email
- Validates token, updates password, marks token as used

### CSRF Protection
- `generateCsrfToken()` — Creates 64-char hex token in session
- `verifyCsrfToken()` — Compares submitted token against session value
- Used on all POST forms

### Activity Logging
- `logActivity()` — Logs user actions to `activity_logs` table
- Records IP address and user agent

---

## Database Schema

**File:** `database/tasknest.sql` (408+ lines)

All tables use InnoDB engine with utf8mb4_unicode_ci collation.

### Core Tables
| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `users` | User accounts | id, username, email, password_hash, first_name, last_name, avatar_url, phone, bio, timezone, theme, is_active, role (user/admin) |
| `password_resets` | Password reset tokens | id, user_id, token, expires_at, is_used |
| `sessions` | Remember-me tokens | id, user_id, session_token, remember_token, expires_at |
| `settings` | User preferences | user_id, notifications_enabled, two_factor_enabled, two_factor_secret, two_factor_backup_codes, language, date_format, items_per_page |
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
- `feedback` — User feedback with admin replies
- `site_settings` — Key/value site configuration (site_name, maintenance_mode, allow_registration, etc.)

### Key Design Patterns
- Soft deletes (`is_deleted` column) on all module tables
- Foreign keys with `ON DELETE CASCADE` or `ON DELETE SET NULL`
- Composite indexes for common query patterns
- Timestamps on all tables (created_at, updated_at)

---

## Modules

### 1. Tasks Module
**Files:** `modules/tasks/` (5 files)

- Full CRUD operations
- Categories with custom colors
- Priority levels (Low, Medium, High, Urgent)
- Statuses (Pending, In Progress, Completed)
- Due dates with reminders
- 4 views: List, Grid, Kanban, Table
- Bulk actions (complete, delete, change category)
- Task activity logging
- Toggleable filter panel (search, status, priority, category, date range, sort)

### 2. Smart Notes Module
**Files:** `modules/notes/` (5 files)

- Rich text content
- Categories with colors
- Pin important notes
- Archive old notes
- Soft delete (trash)
- Image attachments
- Full-text search
- Toggleable filter panel

### 3. Expense Manager Module
**Files:** `modules/expenses/` (5 files)

- Income and expense tracking
- Custom categories with colors
- Budget management (monthly/weekly/yearly)
- Charts: Income vs Expenses (line), Category Breakdown (pie)
- CSV export
- Recurring transactions
- Toggleable filter panel (search, type, category, date range)

### 4. Document Vault Module
**Files:** `modules/documents/` (5 files)

- Secure file upload (PDF, images, docs)
- File type and size validation
- Categories
- Expiry date tracking
- Reminder dates
- Important flag
- Preview and download
- Toggleable filter panel

### 5. Habits Module
**Files:** `modules/habits/` (4 files)

- Daily/weekly/monthly frequency
- Target count per period
- Streak visualization (7-day grid)
- Weekly activity bar chart (Chart.js)
- Color coding
- Toggleable search filter

### 6. Goals Module
**Files:** `modules/goals/` (5 files)

- Target values with current progress
- Categories
- Start/due dates
- Status tracking (active, completed, abandoned)
- Completion percentage with progress bar
- Toggleable filter panel

### 7. Shopping Module
**Files:** `modules/shopping/` (5 files)

- Item name and quantity
- Estimated vs actual price
- Categories
- Mark as complete (checkbox)
- Notes
- Toggleable filter panel

### 8. Borrow & Lend Module
**Files:** `modules/borrow/` (4 files)

- Track borrowed or lent items/money
- Person name and contact
- Borrow and return dates
- Status tracking (pending, returned, overdue)
- Overdue alerts
- Toggleable filter panel (search, type, status)

### 9. Events Module
**Files:** `modules/events/` (2 files)

- Calendar events with add/edit
- Linked from dashboard calendar

### 10. Admin Panel
**Files:** `modules/admin/` (3 files)

- **Dashboard tab:** User registration chart, module usage chart
- **Users tab:** Searchable user list, activate/deactivate toggle
- **Activity Log tab:** Paginated audit trail
- **Feedback tab:** User feedback with admin replies, status management
- **Settings tab:** Dynamic site configuration (site_name, maintenance_mode, allow_registration, items_per_page)

---

## Dashboard

**File:** `dashboard.php`

- Welcome hero with quick action buttons
- 10 stat cards (tasks, completed, pending, notes, monthly expense, documents, habits, goals, shopping, borrow)
- 3 charts: Expense trends (6-month line), Task completion (doughnut), Habit progress (weekly bar)
- Mini calendar with event counts
- Recent activity timeline
- Upcoming reminders
- Quick action links (tasks, notes, expenses, documents, habits, goals)

---

## Two-Factor Authentication (2FA)

### Setup Flow (`2fa-setup.php`)
1. **Step 1 — Introduction:** Explains how 2FA works with authenticator apps
2. **Step 2 — QR Code:** Shows QR code for scanning + manual secret key for copying
3. **Step 3 — Backup Codes:** Displays 8 one-time backup codes to save/download

### Login Flow
1. User submits email + password (`login.php`)
2. If 2FA enabled → redirect to `2fa-verify.php` (user not fully logged in yet)
3. User enters 6-digit TOTP code OR 8-character backup code
4. On success → session created, user logged in

### Verification
- TOTP codes verified with ±1 time window (30 seconds each)
- Backup codes are single-use, stored as SHA-256 hashes
- 2FA can be enabled/disabled from Settings → Security

---

## Frontend Architecture

### CSS (17 files)
- `variables.css` — CSS custom properties (colors, spacing, typography, shadows)
- `reset.css` — CSS reset and normalization
- `styles.css` — Main component styles, layout, 2FA page styles
- `responsive.css` — Mobile breakpoints (1024px, 768px, 480px)
- `theme.css` — Dark/light theme variable overrides
- `components.css` — Reusable component styles (modal, sidebar profile)
- `landing.css` — Landing page styles
- Module-specific: dashboard, tasks, notes, expenses, documents, habits, goals, shopping, borrow, admin

### JavaScript (14 files)
- `main.js` — Core utilities, `window.TaskNest` namespace
- `theme.js` — Dark/light theme toggle with localStorage + database persistence
- `sidebar.js` — Sidebar collapse/expand, click-outside close, resize handler
- `toast.js` — Toast notification system
- `modal.js` — Modal dialog management
- Module-specific: dashboard, tasks, notes, expenses, documents, habits, goals, shopping, borrow

### Key Frontend Patterns
- Global `TaskNest` namespace for JS modules
- CSS custom properties for theming
- Vanilla JS (no frameworks, no jQuery)
- Chart.js via CDN for data visualization
- Event delegation for dynamic content
- Filter toggle pattern: `display:none` by default, `.open` class shows
- Cache-busting on CSS files via `?v=filemtime()` in header.php

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
1. **Root entry point** (e.g., `tasks.php`) — Handles POST routing, includes module view
2. **Module view** (`modules/tasks/index.php`) — HTML template with filters, list, pagination
3. **Module handler** (`modules/tasks/tasks.php`) — POST/AJAX processing, returns JSON
4. **Functions** (`includes/functions.php`) — Business logic, database queries
5. **CSS** (`assets/css/tasks.css`) — Module-specific styles
6. **JS** (`assets/js/tasks.js`) — Module-specific interactions

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
- Two-Factor Authentication (TOTP) with backup codes

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

---

## Author

**Name** — Chetan Pawar

**Email** — chetanpawar8125@email.com

**Project Link** — [https://github.com/Chetan-Pawar18706/TaskNest](https://github.com/Chetan-Pawar18706/TaskNest)
