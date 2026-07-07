# TaskNest

All-in-one Personal Life Management System built with PHP 8+, MySQL, vanilla JavaScript, and custom CSS.

![PHP](https://img.shields.io/badge/PHP-8%2B-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=flat&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-Vanilla-F7DF1E?style=flat&logo=javascript&logoColor=black)
![License](https://img.shields.io/badge/License-MIT-green)

---

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Tech Stack](#tech-stack)
- [Database Schema](#database-schema)
- [Security Features](#security-features)
- [Browser Support](#browser-support)
- [Contributing](#contributing)
- [License](#license)
- [Author](#author)

---

## Features

### Authentication & Security

| Feature | Description |
| --- | --- |
| User Registration | Username, email, password with validation |
| Login | Email + password with Remember Me (7-day cookie) |
| Forgot/Reset Password | Token-based password reset via email |
| Two-Factor Auth (2FA) | TOTP-based 2FA compatible with Google Authenticator / Authy |
| 2FA Setup Wizard | 3-step setup: Intro, QR code scan, backup codes download |
| Backup Codes | 8 single-use backup codes hashed with password_hash() |
| Session Security | HttpOnly, Secure, SameSite=Lax cookies, 30-min timeout |
| CSRF Protection | Token verification on all POST requests |

### Dashboard

| Feature | Description |
| --- | --- |
| Welcome Hero | Greeting card with user name and quick action buttons |
| 10 Stat Cards | Tasks, completed, pending, notes, monthly expense, documents, habits, goals, shopping, borrow |
| Expense Trends Chart | 6-month income vs expenses line chart (Chart.js) |
| Task Completion Chart | Doughnut chart showing task status distribution |
| Habit Progress Chart | Weekly activity bar chart (Chart.js) |
| Mini Calendar | Calendar with event counts and navigation |
| Recent Activity | Timeline of user actions across all modules |
| Upcoming Reminders | Panel showing pending reminders with priority indicators |

### Tasks Module

| Feature | Description |
| --- | --- |
| Full CRUD | Create, read, update, delete tasks |
| Categories | Custom categories with colors and icons |
| Priority Levels | Low, Medium, High, Urgent |
| Statuses | Pending, In Progress, Completed |
| Due Dates | With optional reminder datetimes |
| 4 Views | List, Grid, Kanban, Table views |
| Bulk Actions | Complete, delete, change category for multiple tasks |
| Task Activity Log | Track changes to tasks |
| Soft Delete | Tasks moved to trash, not permanently deleted |
| Filter Panel | Search, status, priority, category, date range, sort (toggleable) |

### Smart Notes Module

| Feature | Description |
| --- | --- |
| Rich Text Content | Full text editing for notes |
| Categories | Custom categories with colors |
| Pin Notes | Pin important notes to top |
| Archive | Archive old notes |
| Soft Delete (Trash) | Move to trash, restore or permanently delete |
| Image Attachments | Attach images to notes |
| Full-Text Search | Search across note titles and content |
| Filter Panel | Search, category, status filters (toggleable) |

### Expense Manager Module

| Feature | Description |
| --- | --- |
| Income & Expense Tracking | Record both income and expenses |
| Custom Categories | Categories with colors |
| Budget Management | Monthly/weekly/yearly budgets per category |
| Income vs Expenses Chart | Line chart showing trends over time |
| Category Breakdown Chart | Pie chart of expenses by category |
| CSV Export | Export transactions to CSV file |
| Recurring Transactions | Set up recurring income/expenses |
| Filter Panel | Search, type, category, date range filters (toggleable) |

### Document Vault Module

| Feature | Description |
| --- | --- |
| Secure File Upload | Upload PDF, images, documents |
| File Validation | Type and size validation (5MB max) |
| Categories | Organize documents in categories |
| Expiry Date Tracking | Track document expiry dates |
| Reminder Dates | Set reminders for document renewals |
| Important Flag | Mark documents as important |
| Preview and Download | View and download uploaded files |
| Filter Panel | Search, category, type filters (toggleable) |

### Password Manager Module

| Feature | Description |
| --- | --- |
| Encrypted Vault | AES-256-CBC encryption derived from user password |
| Vault Lock/Unlock | 30-minute auto-lock timeout |
| Password Generator | Configurable password generation (length, characters) |
| Categories | Organize passwords in categories |
| Favorites | Mark frequently used passwords |
| Bulk Actions | Select and manage multiple passwords |
| Filter Panel | Search, category, favorites filters (toggleable) |

### Habits Module

| Feature | Description |
| --- | --- |
| Frequency Tracking | Daily, weekly, monthly habit tracking |
| Target Counts | Set target completions per period |
| Streak Visualization | 7-day streak grid with color coding |
| Weekly Activity Chart | Bar chart showing habit completions |
| Color Coding | Custom colors for habits |
| Search Filter | Quick search across habits |

### Goals Module

| Feature | Description |
| --- | --- |
| Progress Tracking | Target values with current progress |
| Categories | Custom goal categories |
| Start/Due Dates | Track goal timelines |
| Status Tracking | Active, completed, abandoned |
| Progress Bar | Visual completion percentage |
| Filter Panel | Search, status, category filters (toggleable) |

### Shopping List Module

| Feature | Description |
| --- | --- |
| Item Management | Add items with name and quantity |
| Price Tracking | Estimated vs actual price comparison |
| Categories | Organize items in categories |
| Completion Toggle | Checkbox to mark items as bought |
| Notes | Add notes to shopping items |
| Filter Panel | Search, category, status filters (toggleable) |

### Borrow and Lend Module

| Feature | Description |
| --- | --- |
| Item Tracking | Track borrowed or lent items/money |
| Person Details | Name and contact information |
| Date Tracking | Borrow and return dates |
| Status Management | Pending, returned, overdue |
| Overdue Alerts | Visual indicators for overdue items |
| Filter Panel | Search, type, status filters (toggleable) |

### Calendar Events Module

| Feature | Description |
| --- | --- |
| Event Management | Add, edit, delete calendar events |
| Dashboard Integration | Events shown in dashboard mini calendar |
| Date Navigation | Navigate through months |

### Reminders Module

| Feature | Description |
| --- | --- |
| Date/Time Reminders | Set reminders with specific dates |
| Repeat Types | None, daily, weekly, monthly, yearly |
| Priority Levels | Set reminder priorities |
| Categories | Organize reminders in categories |
| Email Notifications | Send email reminders |
| Bell Notifications | In-app notification system |
| Bulk Delete | Select and delete multiple reminders |

### Feedback Module

| Feature | Description |
| --- | --- |
| Submit Feedback | Users can submit bug reports, feature requests, improvements |
| Track Status | View feedback status (Open, In Progress, Resolved, Closed) |
| Admin View Status | See if admin has viewed your feedback |
| Admin Replies | View admin responses with full date and time |
| Feedback Details | Full timeline: submitted date, updated date, reply date |
| Delete Feedback | Delete open feedback items |
| Stats Overview | Total, Open, In Progress, Resolved counts |

### Admin Panel

| Feature | Description |
| --- | --- |
| Dashboard | User registration chart, module usage statistics |
| User Management | Searchable user list, activate/deactivate users |
| Activity Log | Paginated audit trail of all user actions |
| Feedback System | User feedback with admin replies and status management |
| Site Settings | Dynamic configuration (site name, maintenance mode, registration toggle) |

### UI Features

| Feature | Description |
| --- | --- |
| Dark/Light Theme | Toggle from navbar or settings, persisted to database per user |
| Responsive Design | Mobile-first with collapsible sidebar (breakpoints: 1024px, 768px, 480px) |
| Filter Panels | Toggleable filter forms on all module pages (hidden on mobile by default) |
| Default Profile Avatar | SVG fallback for users without profile pictures |
| Toast Notifications | Non-intrusive success/error messages |
| Confirm Modals | For destructive actions (delete, etc.) |
| Cache Busting | CSS/JS files loaded with version-based cache busting |

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

Place the TaskNest folder in your web server's document root.

### 2. Create Database

Open phpMyAdmin and import the schema:

Option A: Import via phpMyAdmin

1. Open phpMyAdmin
2. Click Import tab
3. Choose database/tasknest.sql
4. Click Go

Option B: Import via command line

```bash
mysql -u root -p tasknest < database/tasknest.sql
```

### 3. Configure Database

Edit config/db.php with your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'tasknest');
```

### 4. Set Up Admin User

Create an admin account through the application or your database tool, then assign the admin role to that user.

### 5. Access the Application

Navigate to http://localhost/TaskNest/ in your browser.

---

## Tech Stack

| Layer | Technology |
| --- | --- |
| Backend | PHP 8+ (procedural with prepared statements) |
| Database | MySQL with InnoDB, utf8mb4_unicode_ci |
| Frontend | HTML5, CSS3 (custom properties), Vanilla JavaScript |
| Charts | Chart.js (CDN) |
| 2FA | TOTP (RFC 6238) - pure PHP implementation |
| Encryption | AES-256-CBC for password vault (via openssl) |
| Password Hashing | Argon2id |
| Security | CSRF tokens, XSS protection, HttpOnly cookies, prepared statements |

---

## Database Schema

The application uses 25+ tables with InnoDB engine and utf8mb4_unicode_ci collation.

### Core Tables

| Table | Purpose |
| --- | --- |
| users | User accounts with roles (user/admin), theme preference |
| password_resets | Password reset tokens |
| sessions | Remember-me tokens |
| settings | User preferences (notifications, 2FA enabled/secret/backup codes) |
| activity_logs | Audit trail |
| notifications | User notifications |
| calendar_events | Calendar entries |

### Module Tables

| Module | Tables |
| --- | --- |
| Tasks | tasks, task_categories, task_activity_logs |
| Notes | notes, note_categories, note_images |
| Expenses | expenses, expense_categories, budgets |
| Documents | documents, document_categories |
| Habits | habits, habit_logs |
| Goals | goals |
| Shopping | shopping |
| Borrow | borrow_items |
| Reminders | reminders |
| Passwords | saved_passwords, password_categories |
| Feedback | feedback (with admin replies, viewed status) |
| Admin | site_settings (dynamic) |

### Key Design Patterns

- Soft deletes (is_deleted column) on all module tables
- Foreign keys with ON DELETE CASCADE or ON DELETE SET NULL
- Composite indexes for common query patterns
- Timestamps on all tables (created_at, updated_at)

---

## Security Features

### Application Level

- Prepared statements for all SQL queries (no string concatenation)
- CSRF token verification on all POST requests
- XSS protection via htmlspecialchars() with ENT_QUOTES
- Argon2id password hashing
- Session security (HttpOnly, Secure, SameSite, 30-min timeout)
- File upload validation (type, size, MIME)
- Role-based access control for admin features
- Soft deletes (no data actually removed)
- Two-Factor Authentication (TOTP) with backup codes
- AES-256-CBC encryption for password vault

### Server Level (.htaccess)

- Directory browsing disabled
- Protected directories: config/, database/, logs/
- PHP execution blocked in uploads/
- Security headers enabled
- Server signature disabled

---

## Browser Support

| Browser | Minimum Version |
| --- | --- |
| Chrome | 90+ |
| Firefox | 88+ |
| Safari | 14+ |
| Edge | 90+ |

---

## Contributing

1. Fork the repository
2. Create your feature branch (git checkout -b feature/amazing-feature)
3. Commit your changes (git commit -m 'Add some amazing feature')
4. Push to the branch (git push origin feature/amazing-feature)
5. Open a Pull Request

---

## License

MIT License

---

## Author

**Name** - Chetan Pawar

**Email** - chetanpawar8125@email.com

**Project Link** - [https://github.com/Chetan-Pawar18706/TaskNest](https://github.com/Chetan-Pawar18706/TaskNest)
<<<<<<< HEAD
#   T a s k N e s t  
 #   T a s k N e s t  
 #   T a s k N e s t  
 #   T a s k N e s t  
 #   T a s k N e s t  
 
=======
#
>>>>>>> 441bab303e25e1fb45620d60dfa5d630c29976cd
#   T a s k N e s t  
 