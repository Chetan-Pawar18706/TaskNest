# TaskNest System Architecture & Flow Diagrams

## 🏗️ COMPLETE SYSTEM ARCHITECTURE

```
┌─────────────────────────────────────────────────────────────────┐
│                    USER BROWSER / CLIENT                        │
│  (Chrome, Firefox, Safari, Edge - Desktop, Tablet, Mobile)     │
└────────────────────────┬────────────────────────────────────────┘
                         │
                    HTTP/HTTPS
                         │
        ┌────────────────┼────────────────┐
        │                │                │
    CSS Files         JavaScript      Images
   (variables.css)   (5 modules)    (avatars)
   (reset.css)       (main.js)
   (styles.css)      (theme.js)
   (responsive.css)  (sidebar.js)
                     (toast.js)
                     (modal.js)
        │                │                │
        └────────────────┼────────────────┘
                         │
        ┌────────────────▼────────────────┐
        │   WEB SERVER (Apache/Nginx)     │
        │   PHP 8.0+ Environment          │
        └────────────────┬────────────────┘
                         │
        ┌────────────────▼────────────────┐
        │  PHP APPLICATION LAYER          │
        │  (37 PHP Files + 6 Includes)    │
        │                                  │
        │  Pages:                          │
        │  ├─ index.php (Landing)         │
        │  ├─ login.php (Auth)            │
        │  ├─ register.php (Auth)         │
        │  ├─ forgot-password.php (Auth)  │
        │  ├─ reset-password.php (Auth)   │
        │  ├─ logout.php (Auth)           │
        │  ├─ dashboard.php (Protected)   │
        │  ├─ profile.php (Protected)     │
        │  ├─ settings.php (Protected)    │
        │  └─ 8 Phase-2 Placeholders      │
        │                                  │
        │  Core Includes:                  │
        │  ├─ config/db.php               │
        │  ├─ includes/auth.php (Class)   │
        │  ├─ includes/functions.php (50+)│
        │  ├─ includes/header.php         │
        │  ├─ includes/footer.php         │
        │  ├─ includes/navbar.php         │
        │  └─ includes/sidebar.php        │
        └────────────────┬────────────────┘
                         │
        ┌────────────────▼────────────────┐
        │     MySQL DATABASE              │
        │     (6 Normalized Tables)       │
        │                                  │
        │  ├─ users (Accounts)            │
        │  ├─ password_resets (Recovery)  │
        │  ├─ sessions (Session Mgmt)     │
        │  ├─ settings (Preferences)      │
        │  └─ activity_logs (Audit Trail) │
        │                                  │
        └────────────────────────────────┘
                         │
        ┌────────────────▼────────────────┐
        │  FILE STORAGE                   │
        │                                  │
        │  ├─ logs/ (Error logs)          │
        │  └─ uploads/avatars/ (Images)   │
        └────────────────────────────────┘
```

---

## 🔄 USER AUTHENTICATION FLOW

```
┌──────────────┐
│  User Visits │
│  index.php   │
└──────┬───────┘
       │
       ▼
┌─────────────────────┐
│ Check $_SESSION     │
│ Is user logged in?  │
└──────┬────────┬─────┘
       │        │
    YES│        │NO
       │        │
       │        ▼
       │    ┌──────────────────┐
       │    │ Show Auth Pages  │
       │    │ - login.php      │
       │    │ - register.php   │
       │    │ - forgot-pwd.php │
       │    └────────┬─────────┘
       │             │
       │             ▼
       │    ┌──────────────────────┐
       │    │ User Enters Creds    │
       │    │ POST /login.php      │
       │    └──────┬───────────────┘
       │           │
       │           ▼
       │    ┌──────────────────────┐
       │    │ Auth::login()        │
       │    │ - Validate CSRF      │
       │    │ - Verify password    │
       │    │ - Regenerate session │
       │    │ - Log activity       │
       │    │ - Return user object │
       │    └──────┬───────────────┘
       │           │
       │           ▼
       │    ┌──────────────────────┐
       │    │ Redirect to:         │
       │    │ dashboard.php        │
       │    └──────────────────────┘
       │
       ▼
┌────────────────────────┐
│ requireLogin Check     │
│ $_SESSION['user_id']   │
│ exists?                │
└──────┬────────┬────────┘
       │        │
    YES│        │NO
       │        │
       │        ▼
       │    ┌──────────────────┐
       │    │ Redirect to      │
       │    │ login.php        │
       │    │ + error message  │
       │    └──────────────────┘
       │
       ▼
┌────────────────────────┐
│ Load Dashboard Page    │
│ show protected content │
└────────────────────────┘
```

---

## 🔐 SECURITY LAYER ARCHITECTURE

```
                    REQUEST ENTERS APPLICATION
                            │
                ┌───────────▼───────────┐
                │   HTTP Request        │
                │   $_GET, $_POST, etc  │
                └───────────┬───────────┘
                            │
        ┌───────────────────┼───────────────────┐
        │                   │                   │
        ▼                   ▼                   ▼
    ┌─────────┐         ┌──────────┐       ┌────────┐
    │ CSRF    │         │ Session  │       │ Cookie │
    │ Token   │         │ Check    │       │ Secure │
    │ Verify  │         │ Validate │       │ HttpOnly│
    └────┬────┘         └────┬─────┘       └───┬────┘
         │                   │                  │
         └───────────────────┼──────────────────┘
                             │
                    ┌────────▼────────┐
                    │ All 3 Passed?   │
                    └────────┬────────┘
                             │
                        ┌────┴────┐
                    NO  │        YES
                        │         │
                        ▼         ▼
                    ┌────────┐  ┌──────────────┐
                    │ 403    │  │ Continue to  │
                    │Forbidden│ │Input Process │
                    └────────┘  └────────┬─────┘
                                         │
                    ┌────────────────────┼────────────────────┐
                    │                    │                    │
                    ▼                    ▼                    ▼
              ┌──────────┐         ┌──────────┐         ┌──────────┐
              │ Input    │         │ Input    │         │ File     │
              │ Type     │         │ Length   │         │ Upload   │
              │ Check    │         │ Validate │         │ Validate │
              └────┬─────┘         └────┬─────┘         └────┬─────┘
                   │                    │                    │
                   └────────────────────┼────────────────────┘
                                        │
                           ┌────────────▼────────────┐
                           │ sanitize()             │
                           │ trim/strip_tags()      │
                           │ htmlspecialchars()     │
                           └────────────┬───────────┘
                                        │
                        ┌───────────────┼───────────────┐
                        │               │               │
                        ▼               ▼               ▼
                  ┌──────────┐    ┌──────────┐    ┌──────────┐
                  │ Database │    │ Email    │    │ Output   │
                  │ Query    │    │ Send     │    │ Render   │
                  │ Prepared │    │ PHPMailer│    │ Escape   │
                  │ Stmt     │    │          │    │ htmlsc() │
                  └────┬─────┘    └──────────┘    └────┬─────┘
                       │                              │
                       └──────────────┬───────────────┘
                                      │
                           ┌──────────▼──────────┐
                           │ Activity Log        │
                           │ Record Action       │
                           │ User, IP, Time      │
                           └─────────────────────┘
```

---

## 📊 DATABASE RELATIONSHIP DIAGRAM

```
┌─────────────────────────────────────┐
│          USERS TABLE               │
├─────────────────────────────────────┤
│ PK: id                             │
│ username (UNIQUE)                  │
│ email (UNIQUE)                     │
│ password_hash (Argon2id)           │
│ first_name, last_name              │
│ avatar_url, phone, bio             │
│ timezone, theme, is_active         │
│ created_at, updated_at             │
└────────────┬────────────────────────┘
             │ 1:N
      ┌──────┼──────┬──────┬──────┐
      │      │      │      │      │
      ▼      ▼      ▼      ▼      ▼
  ┌──┐  ┌──────┐ ┌──────┐ ┌────┐ ┌──────┐
  │ │  │PASS  │ │SESH  │ │SETT│ │ACT   │
  │ │  │RESET │ │      │ │    │ │LOG   │
  │ │  └──────┘ └──────┘ └────┘ └──────┘
  └──┘

┌──────────────────────────────────────┐
│    PASSWORD_RESETS TABLE            │
├──────────────────────────────────────┤
│ PK: id                              │
│ FK: user_id → users.id              │
│ token (UNIQUE, random 64 chars)     │
│ expires_at (1 hour from request)    │
│ is_used (0 or 1)                    │
│ created_at                          │
└──────────────────────────────────────┘

┌──────────────────────────────────────┐
│       SESSIONS TABLE                │
├──────────────────────────────────────┤
│ PK: id                              │
│ FK: user_id → users.id              │
│ session_token (unique, 64 chars)    │
│ remember_token (for "Remember Me")  │
│ ip_address (IPv4 or IPv6)           │
│ user_agent (browser info)           │
│ last_activity (TIMESTAMP)           │
│ expires_at (30 mins or 7 days)      │
│ created_at                          │
└──────────────────────────────────────┘

┌──────────────────────────────────────┐
│      SETTINGS TABLE                 │
├──────────────────────────────────────┤
│ PK: id                              │
│ FK: user_id → users.id (UNIQUE)     │
│ notifications_enabled (1/0)         │
│ email_on_reminder (1/0)             │
│ email_on_collaboration (1/0)        │
│ email_on_digest (1/0)               │
│ two_factor_enabled (1/0)            │
│ two_factor_secret (base32)          │
│ language (en, es, fr, etc)          │
│ date_format (Y-m-d, m/d/Y, etc)     │
│ time_format (24h, 12h)              │
│ items_per_page (10-100)             │
│ created_at, updated_at              │
└──────────────────────────────────────┘

┌──────────────────────────────────────┐
│     ACTIVITY_LOGS TABLE             │
├──────────────────────────────────────┤
│ PK: id                              │
│ FK: user_id → users.id              │
│ action (login, logout, update, etc) │
│ entity_type (user, task, note, etc) │
│ entity_id (ID of affected entity)   │
│ description (human-readable text)   │
│ ip_address (log IP for security)    │
│ user_agent (browser info)           │
│ created_at (when action occurred)   │
└──────────────────────────────────────┘
```

---

## 🌳 APPLICATION FLOW HIERARCHY

```
APPLICATION START
      │
      ▼
┌─────────────────────────────────────────┐
│ Load config/db.php                      │
│ ├─ DB constants                         │
│ ├─ Error handlers                       │
│ ├─ MySQLi connection                    │
│ └─ Initialize globals                   │
└─────────┬───────────────────────────────┘
          │
          ▼
┌─────────────────────────────────────────┐
│ Include includes/auth.php               │
│ ├─ Create $auth instance                │
│ ├─ Check $_SESSION['user_id']           │
│ └─ Load current user data               │
└─────────┬───────────────────────────────┘
          │
          ▼
┌─────────────────────────────────────────┐
│ Include includes/functions.php          │
│ ├─ 50+ utility functions available      │
│ └─ Ready for page logic                 │
└─────────┬───────────────────────────────┘
          │
          ▼
┌─────────────────────────────────────────┐
│ Page-Specific Logic                     │
│ ├─ requireLogin() or requireGuest()     │
│ ├─ Handle $_POST (forms)                │
│ ├─ Query database                       │
│ └─ Process business logic               │
└─────────┬───────────────────────────────┘
          │
          ▼
┌─────────────────────────────────────────┐
│ Include includes/header.php             │
│ ├─ HTML doctype & meta tags             │
│ ├─ Load 4 CSS files                     │
│ ├─ Include navbar.php                   │
│ └─ Include sidebar.php (if logged in)   │
└─────────┬───────────────────────────────┘
          │
          ▼
┌─────────────────────────────────────────┐
│ Page HTML Content                       │
│ ├─ Main content area                    │
│ ├─ Dynamic data from PHP                │
│ └─ Forms with CSRF tokens               │
└─────────┬───────────────────────────────┘
          │
          ▼
┌─────────────────────────────────────────┐
│ Include includes/footer.php             │
│ ├─ Close HTML tags                      │
│ ├─ Load 5 JavaScript files              │
│ ├─ Inject CSRF token                    │
│ └─ Initialize JavaScript                │
└─────────┬───────────────────────────────┘
          │
          ▼
HTML DOCUMENT COMPLETE & SENT TO BROWSER
          │
          ▼
┌─────────────────────────────────────────┐
│ Browser Renders Page                    │
│ ├─ Load CSS (variables, reset, styles)  │
│ ├─ Apply responsive CSS                 │
│ ├─ Load responsive CSS (768px breaks)   │
│ ├─ Execute JavaScript modules           │
│ └─ Initialize TaskNest.* objects        │
└─────────┬───────────────────────────────┘
          │
          ▼
┌─────────────────────────────────────────┐
│ JavaScript Initialization               │
│ ├─ main.js: Setup event listeners       │
│ ├─ theme.js: Detect & apply theme      │
│ ├─ sidebar.js: Setup sidebar toggle     │
│ ├─ toast.js: Initialize notifications  │
│ └─ modal.js: Initialize modals          │
└─────────┬───────────────────────────────┘
          │
          ▼
┌─────────────────────────────────────────┐
│ User Interaction Ready                  │
│ ├─ Click buttons, submit forms          │
│ ├─ Toggle sidebar/theme                 │
│ ├─ Navigate pages                       │
│ └─ Receive notifications                │
└─────────────────────────────────────────┘
```

---

## 🔄 FORM SUBMISSION FLOW

```
USER INTERACTION: Submit Form
            │
            ▼
    ┌──────────────────┐
    │ Form POST Fires  │
    │ /path/page.php   │
    └────────┬─────────┘
             │
             ▼
    ┌──────────────────────────┐
    │ Server Receives POST     │
    │ $_POST array populated   │
    └────────┬─────────────────┘
             │
      ┌──────┴──────┐
      │             │
      ▼             ▼
┌──────────────┐ ┌──────────────┐
│ CSRF Token   │ │ Data Input   │
│ Validate     │ │ Validate     │
│ $_POST       │ │ Type & Len   │
│['csrf_token']│ │              │
└──────┬───────┘ └──────┬───────┘
       │                │
       └────────┬───────┘
                ▼
         ┌──────────────────┐
         │ Both Valid?      │
         └────────┬─────────┘
                  │
              ┌───┴───┐
          YES │       │ NO
              │       │
              ▼       ▼
         ┌────────┐ ┌─────────────┐
         │Process │ │Add Error    │
         │Data    │ │Set $errors  │
         └────┬───┘ │Show Form    │
              │     │Again        │
              │     └─────────────┘
              │
              ▼
         ┌──────────────────┐
         │ sanitize()       │
         │ Trim, strip tags │
         │ htmlspecialchars │
         └────────┬─────────┘
                  │
                  ▼
         ┌──────────────────┐
         │ Prepared Query   │
         │ $mysqli->prepare │
         │ $stmt->bind_param│
         │ $stmt->execute   │
         └────────┬─────────┘
                  │
              ┌───┴────┐
            OK │        │ ERROR
              │        │
              ▼        ▼
         ┌────────┐ ┌─────────────┐
         │Success │ │Database     │
         │Message │ │Error        │
         │Log Acti│ │Log Activity │
         │Redirect│ │Show Error   │
         └────────┘ └─────────────┘
```

---

## 📱 RESPONSIVE BREAKPOINT FLOW

```
User Opens Application
            │
            ▼
    Check Window Width
    (JavaScript or CSS)
            │
    ┌───────┼────────┬──────────┬────────┐
    │       │        │          │        │
    ▼       ▼        ▼          ▼        ▼
  1920+    1024     768        480      320
  Desktop  Laptop   Tablet     Mobile   Phone
    │       │        │          │        │
    ├───────┤        │          │        │
    │    Sidebar:    │          │        │
    │    280px       │          │        │
    │    Visible     │          │        │
    │               │          │        │
    │    ┌──────────┤          │        │
    │    │ Sidebar: │          │        │
    │    │ 240px    │          │        │
    │    │ Visible  │          │        │
    │    │          │    ┌─────┤        │
    │    │          │    │ Sidebar     │
    │    │          │    │ 280px       │
    │    │          │    │ Hidden      │
    │    │          │    │ Toggle Only │
    │    │          │    │             │
    │    │          │    │    ┌────────┤
    │    │          │    │    │Sidebar │
    │    │          │    │    │Hidden  │
    │    │          │    │    │Full    │
    │    │          │    │    │Width   │
    │    │          │    │    │Content │
    └────┴──────────┴────┴────┴────────┘
         │          │        │        │
         ▼          ▼        ▼        ▼
     Layout1    Layout2    Layout3  Layout4
     Stats 4    Stats 2    Stats 1  Stats 1
     Columns    Columns    Column   Column
     
     (Plus responsive CSS applied at each level)
```

---

## 🎯 SECURITY TOKENS LIFECYCLE

```
┌─────────────────────────────────────┐
│ SESSION START / USER LOGIN          │
└────────────────┬────────────────────┘
                 │
    ┌────────────┼────────────┐
    │            │            │
    ▼            ▼            ▼
┌─────────┐ ┌──────────┐ ┌──────────┐
│ CSRF    │ │ Session  │ │ Remember │
│ Token   │ │ Cookie   │ │ Token    │
│ Gen     │ │ Create   │ │ (7 days) │
│ 32 bytes│ │ 30 min   │ │ 20 bytes │
└────┬────┘ └────┬─────┘ └────┬─────┘
     │           │            │
     │           ▼            │
     │  ┌──────────────────┐  │
     │  │ Store in DB      │  │
     │  │ sessions table   │  │
     │  └──────────────────┘  │
     │                        │
     ├────────────┬───────────┘
     │            │
     ▼            ▼
┌──────────────────────────────┐
│ Store in $_SESSION Array     │
│ - csrf_token                 │
│ - user_id                    │
│ - session_id                 │
└──────────────────────────────┘
     │
     ▼
┌──────────────────────────────┐
│ EVERY PAGE LOAD / FORM       │
└──────────────────┬───────────┘
                   │
         ┌─────────┼─────────┐
         │         │         │
         ▼         ▼         ▼
    ┌────────┐ ┌───────┐ ┌─────────┐
    │Inject  │ │Cookie │ │Remember │
    │CSRF    │ │Exists │ │Token    │
    │Token   │ │Valid? │ │Valid?   │
    │in Form │ │Check  │ │Refresh? │
    └────┬───┘ └───┬───┘ └────┬────┘
         │         │          │
         └─────────┼──────────┘
                   ▼
         ┌──────────────────┐
         │ All Valid?       │
         └────┬──────┬──────┘
              │      │
           YES│      │NO
              │      │
              ▼      ▼
         ┌────────┐ ┌────────────┐
         │Continue│ │Logout User │
         │Request │ │Clear Tokens│
         └────────┘ │Redirect    │
                    │Login       │
                    └────────────┘

┌──────────────────────────────┐
│ TOKEN EXPIRATION FLOW        │
└────────────┬─────────────────┘
             │
    ┌────────┴────────┐
    │                 │
    ▼                 ▼
┌──────────────┐ ┌──────────────┐
│Session Token │ │Remember Token│
│30 min        │ │7 days        │
│Expires at?   │ │Expires at?   │
└──────┬───────┘ └──────┬───────┘
       │                │
       │ Expiration     │ Expiration
       │ time reached   │ time reached
       │                │
       ▼                ▼
    ┌──────────────────────────┐
    │ Remove from DB           │
    │ Clear $_SESSION          │
    │ Redirect to Login        │
    │ Show Session Expired Msg │
    └──────────────────────────┘
```

---

## ✨ USER EXPERIENCE FLOW

```
FIRST-TIME VISITOR
        │
        ├─ index.php (landing page)
        │  ├─ View features
        │  ├─ Click "Register"
        │  └─ → register.php
        │
        ▼
    register.php
    ├─ Fill form
    │ ├─ Username validation
    │ ├─ Email validation
    │ └─ Password strength check
    ├─ Click register
    └─ → Success message + Link to login
        │
        ▼
    login.php
    ├─ Fill email & password
    ├─ Optional: Check "Remember Me"
    ├─ Click login
    └─ → dashboard.php
        │
        ▼
    dashboard.php
    ├─ View stats & activity
    ├─ Access sidebar menu
    ├─ Click "Settings"
    └─ → settings.php
        │
        ▼
    settings.php
    ├─ Edit profile info
    ├─ Set timezone & theme
    ├─ Configure notifications
    ├─ Click "Save"
    └─ → Dashboard
        │
        ▼
    User can now:
    ├─ Toggle theme (light/dark)
    ├─ View profile (profile.php)
    ├─ Access placeholders (tasks, notes, etc)
    ├─ Use notifications (toast system)
    ├─ Use modal dialogs
    └─ Logout when done

RETURNING VISITOR (with Remember Me)
        │
        ├─ Cookies still valid?
        │  └─ YES: Auto-login
        │  └─ NO: Requires re-login
        │
        └─ → dashboard.php directly
```

---

## 🔗 PAGE INTERCONNECTION MAP

```
index.php (Landing)
├─ Register → register.php
├─ Login → login.php
└─ Logo → index.php

login.php
├─ Forgot Password → forgot-password.php
├─ Register → register.php
└─ Submit → dashboard.php

register.php
├─ Login → login.php
└─ Submit → login.php

forgot-password.php
├─ Back to Login → login.php
└─ Submit → login.php (with success message)

reset-password.php
├─ From email link
└─ Submit → login.php (with success message)

logout.php
└─ → login.php

dashboard.php (Protected)
├─ Logo → dashboard.php
├─ Profile → profile.php
├─ Settings → settings.php
├─ Tasks → tasks.php
├─ Notes → notes.php
├─ Expenses → expenses.php
├─ Documents → documents.php
├─ Habits → habits.php
├─ Goals → goals.php
├─ Shopping → shopping.php
├─ Borrow → borrow.php
└─ Logout → logout.php

profile.php (Protected)
├─ Edit Profile → settings.php
├─ Dashboard → dashboard.php
└─ Logo → dashboard.php

settings.php (Protected)
├─ Dashboard → dashboard.php
├─ Logo → dashboard.php
└─ Save → settings.php (reload)

Phase 2 Pages (tasks, notes, etc)
├─ Dashboard → dashboard.php
├─ Logo → dashboard.php
└─ Other modules → respective pages
```

---

## 🛡️ COMPLETE SECURITY IMPLEMENTATION MAP

```
REQUEST LIFECYCLE SECURITY
        ↓
    ┌─────────────────────────────────┐
    │ 1. SSL/HTTPS (Production)       │
    │    └─ Encrypt in transit        │
    └─────────────────────────────────┘
        ↓
    ┌─────────────────────────────────┐
    │ 2. Session Security             │
    │    ├─ HttpOnly flag             │
    │    ├─ Secure flag (HTTPS)       │
    │    ├─ SameSite=Lax              │
    │    └─ 30-minute timeout         │
    └─────────────────────────────────┘
        ↓
    ┌─────────────────────────────────┐
    │ 3. CSRF Protection              │
    │    ├─ Generate token on page    │
    │    ├─ Verify token on POST      │
    │    ├─ 32-byte random token      │
    │    └─ Regenerate on login       │
    └─────────────────────────────────┘
        ↓
    ┌─────────────────────────────────┐
    │ 4. Input Validation             │
    │    ├─ Type checking             │
    │    ├─ Length limits             │
    │    ├─ Format validation         │
    │    └─ File type verification    │
    └─────────────────────────────────┘
        ↓
    ┌─────────────────────────────────┐
    │ 5. Input Sanitization           │
    │    ├─ trim() & strip_tags()     │
    │    ├─ htmlspecialchars()        │
    │    └─ escapeshellarg() for CLI  │
    └─────────────────────────────────┘
        ↓
    ┌─────────────────────────────────┐
    │ 6. SQL Injection Prevention      │
    │    ├─ Prepared statements       │
    │    ├─ bind_param() for data     │
    │    ├─ Parameterized queries     │
    │    └─ Never concat user input   │
    └─────────────────────────────────┘
        ↓
    ┌─────────────────────────────────┐
    │ 7. XSS Prevention               │
    │    ├─ Output escaping           │
    │    ├─ htmlspecialchars() in PHP │
    │    ├─ HTML encoding in JS       │
    │    └─ No innerHTML assignments  │
    └─────────────────────────────────┘
        ↓
    ┌─────────────────────────────────┐
    │ 8. Password Security            │
    │    ├─ Argon2id hashing          │
    │    ├─ 8+ chars required         │
    │    ├─ Uppercase + number        │
    │    └─ Unique per user           │
    └─────────────────────────────────┘
        ↓
    ┌─────────────────────────────────┐
    │ 9. Authentication               │
    │    ├─ Email/username check      │
    │    ├─ Password hash verify      │
    │    ├─ Rate limiting ready       │
    │    └─ Login logging             │
    └─────────────────────────────────┘
        ↓
    ┌─────────────────────────────────┐
    │ 10. Authorization               │
    │    ├─ requireLogin() check      │
    │    ├─ requireGuest() check      │
    │    ├─ Session validation        │
    │    └─ User ID verification      │
    └─────────────────────────────────┘
        ↓
    ┌─────────────────────────────────┐
    │ 11. Activity Logging            │
    │    ├─ Log all auth events       │
    │    ├─ Record IP address         │
    │    ├─ Store user agent          │
    │    └─ Timestamp all actions     │
    └─────────────────────────────────┘
        ↓
    ┌─────────────────────────────────┐
    │ 12. Error Handling              │
    │    ├─ No sensitive info in msgs │
    │    ├─ Log to file not display   │
    │    ├─ 404 for fake URLs         │
    │    └─ 403 for unauthorized      │
    └─────────────────────────────────┘
        ↓
    RESPONSE SENT SECURELY
```

---

**Architecture Complete**  
**All Systems Documented**  
**Ready for Implementation Review**

