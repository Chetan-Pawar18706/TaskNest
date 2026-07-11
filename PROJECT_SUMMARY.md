# TaskNest — Complete Project Summary

## Overview
TaskNest is an all-in-one personal life management system built with PHP, MySQL, and vanilla JavaScript. It provides **8 integrated modules** for managing daily life from a single beautiful dashboard with **350+ distinct features**.

## Tech Stack
- **Backend:** PHP 7.4+ / MySQL (MySQLi with prepared statements)
- **Frontend:** HTML5, CSS3 (Custom Properties), Vanilla JavaScript
- **Auth:** Session-based with CSRF, 2FA (TOTP), bcrypt password hashing
- **Email:** PHPMailer via SMTP
- **Charts:** Chart.js for data visualization
- **Hosting:** Shared hosting compatible (InfinityFree, 000webhost, etc.)

---

## Complete Feature List

---

### 1. LANDING PAGE

- Animated hero section with floating particle effects
- Navigation bar with smooth scroll to sections
- Mobile hamburger menu toggle
- Navbar scroll effect (transparent → solid on scroll)
- "Features" section showcasing all 8 modules with SVG icons
- "How It Works" 3-step guide section
- Social proof / reviews section with 5-star ratings
- CTA (Call to Action) section
- Footer with navigation links
- Scroll-triggered fade-in animations (IntersectionObserver)
- Smooth scroll for anchor links
- Auto-close mobile menu on link click

---

### 2. AUTHENTICATION SYSTEM

#### 2.1 Login
- Email + password authentication
- CSRF token protection (session + cookie fallback for shared hosting)
- Rate limiting: 5 attempts per email per 15 min, 20 per IP per 15 min
- "Remember me" checkbox (7-day persistent cookie)
- Session regeneration on successful login (prevents session fixation)
- 2FA redirect flow (if 2FA enabled)
- Link to register page
- Link to forgot password page
- Error display for invalid credentials
- Account deactivation check
- Activity logging on login (IP address, user agent, timestamp)

#### 2.2 Registration
- Fields: first name, last name, username, email, password, confirm password
- Username validation: 3-50 characters, letters and numbers
- Password strength validation: min 8 chars, must include uppercase + number
- Email validation
- Duplicate username/email check
- Rate limiting: 3 registrations per IP per hour
- Auto-creates settings row for new user
- CSRF protection
- Activity logging on registration

#### 2.3 Forgot Password
- Email input for password reset request
- Rate limiting: 3 per email per hour, 10 per IP per hour
- Sends password reset email with unique token
- Always shows same success message (security — doesn't reveal if email exists)
- Rate limit notification email sent when limit exceeded
- 1-hour token expiry
- CSRF protection

#### 2.4 Reset Password
- Token-based password reset (from email link)
- New password + confirm password fields
- Password strength validation
- Token validation (checks expiry and is_used flag)
- Marks token as used after successful reset
- Redirects to login with success message
- CSRF protection

#### 2.5 Two-Factor Authentication Setup
- Step 1: Introduction with 3-step visual guide
- Step 2: QR code generation for authenticator apps (Google Authenticator, Authy, etc.)
  - Manual secret key display with copy-to-clipboard button
  - 6-digit code verification input
  - Auto-format input (numbers only)
- Step 3: Backup codes display
  - 8 backup codes (8 characters each)
  - Copy all codes button
  - Download backup codes as text file
  - Warning that codes won't be shown again
- Disable 2FA: requires password confirmation
- TOTP implementation (HMAC-SHA1, 30-second window, ±1 time drift tolerance)
- Status badge (Enabled/Disabled)

#### 2.6 Two-Factor Verification
- 6-digit TOTP code input
- 8-character backup code support (auto-detected by length)
- Auto-format input (alphanumeric only)
- Back to login link
- Session-based pending 2FA state

#### 2.7 Logout
- Session destruction
- Remember me cookie deletion
- Activity logging
- Redirect to login with success message

---

### 3. DASHBOARD

#### 3.1 Hero Section
- Personalized welcome message (first name or username)
- Subtitle text
- Quick action buttons: "New Task", "Add Note"

#### 3.2 Quick Actions Grid (6 buttons)
- New Task
- New Note
- Add Expense
- Upload Document
- Add Habit
- Add Goal

#### 3.3 Overview Stats Cards (10 cards)
- Total Tasks (with icon, trend label)
- Completed Tasks (success tone)
- Pending Tasks (warning tone if > 0)
- Total Notes
- Monthly Expense (formatted as currency)
- Documents Stored
- Active Habits
- Active Goals
- Shopping Items
- Borrow Items

#### 3.4 Income vs Expenses Chart
- Bar/line chart (Chart.js)
- Range selector: 3M, 6M, 12M buttons
- Dynamic AJAX data loading on range change
- Income (green) vs Expenses (red) bars

#### 3.5 Task Completion Chart
- Doughnut/pie chart (completed vs pending)
- Color-coded segments

#### 3.6 Habit Progress Chart
- Bar chart showing weekly consistency (Mon–Sun)

#### 3.7 Calendar Widget
- Monthly calendar grid with event dots
- Event count for current month
- "+ Add Event" button
- Hover tooltips showing event titles and descriptions
- Today highlighting

#### 3.8 Recent Activity Timeline
- Last 10 activities with action icons
- Action type, description, time ago
- Empty state with icon when no activity

#### 3.9 Upcoming Reminders Panel
- Overdue reminders (red border, danger color)
- Upcoming reminders with urgency colors (urgent/soon/info)
- Reminder title, date, time, time label
- "+ Add" button
- Empty state

---

### 4. TASKS MODULE

#### 4.1 Task List View
- 4 view modes: List, Grid, Kanban, Table
- Search: text search across title and description
- Filters (collapsible panel):
  - Status: All / Pending / In Progress / Completed
  - Priority: All / Low / Medium / High
  - Category: dropdown (dynamic from user's categories)
  - Date Range: Today / This Week / This Month
  - Checkboxes: Overdue, Due Today, Due This Week
  - Sort: Due Date / Priority / Status / Created / Alphabetical
- Summary cards: Total, Pending, Completed, Overdue
- Bulk actions:
  - Bulk Complete (marks selected as completed)
  - Bulk Delete (soft-deletes selected)
  - Change Category (assigns category to selected tasks)
- Select all checkbox (table view)
- Individual checkboxes per task (all views)
- Pagination with page links

#### 4.2 Task Card Details
- Title, description preview
- Priority badge (Low/Medium/High with color coding)
- Status badge (Pending/In Progress/Completed)
- Category name with color
- Due date
- Attachment link (if present)
- Edit button
- Complete toggle button
- Delete button (with confirmation modal)
- Time ago display

#### 4.3 Kanban Board View
- 3 columns: Pending, In Progress, Completed
- Task cards in each column with checkbox, title, category
- Edit/Delete actions per card

#### 4.4 Table View
- Columns: Checkbox, Title, Category, Priority (badge), Status (badge), Due Date, Actions
- Edit/Delete per row

#### 4.5 Add Task
- Title (required)
- Description (textarea)
- Priority (Low/Medium/High select)
- Status (Pending/In Progress/Completed select)
- Category (dropdown from user's categories)
- Due Date (date picker)
- Reminder (datetime-local picker)
- Attachment (file upload)
  - Allowed: PDF, PNG, JPG, JPEG, DOC, DOCX, XLS, XLSX, TXT, ZIP
  - Max 10MB
- Create/Cancel buttons
- CSRF protection
- Flash messages (success/error)

#### 4.6 Edit Task
- Same fields as add, pre-populated with existing data
- Current attachment display with View link
- Remove attachment checkbox
- Replace attachment option
- Save Changes/Cancel buttons

#### 4.7 Task Categories
- Add category form: Name (required), Color (color picker), Icon (text)
- Existing categories list: color dot, name, icon
- Edit button → opens modal with pre-filled form
- Delete button → confirmation modal (warns tasks become uncategorized)
- CSRF protection
- Flash messages

#### 4.8 Task AJAX Actions
- save_task — create or update task
- delete_task — soft delete (move to trash)
- restore_task — restore from trash
- permanent_delete_task — hard delete (permanent)
- duplicate_task — clone with "(Copy)" suffix
- update_status — toggle complete/pending
- bulk_action — complete/delete/change category on multiple tasks
- save_category — create/update category
- delete_category — soft delete category
- get_task — fetch single task details
- get_category — fetch single category
- get_categories — fetch all user categories

---

### 5. SMART NOTES MODULE

#### 5.1 Notes List View
- Grid layout for note cards
- Filters (collapsible panel):
  - Search (text search across title and content)
  - Category dropdown
  - Show Archived checkbox
  - Show Trash checkbox
- Summary cards: Total, Pinned, Archived, Trashed
- Bulk actions: Archive, Delete
- Pagination
- Pinned notes display pin icon and highlighted style

#### 5.2 Note Card Details
- Title
- Content preview (HTML stripped, truncated)
- Time ago
- Category badge (with color)
- Attachment link (if present)
- Edit, Pin/Unpin, Archive/Unarchive, Delete buttons
- Delete Forever (when viewing trash)

#### 5.3 Add Note
- Title (required, max 255 chars)
- Category dropdown
- Rich text editor with toolbar:
  - Bold, Italic, Underline
  - Bullet List, Numbered List
  - Heading (H2), Subheading (H3), Paragraph (P)
- Pin checkbox
- File attachment (same rules as tasks)
- Save/Cancel buttons

#### 5.4 Edit Note
- Same fields as add, pre-populated
- Current attachment with View link
- Remove/Replace attachment
- Rich text editor with existing content

#### 5.5 Note Categories
- Add/Edit form: Name (required), Color (picker + hex input)
- Color picker syncs with hex input (and vice versa)
- Category list with color dots
- Edit → populates form inline (cancel button appears)
- Delete → confirmation modal
- AJAX-based save/delete
- Empty state when no categories

#### 5.6 Note AJAX Actions
- save_note — create/update
- get_note — fetch single note
- delete_note — soft delete
- restore_note — restore from trash
- permanent_delete_note — hard delete
- toggle_pin — pin/unpin note
- toggle_archive — archive/unarchive
- duplicate_note — clone note
- bulk_action — archive/delete multiple notes
- save_category — create/update category
- delete_category — soft delete category
- get_categories — fetch all categories
- upload_image — upload image for note
- delete_image — delete note image

---

### 6. EXPENSE TRACKER MODULE

#### 6.1 Transaction List
- Filters (collapsible panel):
  - Search (text)
  - Type: All / Income / Expense
  - Category dropdown
  - Date From / Date To (date pickers)
- Summary cards: Income (this month), Expenses (this month), Balance, Transactions count
- 3 tabs: Transactions, Charts, Budgets

#### 6.2 Transactions Tab
- Transaction items with:
  - Income/Expense icon (up/down arrow)
  - Title, category name, notes preview
  - Amount (green for income, red for expense)
  - Transaction date
  - Edit / Delete buttons (with confirmation)
- Pagination

#### 6.3 Charts Tab
- Income vs Expenses bar/line chart (6-month history)
- Expense Breakdown pie/doughnut chart (by category)

#### 6.4 Budgets Tab
- Add Budget button → modal
- Budget cards grid showing budget limits
- Budget CRUD via AJAX

#### 6.5 Add Transaction
- Type (Income/Expense)
- Title (required)
- Amount (required)
- Category
- Transaction date
- Notes
- CSRF + flash messages

#### 6.6 Edit Transaction
- Same fields, pre-populated with existing data

#### 6.7 Expense Categories
- CRUD with color picker
- Same pattern as task/note categories

#### 6.8 CSV Export
- Export filtered expenses as CSV download
- Respects date filters and category filters

#### 6.9 Expense AJAX Actions
- save_expense — create/update
- get_expense — fetch single
- delete_expense — delete
- save_category / delete_category / get_categories
- chart_data — fetch chart data
- category_breakdown — pie chart data
- save_budget / get_budgets / delete_budget / get_budget
- export_csv (GET) — CSV download

---

### 7. DOCUMENT VAULT MODULE

#### 7.1 Document List (Grid view)
- Filters (collapsible panel):
  - Search (text)
  - Category dropdown
  - Expiring Soon checkbox
  - Expired checkbox
- Summary cards: Total, Important, Expiring Soon, Expired
- Document cards with:
  - File type icon (image/PDF/DOC/XLS/custom)
  - Title, description/original filename
  - File size (formatted as human-readable)
  - Category badge (with color)
  - Expiry status: Expired (red), Expiring soon (orange), OK (green)
  - Time ago
  - Actions: Preview (new tab), Download, Edit, Delete
- Pagination

#### 7.2 Upload Document
- Title (required)
- Description
- Category dropdown
- Expiry date (optional)
- Is Important checkbox
- File upload (multiple types allowed)
- File size limit

#### 7.3 Edit Document
- Same fields, pre-populated
- Current file with preview/download
- Replace/remove file options

#### 7.4 Document Categories
- CRUD with color picker
- Same pattern as other category managers

#### 7.5 Document AJAX Actions
- upload_document — upload new document
- get_document — fetch single
- update_document — update metadata
- delete_document — soft delete
- permanent_delete_document — hard delete
- save_category / delete_category / get_categories

---

### 8. HABIT TRACKER MODULE

#### 8.1 Habit List (Grid view)
- Search filter
- Summary cards: Total Habits, Active, Logged Today, Best Streak
- Habit cards with:
  - Habit name, frequency (Daily/Weekly)
  - Description
  - 7-day streak visualizer: clickable day dots (Mon–Sun)
    - Logged days highlighted with color
    - Today highlighted
    - Click to toggle log for any day
  - Logged today count, logged this week count
  - Log Today button
  - Edit button
  - Delete button (with confirmation)
- Weekly Activity chart (bar chart, Chart.js)

#### 8.2 Add Habit
- Name (required)
- Description
- Frequency (Daily/Weekly)
- Target value (optional)
- CSRF + flash messages

#### 8.3 Edit Habit
- Same fields, pre-populated

#### 8.4 Habit AJAX Actions
- save_habit — create/update
- get_habit — fetch single
- log_habit — log today's completion
- delete_habit — delete
- chart_data — weekly chart data

---

### 9. GOALS MODULE

#### 9.1 Goals List
- Filters (collapsible panel):
  - Search (text)
  - Status: All / Active / Completed / Abandoned
- Summary cards: Total, Active, Completed, Abandoned
- Goal cards with:
  - Title, status badge (active/completed/abandoned with colors)
  - Description
  - Progress bar with percentage (current value / target unit)
  - Start date, due date, category
  - Update Progress button (active goals only)
  - Edit button (active goals only)
  - Delete button (with confirmation)

#### 9.2 Add Goal
- Title (required)
- Description
- Category
- Target value + unit
- Start date, due date
- CSRF + flash messages

#### 9.3 Edit Goal
- Same fields, pre-populated

#### 9.4 Goal Categories
- CRUD with color picker

#### 9.5 Goal AJAX Actions
- save_goal — create/update
- update_progress — update current value
- delete_goal — delete
- save_category / delete_category

---

### 10. SHOPPING LIST MODULE

#### 10.1 Shopping List
- Filters (collapsible panel):
  - Search (text)
  - Show Completed checkbox
- Summary cards: Total Items, Pending, Completed, Estimated Total, Actual Spent
- Shopping items with:
  - Completion checkbox (toggle)
  - Item name
  - Category, notes preview
  - Quantity (if > 1)
  - Estimated price, Actual price
  - Edit / Delete buttons
- Footer: remaining items count, estimated total
- Clear Completed button (deletes all completed items)

#### 10.2 Add Item
- Name (required)
- Category
- Quantity
- Estimated price, Actual price
- Notes
- CSRF + flash messages

#### 10.3 Edit Item
- Same fields, pre-populated

#### 10.4 Shopping Categories
- CRUD with color picker

#### 10.5 Shopping AJAX Actions
- save_item — create/update
- get_item — fetch single
- toggle_complete — toggle completion status
- delete_item — delete
- clear_completed — delete all completed items
- save_category / delete_category

---

### 11. BORROW & LEND MODULE

#### 11.1 Borrow List
- Filters (collapsible panel):
  - Search (text)
  - Type: All / Borrowed / Lent
  - Status: All / Pending / Returned / Overdue
- Summary cards: Total Records, Pending Borrowed, Pending Lent, Overdue (warning), Total Lent ($), Total Borrowed ($)
- Borrow cards with:
  - Title
  - Type badge (Borrowed/Lent with colors)
  - Status badge (Pending/Returned/Overdue)
  - Amount (for money items, colored by type)
  - Person name, contact
  - Borrow date, expected return, actual return
  - Item type (Money/Item)
  - Description
  - Overdue warning banner
  - Edit, Mark Returned, Delete buttons
- Pagination

#### 11.2 Add Record
- Title (required)
- Type (Borrowed/Lent)
- Item Type (Money/Item)
- Amount (for money)
- Person name, contact
- Borrow date, return date
- Description
- CSRF + flash messages

#### 11.3 Edit Record
- Same fields, pre-populated

#### 11.4 Borrow AJAX Actions
- save_borrow — create/update
- get_borrow — fetch single
- delete_borrow — delete
- mark_returned — mark as returned with actual return date

---

### 12. PASSWORD VAULT MODULE

#### 12.1 Vault Lock Screen
- Account password verification to unlock vault
- 30-minute session timeout
- Lock icon and vault title
- "Back to Dashboard" link
- Error message on wrong password

#### 12.2 Password List
- Search input (real-time filtering)
- Category filter dropdown
- Favorites filter button
- Summary stats: Total, Favorites
- Bulk actions: Delete Selected
- Lock Vault button
- Passwords loaded via AJAX (JavaScript-rendered)
- Empty state

#### 12.3 Add Password
- Site/Service name (required)
- Username/Email
- Password
- Password Generator:
  - Length slider/input
  - Uppercase, Lowercase, Numbers, Symbols checkboxes
  - Generate button
  - Copy to clipboard
- URL
- Category
- Notes
- Favorite toggle
- CSRF + flash messages

#### 12.4 Edit Password
- Same fields, pre-populated
- Password visible/hidden toggle

#### 12.5 Password Categories
- CRUD with color picker

#### 12.6 Vault Security Features
- AES encryption for stored passwords (key derived from user's password)
- Vault unlock/lock via session
- 30-minute auto-lock timeout
- check_vault AJAX endpoint for JS timeout handling
- lock_vault endpoint for manual lock

#### 12.7 Password AJAX Actions
- unlock_vault — verify password, derive encryption key
- lock_vault — clear session vault data
- check_vault — check if vault is still unlocked
- generate_password — generate strong password
- save_password — create/update (encrypted)
- get_passwords — list all (decrypted for display)
- get_password — fetch single (decrypted)
- delete_password — delete
- toggle_favorite — toggle favorite status
- bulk_action — bulk delete
- save_category / delete_category / get_categories

---

### 13. REMINDERS MODULE

#### 13.1 Reminder Processing
- Automatic reminder processing on page load
- Checks tasks, goals, documents, borrow items for upcoming due dates
- Sends email notifications for pending reminders

#### 13.2 Reminder Bell Icon (Dashboard Navbar)
- Upcoming reminders dropdown
- Overdue reminders
- Unread count badge
- Quick view without leaving page

#### 13.3 Add Reminder
- Title, date, time
- Urgency level (urgent/soon/info)
- Linked entity type and ID
- Email notification toggle

---

### 14. CALENDAR / EVENTS

#### 14.1 Add Event
- Event title (required)
- Event date (required)
- Description
- Create/Cancel buttons

#### 14.2 Calendar Events AJAX
- save_calendar_event — create/update
- delete_calendar_event — delete
- Events rendered on dashboard calendar with tooltips
- Events stored in calendar_events table

---

### 15. FEEDBACK MODULE

#### 15.1 Feedback Page
- New Feedback button → toggleable form
- Feedback form:
  - Category (Bug Report, Feature Request, Improvement, Other)
  - Subject (required, max 255 chars)
  - Message (required, textarea)
  - Submit/Cancel buttons
  - Success/error alerts
- Feedback stats: Total, Open, In Progress, Resolved
- Feedback list with cards:
  - Subject, feedback ID (#)
  - Category badge
  - Time ago
  - Admin status badges: Pending/Viewed/Replied
  - Status badge: Open/In Progress/Resolved/Closed
  - Message content
  - Admin reply section (if replied)
  - Details section:
    - Submitted date/time
    - Last updated
    - Admin replied date
    - Status
    - Admin view status
  - Delete button (only for open status)
  - Confirmation dialog on delete

#### 15.2 Feedback AJAX Actions
- submit_feedback — submit new feedback
- delete_feedback — delete open feedback

---

### 16. SETTINGS MODULE

#### 16.1 Profile Picture
- Current avatar display (or default avatar)
- Upload new picture (PNG, JPG, GIF, WebP, max 5MB)
- Remove picture checkbox
- Old avatar auto-deleted on replace

#### 16.2 Personal Information
- First Name, Last Name
- Phone
- Bio (textarea)

#### 16.3 Preferences
- Timezone selector (12 options: UTC, US zones, London, Paris, Dubai, Bangkok, Shanghai, Tokyo, Sydney)
- Theme selector (Light/Dark)

#### 16.4 Notifications
- Enable/disable notifications checkbox

#### 16.5 Security
- 2FA status badge (Enabled/Disabled)
- Manage/Enable 2FA button → 2FA setup page

#### 16.6 Quick Theme Save
- AJAX save_theme action (no page reload)
- Applies theme immediately via JavaScript

#### 16.7 Feedback Link
- "Send Feedback" card linking to feedback page

#### 16.8 Actions
- Save Settings button
- Cancel (back to dashboard)
- CSRF protection
- Flash messages

---

### 17. USER PROFILE
- Profile picture (120px circle)
- Full name, email, join date
- Username, email, phone, timezone, bio
- Edit Profile button → settings page
- Back to Dashboard button

---

### 18. ADMIN PANEL

#### 18.1 Admin Stats Dashboard
- Total Users, Active Users
- Total Tasks, Total Notes, Expenses, Documents
- Open Feedback count

#### 18.2 Admin Tabs (5 tabs)
- Dashboard Tab:
  - User Registrations chart (over time)
  - Module Usage chart (tasks, notes, expenses, etc.)

- Users Tab:
  - Search users
  - Table: ID, Username, Email, Name, Status (Active/Inactive), Joined date, Actions
  - Toggle user active/inactive button

- Activity Log Tab:
  - Paginated activity logs
  - Action type, description, username, time ago

- Feedback Tab:
  - Filter by status (Open/In Progress/Resolved/Closed)
  - Feedback cards with subject, message, user, category, time
  - Admin reply input
  - Status change dropdown
  - Reply button
  - Auto-marks feedback as "viewed by admin"

- Settings Tab:
  - Dynamic settings form (boolean/number/text types)
  - Save settings button

#### 18.3 Admin AJAX Actions
- toggle_user — activate/deactivate user
- deactivate_user — delete user
- reply_feedback — reply to feedback with status change
- save_settings — save site settings

#### 18.4 Access Control
- Admin role check (role === 'admin')
- Redirect non-admins to dashboard
- Admin link only visible in sidebar for admin users

---

### 19. CROSS-CUTTING / SYSTEM FEATURES

#### 19.1 Security
- CSRF protection: token in session + cookie fallback, verified on all POST requests
- SQL injection prevention: prepared statements everywhere
- XSS prevention: htmlspecialchars() on all output, sanitize() function
- Rate limiting: configurable per-identifier limits with time windows
- Password hashing: bcrypt (PASSWORD_BCRYPT)
- Session security: strict mode, httponly cookies, sameSite=Lax, session regeneration on login
- File upload validation: extension whitelist, MIME type check, file size limits
- Admin role-based access control
- Content Security Policy headers (CSP)
- X-Content-Type-Options header
- X-Frame-Options header (clickjacking prevention)
- Referrer-Policy header
- Permissions-Policy header (camera, microphone, geolocation disabled)
- Server signature disabled

#### 19.2 UI/UX
- Theme system: Light/Dark mode with CSS custom properties
- Responsive design: mobile-first, breakpoints for tablet/desktop
- Collapsible filter panels on all list pages
- Confirmation modals for all delete actions
- Toast notifications for success/error/info messages
- Empty states with icons and CTA buttons for every module
- Summary stat cards on every list page
- Pagination on all paginated lists
- Time ago formatting throughout ("2 hours ago", "3 days ago")
- SVG icons throughout (no icon library dependency, zero external requests)
- CSS variables for theming
- Card-based layouts with hover effects
- Smooth transitions and animations
- Form validation with inline error messages

#### 19.3 Sidebar Navigation
- Collapsible sidebar (toggle button)
- Active page highlighting
- Navigation sections: Main, Organize, Track, Utilities, Admin
- User info display with avatar
- Settings and Security links
- Logout button
- Version info display

#### 19.4 Header
- Page title with site name
- CSS asset loading with cache busting
- Theme application via data-theme attribute
- Meta tags (charset, viewport, description)
- Favicon

#### 19.5 Footer
- JavaScript asset loading
- CSRF token and site URL global variables
- Global confirmation modal
- Chart.js library inclusion

#### 19.6 JavaScript Architecture
- main.js — global utilities
- theme.js — light/dark theme toggle with persistence
- toast.js — toast notification system
- modal.js — confirmation modal system
- sidebar.js — sidebar toggle and responsive behavior
- tasks.js — tasks AJAX + UI interactions
- notes.js — notes AJAX + UI interactions
- expenses.js — expenses AJAX + charts
- documents.js — documents AJAX
- habits.js — habits AJAX + charts
- goals.js — goals AJAX
- shopping.js — shopping AJAX
- borrow.js — borrow AJAX
- passwords.js — passwords AJAX (vault-aware)
- dashboard.js — dashboard charts + calendar
- admin.js — admin panel AJAX
- reminders.js — reminders AJAX + bell icon
- chart.min.js — Chart.js library (bundled)

#### 19.7 Email System
- PHPMailer integration via Composer
- Password reset emails
- Rate limit notification emails
- Reminder notification emails
- HTML email templates
- SMTP configuration

#### 19.8 Database
- Auto-creation of tables if not exist (migration-ready)
- Foreign key constraints with CASCADE deletes
- Indexes for performance optimization
- utf8mb4 charset throughout (full Unicode support)
- InnoDB engine for all tables

#### 19.9 Activity Logging
- All CRUD operations logged (create, update, delete)
- IP address and user agent captured
- Activity log viewable in admin panel
- Recent activity feed on dashboard
- Task-specific activity logs

#### 19.10 Utility Functions
- safePrepare() — null-safe prepared statements
- checkRateLimit() — rate limiting with time windows
- logActivity() — activity logging
- sanitize() — XSS prevention
- isValidEmail() — email validation
- isStrongPassword() — password strength validation
- generateRandomString() — random token generation
- redirect() — HTTP redirect
- requireLogin() / requireGuest() — auth guards
- formatDate() — date formatting
- timeAgo() — relative time ("2 hours ago")
- validateFileUpload() — file validation (extension, MIME, size)
- saveUploadedFile() — file saving with random filename
- getFileUrl() — generate file URL
- formatBytes() — human-readable file sizes ("2.5 MB")
- tableExists() — database table existence check
- getDashboardCounts() — aggregate stats for dashboard
- getRecentActivity() — activity feed
- getUpcomingReminders() — cross-module reminders
- Chart data functions for all modules
- Pagination URL builders for all modules
- All CRUD handler functions for all modules

#### 19.11 File Upload System
- Directories for tasks, notes, documents, profile pictures
- Auto-directory creation with proper permissions
- Filename sanitization (random hex filename)
- Old file cleanup on replace
- Type-specific upload handlers
- .htaccess protection in uploads directory (prevents PHP execution)

#### 19.12 Configuration & Hosting
- Auto-detecting SITE_URL (protocol, host, path)
- PROJECT_ROOT via __DIR__ (works on any server)
- .user.ini for PHP settings (shared hosting compatible)
- .htaccess for Apache security and routing
- DEBUG mode toggle
- Error logging to file
- Environment variable support for secrets

---

## Database Tables (26 tables)

| Table | Purpose |
|-------|---------|
| users | User accounts (username, email, password_hash, role, theme) |
| settings | User preferences, 2FA config, backup codes |
| sessions | Remember-me tokens |
| password_resets | Password reset tokens |
| tasks | Task items with status, priority, due date, attachments |
| task_categories | Task categories with colors |
| task_activity_logs | Task audit trail |
| notes | Notes with rich content, categories, pinning |
| note_categories | Note categories with colors |
| note_images | Note image attachments |
| expenses | Income/expense transactions |
| expense_categories | Expense categories with colors |
| documents | Document vault entries with expiry |
| habits | Habit definitions with frequency |
| habit_logs | Daily habit completion logs |
| goals | Goal definitions with progress tracking |
| shopping | Shopping list items with prices |
| borrow_items | Borrowed/lent items and money |
| reminders | Reminder entries |
| saved_passwords | Encrypted password vault entries |
| password_categories | Password vault categories |
| feedback | User feedback with admin replies |
| activity_logs | Global activity audit trail |
| calendar_events | Calendar events |
| site_settings | Admin site configuration |
| rate_limits | Rate limiting counters |

---

## Deployment Checklist

1. Import the database SQL file into MySQL
2. Update database credentials in the configuration file
3. Set the site URL (auto-detect or manual)
4. Set DEBUG to false for production
5. Run composer install to generate vendor/ directory
6. Ensure uploads/, logs/, cache/ directories are writable (755)
7. Upload all files including vendor/ directory
