<?php
/**
 * TaskNest - Sample Data Generator
 * Run this file once: http://localhost/TaskNest/database/sample_data.php
 * Creates a demo user with sample data across all modules.
 */

require_once __DIR__ . '/../config/db.php';

// Demo credentials
$demo_username = 'demo';
$demo_email = 'demo@tasknest.com';
$demo_password = 'Demo@1234';
$demo_first_name = 'Rahul';
$demo_last_name = 'Sharma';

echo "<h2>TaskNest Sample Data Generator</h2><pre>";

// Check if demo user already exists
$stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param('ss', $demo_username, $demo_email);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();

if ($existing) {
    $user_id = $existing['id'];
    echo "Demo user already exists (ID: $user_id). Using existing user.\n\n";
} else {
    $password_hash = password_hash($demo_password, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssss', $demo_username, $demo_email, $password_hash, $demo_first_name, $demo_last_name);
    $stmt->execute();
    $user_id = $stmt->insert_id;

    // Create settings
    $stmt = $mysqli->prepare("INSERT INTO settings (user_id) VALUES (?)");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();

    echo "Demo user created!\n";
    echo "  Username: $demo_username\n";
    echo "  Email:    $demo_email\n";
    echo "  Password: $demo_password\n";
    echo "  User ID:  $user_id\n\n";
}

// ============================================================
// TASKS
// ============================================================
echo "Inserting Tasks...\n";

$categories = [
    ['Work', '#6366f1'],
    ['Personal', '#10b981'],
    ['Health', '#f59e0b'],
    ['Finance', '#ef4444'],
];

$cat_ids = [];
foreach ($categories as [$name, $color]) {
    $stmt = $mysqli->prepare("INSERT INTO task_categories (user_id, name, color) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE color = color");
    $stmt->bind_param('iss', $user_id, $name, $color);
    $stmt->execute();
    $cat_ids[] = $mysqli->insert_id ?: $mysqli->query("SELECT id FROM task_categories WHERE user_id = $user_id AND name = '$name'")->fetch_assoc()['id'];
}

$tasks = [
    ['Complete project proposal', 'Draft the Q3 project proposal for the team review.', 'Pending', 'High', 0, '2026-07-10'],
    ['Review pull requests', 'Review and merge pending PRs on GitHub.', 'In Progress', 'High', 0, '2026-07-05'],
    ['Update portfolio website', 'Add recent projects to portfolio.', 'Pending', 'Medium', 0, '2026-07-15'],
    ['Buy groceries', 'Milk, eggs, bread, rice, vegetables.', 'Completed', 'Low', 1, '2026-07-02'],
    ['Gym session - chest day', 'Bench press, incline press, cable flyes.', 'Completed', 'Medium', 1, '2026-07-03'],
    ['Schedule dentist appointment', 'Annual checkup reminder.', 'Pending', 'Low', 0, '2026-07-20'],
    ['Prepare presentation slides', 'Slides for Monday client meeting.', 'In Progress', 'High', 0, '2026-07-07'],
    ['Pay electricity bill', 'Due by 10th July.', 'Pending', 'Medium', 0, '2026-07-10'],
    ['Read 30 pages of Atomic Habits', 'Continue from Chapter 5.', 'Pending', 'Low', 0, '2026-07-05'],
    ['Fix login bug on staging', 'Users getting logged out after 5 mins.', 'In Progress', 'High', 0, '2026-07-04'],
];

foreach ($tasks as [$title, $desc, $status, $priority, $completed, $due]) {
    $completed_val = $completed ? 1 : 0;
    $completed_at = $completed ? date('Y-m-d H:i:s', strtotime('-1 day')) : null;
    $cat_id = $cat_ids[array_rand($cat_ids)];
    $stmt = $mysqli->prepare("INSERT INTO tasks (user_id, title, description, status, priority, category_id, due_date, completed, completed_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('isssssiss', $user_id, $title, $desc, $status, $priority, $cat_id, $due, $completed_val, $completed_at);
    $stmt->execute();
}
echo "  -> 10 tasks inserted\n";

// ============================================================
// NOTES
// ============================================================
echo "Inserting Notes...\n";

$note_cats = ['Ideas', 'Meeting Notes', 'Research'];
$note_cat_ids = [];
foreach ($note_cats as $name) {
    $stmt = $mysqli->prepare("INSERT INTO note_categories (user_id, name) VALUES (?, ?) ON DUPLICATE KEY UPDATE name = name");
    $stmt->bind_param('is', $user_id, $name);
    $stmt->execute();
    $note_cat_ids[] = $mysqli->insert_id ?: $mysqli->query("SELECT id FROM note_categories WHERE user_id = $user_id AND name = '$name'")->fetch_assoc()['id'];
}

$notes = [
    ['Project Architecture Ideas', 'Consider microservices for the payment module. Use Redis for session caching. Implement rate limiting on API endpoints.', 0, 0],
    ['Weekly Standup Notes - June 30', 'Team discussed Q3 milestones. Backend should focus on API optimization. Frontend needs design system updates. Next sprint starts Monday.', 1, 1],
    ['React vs Vue comparison', 'React: Larger ecosystem, more jobs, harder learning curve.\nVue: Easier to learn, great docs, smaller community.\nVerdict: Stick with React for this project.', 2, 0],
    ['Book recommendations', '- Atomic Habits by James Clear\n- Deep Work by Cal Newport\n- The Pragmatic Programmer\n- Clean Code by Robert Martin', 0, 1],
    ['API Design Notes', 'REST endpoints:\nGET /api/v1/tasks\nPOST /api/v1/tasks\nPUT /api/v1/tasks/:id\nDELETE /api/v1/tasks/:id\n\nUse JWT for auth. Rate limit: 100 req/min.', 2, 0],
    ['Grocery list backup', 'Milk, Eggs, Bread, Rice, Chicken, Tomatoes, Onions, Garlic, Ginger, Green chilies, Cooking oil, Salt, Sugar.', 0, 0],
];

foreach ($notes as [$title, $content, $cat_idx, $pinned]) {
    $cat_id = $note_cat_ids[$cat_idx];
    $stmt = $mysqli->prepare("INSERT INTO notes (user_id, title, content, category_id, is_pinned) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('issii', $user_id, $title, $content, $cat_id, $pinned);
    $stmt->execute();
}
echo "  -> 6 notes inserted\n";

// ============================================================
// EXPENSES
// ============================================================
echo "Inserting Expenses...\n";

$exp_cats = [
    ['Food & Dining', '#10b981', 'expense'],
    ['Transport', '#3b82f6', 'expense'],
    ['Shopping', '#f59e0b', 'expense'],
    ['Bills & Utilities', '#ef4444', 'expense'],
    ['Entertainment', '#8b5cf6', 'expense'],
    ['Salary', '#10b981', 'income'],
    ['Freelance', '#06b6d4', 'income'],
];

$exp_cat_ids = [];
foreach ($exp_cats as [$name, $color, $type]) {
    $stmt = $mysqli->prepare("INSERT INTO expense_categories (user_id, name, color, type) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE color = color");
    $stmt->bind_param('isss', $user_id, $name, $color, $type);
    $stmt->execute();
    $exp_cat_ids[] = $mysqli->insert_id ?: $mysqli->query("SELECT id FROM expense_categories WHERE user_id = $user_id AND name = '$name' AND type = '$type'")->fetch_assoc()['id'];
}

$expenses = [
    ['Monthly Salary', 75000, 'income', 5, '2026-07-01', 'July salary credited'],
    ['Freelance Project - Website', 15000, 'income', 6, '2026-07-03', 'Client payment for landing page'],
    ['Groceries - BigBasket', 2350, 'expense', 0, '2026-07-02', 'Weekly grocery order'],
    ['Uber to office', 280, 'expense', 1, '2026-07-03', 'Monday commute'],
    ['Electricity Bill', 1850, 'expense', 3, '2026-07-01', 'June electricity bill'],
    ['Netflix subscription', 649, 'expense', 4, '2026-07-01', 'Monthly subscription'],
    ['New running shoes', 3500, 'expense', 2, '2026-06-28', 'Nike Pegasus 40'],
    ['Dinner at restaurant', 1200, 'expense', 0, '2026-06-30', 'Birthday dinner with friends'],
    ['Auto ride to market', 150, 'expense', 1, '2026-07-02', 'Quick errand'],
    ['Book - Atomic Habits', 450, 'expense', 2, '2026-06-25', 'Ordered from Amazon'],
    ['Phone recharge - Jio', 599, 'expense', 3, '2026-06-28', 'Annual plan'],
    ['Movie tickets - IMAX', 800, 'expense', 4, '2026-06-29', 'Watched Oppenheimer'],
];

foreach ($expenses as [$title, $amount, $type, $cat_idx, $date, $notes]) {
    $cat_id = $exp_cat_ids[$cat_idx];
    $stmt = $mysqli->prepare("INSERT INTO expenses (user_id, title, amount, type, category_id, transaction_date, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('isdssss', $user_id, $title, $amount, $type, $cat_id, $date, $notes);
    $stmt->execute();
}
echo "  -> 12 expenses inserted\n";

// ============================================================
// HABITS
// ============================================================
echo "Inserting Habits...\n";

$habits = [
    ['Morning Workout', 'Exercise for at least 30 minutes', 'daily', 1, '#10b981'],
    ['Read 20 pages', 'Read non-fiction books', 'daily', 1, '#6366f1'],
    ['Drink 3L water', 'Stay hydrated throughout the day', 'daily', 1, '#3b82f6'],
    ['Meditate', '10 minutes mindfulness meditation', 'daily', 1, '#8b5cf6'],
    ['Journal', 'Write 3 things I am grateful for', 'daily', 1, '#f59e0b'],
    ['Learn coding', 'Practice DSA or new framework', 'daily', 1, '#ef4444'],
];

$habit_ids = [];
foreach ($habits as [$name, $desc, $freq, $target, $color]) {
    $stmt = $mysqli->prepare("INSERT INTO habits (user_id, name, description, frequency, target_count, color) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('isssis', $user_id, $name, $desc, $freq, $target, $color);
    $stmt->execute();
    $habit_ids[] = $mysqli->insert_id;
}

// Add habit logs for last 7 days
foreach ($habit_ids as $hid) {
    for ($i = 1; $i <= 6; $i++) {
        $day = date('Y-m-d', strtotime("-$i days"));
        // Random: 70% chance logged
        if (rand(1, 100) <= 70) {
            $stmt = $mysqli->prepare("INSERT IGNORE INTO habit_logs (user_id, habit_id, log_date, count_value) VALUES (?, ?, ?, 1)");
            $stmt->bind_param('iis', $user_id, $hid, $day);
            $stmt->execute();
        }
    }
}
echo "  -> 6 habits with logs inserted\n";

// ============================================================
// GOALS
// ============================================================
echo "Inserting Goals...\n";

$goals = [
    ['Read 24 books in 2026', 'Read at least 2 books every month this year.', 'Personal Development', 24, 9, 'books', '2026-01-01', '2026-12-31', 'active'],
    ['Save 2 Lakhs', 'Build emergency fund by year end.', 'Finance', 200000, 85000, 'INR', '2026-01-01', '2026-12-31', 'active'],
    ['Run a Half Marathon', 'Complete a 21K run.', 'Fitness', 21, 14, 'km', '2026-03-01', '2026-11-30', 'active'],
    ['Learn React Native', 'Build a complete mobile app.', 'Career', 100, 45, '%', '2026-04-01', '2026-09-30', 'active'],
    ['Loose 5 kg', 'Reach target weight of 75 kg.', 'Health', 5, 2, 'kg', '2026-01-15', '2026-06-30', 'completed'],
];

foreach ($goals as [$title, $desc, $cat, $target, $current, $unit, $start, $due, $status]) {
    $completed_date = $status === 'completed' ? date('Y-m-d', strtotime('-5 days')) : null;
    $stmt = $mysqli->prepare("INSERT INTO goals (user_id, title, description, category, target_value, current_value, unit, start_date, due_date, completed_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('isssddsssss', $user_id, $title, $desc, $cat, $target, $current, $unit, $start, $due, $completed_date, $status);
    $stmt->execute();
}
echo "  -> 5 goals inserted\n";

// ============================================================
// SHOPPING
// ============================================================
echo "Inserting Shopping...\n";

$shopping = [
    ['Wireless Mouse', 1, 1200, 0, 'Electronics', 'Logitech M350', 0],
    ['Protein Powder', 1, 2500, 0, 'Health', 'Optimum Nutrition 2lb', 0],
    ['Running Socks', 3, 300, 0, 'Clothing', 'Anti-blister sports socks', 1],
    ['Desk Lamp', 1, 800, 0, 'Home Office', 'LED adjustable desk lamp', 0],
    ['Phone Case', 1, 500, 450, 'Electronics', 'Spigen Tough Armor for Pixel 8', 1],
    ['Notebook', 5, 150, 0, 'Stationery', 'Classmate ruled A5', 1],
    ['USB-C Hub', 1, 1800, 0, 'Electronics', '7-in-1 hub with HDMI', 0],
];

foreach ($shopping as [$name, $qty, $est, $act, $cat, $notes, $completed]) {
    $stmt = $mysqli->prepare("INSERT INTO shopping (user_id, name, quantity, estimated_price, actual_price, category, notes, is_completed) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('isiddssi', $user_id, $name, $qty, $est, $act, $cat, $notes, $completed);
    $stmt->execute();
}
echo "  -> 7 shopping items inserted\n";

// ============================================================
// BORROW ITEMS
// ============================================================
echo "Inserting Borrow items...\n";

$borrow = [
    ['Money to Amit', 'Lent for dinner last week', 'lent', 'money', 500, 'Amit Patel', '9876543210', '2026-06-28', '2026-07-05', 'pending'],
    ['Book from Priya', 'Atomic Habits copy', 'borrowed', 'item', 0, 'Priya Singh', '9876543211', '2026-06-20', '2026-07-10', 'pending'],
    ['Money from Rahul B', 'Borrowed for movie tickets', 'borrowed', 'money', 800, 'Rahul Bose', '9876543212', '2026-06-25', '2026-07-01', 'returned'],
    ['Laptop charger to Suresh', ' spare MacBook charger', 'lent', 'item', 0, 'Suresh Kumar', '9876543213', '2026-07-01', '2026-07-07', 'pending'],
    ['Money to Vikram', 'Lunch money', 'lent', 'money', 300, 'Vikram Joshi', '9876543214', '2026-06-30', '2026-07-15', 'pending'],
];

foreach ($borrow as [$title, $desc, $type, $item_type, $amount, $person, $contact, $borrow_date, $return_date, $status]) {
    $actual_return = $status === 'returned' ? $return_date : null;
    $stmt = $mysqli->prepare("INSERT INTO borrow_items (user_id, title, description, type, item_type, amount, person_name, person_contact, borrow_date, return_date, actual_return_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('issssdssssss', $user_id, $title, $desc, $type, $item_type, $amount, $person, $contact, $borrow_date, $return_date, $actual_return, $status);
    $stmt->execute();
}
echo "  -> 5 borrow items inserted\n";

// ============================================================
// CALENDAR EVENTS
// ============================================================
echo "Inserting Calendar Events...\n";

$events = [
    ['Team Standup', date('Y-m-d'), 'Weekly team sync meeting'],
    ['Client Presentation', date('Y-m-d', strtotime('+4 days')), 'Q3 project review presentation'],
    ['Doctor Appointment', date('Y-m-d', strtotime('+7 days')), 'Annual health checkup'],
    ['Birthday Party - Rohan', date('Y-m-d', strtotime('+10 days')), 'Rohans birthday celebration at 7 PM'],
    ['Project Deadline', date('Y-m-d', strtotime('+14 days')), 'Submit final deliverables for Q3 project'],
];

foreach ($events as [$title, $date, $desc]) {
    $stmt = $mysqli->prepare("INSERT INTO calendar_events (user_id, title, event_date, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isss', $user_id, $title, $date, $desc);
    $stmt->execute();
}
echo "  -> 5 calendar events inserted\n";

// ============================================================
// ACTIVITY LOGS
// ============================================================
echo "Inserting Activity Logs...\n";

$activities = [
    ['user_login', 'user', 'User logged in'],
    ['task_created', 'task', 'Created: Complete project proposal'],
    ['note_created', 'note', 'Created: Project Architecture Ideas'],
    ['expense_added', 'expense', 'Added expense: Monthly Salary'],
    ['habit_logged', 'habit', 'Logged: Morning Workout'],
    ['shopping_added', 'shopping', 'Added: Wireless Mouse'],
];

foreach ($activities as [$action, $entity, $desc]) {
    $stmt = $mysqli->prepare("INSERT INTO activity_logs (user_id, action, entity_type, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isss', $user_id, $action, $entity, $desc);
    $stmt->execute();
}
echo "  -> 6 activity logs inserted\n";

echo "\n========================================\n";
echo "DONE! All sample data inserted successfully.\n";
echo "========================================\n\n";
echo "Login credentials:\n";
echo "  Username: $demo_username\n";
echo "  Password: $demo_password\n";
echo "  URL:      http://localhost/TaskNest/login.php\n";
echo "\nYou can now delete this file if you want.\n";
echo "</pre>";
?>
