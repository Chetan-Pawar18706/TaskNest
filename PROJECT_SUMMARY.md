# TaskNest - Complete Project Summary

## Overview

TaskNest is a full-stack, server-rendered web application for personal life management. It is a traditional PHP multi-page application where each URL maps to a `.php` file that renders a full HTML page.

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
│   ├── reminders/
│   │   ├── index.php                   # Reminders list with filter toggle
│   │   ├── reminders.php               # Reminders POST/AJAX handler
│   │   ├── reminders-add.php           # Add reminder form
│   │   └── reminders-edit.php          # Edit reminder form
│   ├── passwords/
│   │   ├── index.php                   # Password vault with filter toggle
│   │   ├── passwords.php               # Passwords POST/AJAX handler
│   │   ├── passwords-add.php           # Add password form
│   │   ├── passwords-edit.php          # Edit password form
│   │   └── password-categories.php     # Category management
│   └── admin/
│       ├── index.php                   # Admin panel view (dashboard, users, activity, feedback, settings)
│       ├── admin.php                   # Admin POST handler (AJAX)
│       └── admin-root.php              # Admin entry point (auth check, routing)
├── assets/
│   ├── css/ (18 files)
│   │   ├── variables.css               # CSS custom properties (colors, spacing, typography)
│   │   ├── reset.css                   # CSS reset and normalization
│   │   ├── styles.css                  # Main component styles, layout, 2FA styles
│   │   ├── responsive.css              # Mobile breakpoints
│   │   ├── theme.css                   # Dark/light theme variables
│   │   ├── components.css              # Reusable component styles (modal, sidebar profile)
│   │   ├── landing.css                 # Landing page styles
│   │   ├── dashboard.css               # Dashboard-specific styles
│   │   ├── tasks.css                   # Tasks module
│   │   ├── notes.css                   # Notes module
│   │   ├── expenses.css                # Expenses module
│   │   ├── documents.css               # Documents module
│   │   ├── habits.css                  # Habits module
│   │   ├── goals.css                   # Goals module
│   │   ├── shopping.css                # Shopping module
│   │   ├── borrow.css                  # Borrow module
│   │   ├── reminders.css               # Reminders module
│   │   ├── passwords.css               # Password manager module
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
│       ├── borrow.js                   # Borrow CRUD
│       ├── reminders.js                # Reminders CRUD
│       └── passwords.js                # Password vault CRUD, encryption
├── uploads/
│   ├── documents/                      # Uploaded documents
│   └── profile/                        # Profile pictures
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
├── feedback.php                        # User feedback page (submit, track, view replies)
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
├── reminders.php                       # Reminders entry point
├── passwords.php                       # Password vault entry point
├── .htaccess                           # Apache security
├── .gitignore                          # Git ignore rules
├── README.md                           # Project documentation
└── PROJECT_SUMMARY.md                  # This file
```

**File counts:**
- 21 root-level PHP files
- 43 module PHP files (12 modules)
- 7 include PHP files
- 1 config PHP file
- 18 CSS files
- 14 JavaScript files
- 1 SQL schema file
- **Total: ~105 files**

---

## Authentication System

**File:** `includes/auth.php`

The `Auth` class handles all authentication operations:

### Session Security
- Strict mode enabled
- Cookies only (no URL session IDs)
- HttpOnly, Secure, SameSite=Lax cookies
- 30-minute session timeout
- Session regeneration on login

### Registration
- Username: 3-50 characters, unique
- Email: valid format, unique
- Password: 8+ characters, must contain uppercase letter and number
- Password hashing: Argon2id
- Auto-creates settings row for new user
- Logs registration activity

### Login
- Validates email format and password presence
- Checks `is_active` status
- Uses `password_verify()` against Argon2id hash
- Regenerates session ID on success
- Generates CSRF token
- Optional "remember me" (7 days) with token in `sessions` table + HttpOnly cookie
- Logs login activity

### Two-Factor Authentication (TOTP)
- Generates Base32 secret key
- Generates otpauth:// URI for QR code
- Generates current 6-digit TOTP code (HMAC-SHA1)
- Verifies code with plus/minus 1 time window tolerance
- Generates 8 backup codes (hashed)
- Verifies and consumes a backup code
- Toggle 2FA enable/disable
- Login 2FA flow management

### Password Reset
- Token-based via `password_resets` table
- 1-hour token expiry
- Sends reset link via email
- Validates token, updates password, marks token as used

### CSRF Protection
- Creates 64-char hex token in session
- Compares submitted token against session value
- Used on all POST forms

### Activity Logging
- Logs user actions to `activity_logs` table
- Records IP address and user agent

---

## Database Schema

**File:** `database/tasknest.sql`

All tables use InnoDB engine with utf8mb4_unicode_ci collation.

### Core Tables
| Table | Purpose |
|-------|---------|
| `users` | User accounts with roles (user/admin), theme preference |
| `password_resets` | Password reset tokens |
| `sessions` | Remember-me tokens |
| `settings` | User preferences (notifications, 2FA enabled/secret/backup codes) |
| `activity_logs` | Audit trail |
| `notifications` | User notifications |
| `calendar_events` | Calendar entries |

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
| `reminders` | Date/time reminders with repeat |
| `saved_passwords` | Encrypted password vault entries |
| `password_categories` | Password vault categories |

### Dynamic Tables (created by PHP)
- `feedback` -- User feedback with admin replies, viewed status
- `site_settings` -- Key/value site configuration

### Key Design Patterns
- Soft deletes (`is_deleted` column) on all module tables
- Foreign keys with `ON DELETE CASCADE` or `ON DELETE SET NULL`
- Composite indexes for common query patterns
- Timestamps on all tables (created_at, updated_at)

---

## Modules

### 1. Tasks Module
- Full CRUD operations
- Categories with custom colors
- Priority levels (Low, Medium, High, Urgent)
- Statuses (Pending, In Progress, Completed)
- Due dates with reminders
- 4 views: List, Grid, Kanban, Table
- Bulk actions (complete, delete, change category)
- Task activity logging
- Toggleable filter panel

### 2. Smart Notes Module
- Rich text content
- Categories with colors
- Pin important notes
- Archive old notes
- Soft delete (trash)
- Image attachments
- Full-text search
- Toggleable filter panel

### 3. Expense Manager Module
- Income and expense tracking
- Custom categories with colors
- Budget management (monthly/weekly/yearly)
- Charts: Income vs Expenses (line), Category Breakdown (pie)
- CSV export
- Recurring transactions
- Toggleable filter panel

### 4. Document Vault Module
- Secure file upload (PDF, images, docs)
- File type and size validation
- Categories
- Expiry date tracking
- Reminder dates
- Important flag
- Preview and download
- Toggleable filter panel

### 5. Habits Module
- Daily/weekly/monthly frequency
- Target count per period
- Streak visualization (7-day grid)
- Weekly activity bar chart (Chart.js)
- Color coding
- Toggleable search filter

### 6. Goals Module
- Target values with current progress
- Categories
- Start/due dates
- Status tracking (active, completed, abandoned)
- Completion percentage with progress bar
- Toggleable filter panel

### 7. Shopping Module
- Item name and quantity
- Estimated vs actual price
- Categories
- Mark as complete (checkbox)
- Notes
- Toggleable filter panel

### 8. Borrow & Lend Module
- Track borrowed or lent items/money
- Person name and contact
- Borrow and return dates
- Status tracking (pending, returned, overdue)
- Overdue alerts
- Toggleable filter panel

### 9. Events Module
- Calendar events with add/edit
- Linked from dashboard calendar

### 10. Reminders Module
- Date/time reminders with repeat types (none/daily/weekly/monthly/yearly)
- Priority levels
- Categories
- Email notifications
- Bell notification system
- Bulk delete
- Toggleable filter panel

### 11. Password Manager Module
- Encrypted password vault (AES-256-CBC derived from user password)
- Vault lock/unlock mechanism (30-min timeout)
- Password generator with configurable options
- Categories
- Favorites
- Bulk actions
- Toggleable filter panel

### 12. Feedback Module
- Submit feedback (bug reports, feature requests, improvements)
- Track feedback status (Open, In Progress, Resolved, Closed)
- See if admin has viewed your feedback
- View admin replies with full date and time
- Full details timeline (submitted, updated, reply dates)
- Delete open feedback
- Stats overview (total, open, in progress, resolved)

### 13. Admin Panel
- **Dashboard tab:** User registration chart, module usage chart
- **Users tab:** Searchable user list, activate/deactivate toggle
- **Activity Log tab:** Paginated audit trail
- **Feedback tab:** User feedback with admin replies, status management, viewed indicator
- **Settings tab:** Dynamic site configuration

---

## Dashboard

- Welcome hero with quick action buttons
- 10 stat cards
- 3 charts: Expense trends, Task completion, Habit progress
- Mini calendar with event counts
- Recent activity timeline
- Upcoming reminders

---

## Two-Factor Authentication (2FA)

### Setup Flow
1. **Step 1:** Introduction to 2FA
2. **Step 2:** QR code for scanning + manual secret key
3. **Step 3:** 8 one-time backup codes

### Login Flow
1. User submits email + password
2. If 2FA enabled -> redirect to verification page
3. User enters 6-digit TOTP code OR backup code
4. On success -> session created

---

## Frontend Architecture

### CSS (18 files)
- `variables.css` -- CSS custom properties
- `reset.css` -- CSS reset and normalization
- `styles.css` -- Main component styles, layout
- `responsive.css` -- Mobile breakpoints (1024px, 768px, 480px)
- `theme.css` -- Dark/light theme variable overrides
- `components.css` -- Reusable component styles
- `landing.css` -- Landing page styles
- Module-specific stylesheets

### JavaScript (14 files)
- `main.js` -- Core utilities
- `theme.js` -- Dark/light theme toggle
- `sidebar.js` -- Sidebar collapse/expand
- `toast.js` -- Toast notification system
- `modal.js` -- Modal dialog management
- Module-specific scripts

### Key Frontend Patterns
- CSS custom properties for theming
- Vanilla JS (no frameworks)
- Chart.js via CDN for data visualization
- Event delegation for dynamic content
- Cache-busting on CSS files

---

## Security

### Application Level
- Prepared statements for all SQL queries
- CSRF token verification on all POST requests
- XSS protection via `htmlspecialchars()` with `ENT_QUOTES`
- Argon2id password hashing
- Session security (HttpOnly, Secure, SameSite, 30-min timeout)
- File upload validation (type, size, MIME)
- Role-based access control for admin features
- Soft deletes
- Two-Factor Authentication (TOTP) with backup codes
- AES-256-CBC encryption for password vault

### Server Level (.htaccess)
- Directory browsing disabled
- Protected directories: config/, database/, logs/
- PHP execution blocked in uploads/
- Security headers enabled
- Server signature disabled

### Database
- All queries use prepared statements with bound parameters
- Foreign key constraints enforce referential integrity
- CASCADE deletes prevent orphaned records
- UTF8MB4 charset for full Unicode support

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

**Name** - Chetan Pawar

**Email** - chetanpawar8125@email.com

**Project Link** - [https://github.com/Chetan-Pawar18706/TaskNest](https://github.com/Chetan-Pawar18706/TaskNest)
