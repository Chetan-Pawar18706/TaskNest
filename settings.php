<?php
/**
 * TaskNest - Account Settings
 */

require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin($auth);

$user = $auth->getUser();
$user_id = $auth->getUserId();
$errors = [];
$success = '';

// Get user settings
$stmt = safePrepare($mysqli, "SELECT * FROM settings WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$settings = $stmt->get_result()->fetch_assoc();

// Get user profile
$stmt = safePrepare($mysqli, "SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !$auth->verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Security token invalid';
    }
    
    if (empty($errors)) {
        $first_name = sanitize($_POST['first_name'] ?? '');
        $last_name = sanitize($_POST['last_name'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $bio = sanitize($_POST['bio'] ?? '');
        $timezone = sanitize($_POST['timezone'] ?? 'UTC');
        $theme = $_POST['theme'] ?? 'light';
        $notifications_enabled = isset($_POST['notifications_enabled']) ? 1 : 0;
        
        // Update user profile
        $stmt = safePrepare($mysqli, "
            UPDATE users 
            SET first_name = ?, last_name = ?, phone = ?, bio = ?, timezone = ?, theme = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssssssi", $first_name, $last_name, $phone, $bio, $timezone, $theme, $user_id);
        
        if ($stmt->execute()) {
            // Update settings
            $stmt_settings = safePrepare($mysqli, "
                UPDATE settings 
                SET notifications_enabled = ?
                WHERE user_id = ?
            ");
            $stmt_settings->bind_param("ii", $notifications_enabled, $user_id);
            $stmt_settings->execute();
            
            $success = 'Settings saved successfully!';
            
            // Reload auth to get updated data
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } else {
            $errors[] = 'Failed to save settings';
        }
    }
}

$page_title = 'Settings';
include 'includes/header.php';
?>

<div class="settings-container">
    <div class="card">
        <div class="card-header">
            <h2><?php echo $page_title; ?></h2>
        </div>
        
        <?php if ($success): ?>
        <div class="alert alert-success">
            <p><?php echo htmlspecialchars($success); ?></p>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" class="settings-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($auth->generateCsrfToken()); ?>">
            
            <div class="form-section">
                <h3>Personal Information</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input 
                            type="text" 
                            id="first_name" 
                            name="first_name"
                            value="<?php echo htmlspecialchars($profile['first_name'] ?? ''); ?>"
                            class="form-input"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input 
                            type="text" 
                            id="last_name" 
                            name="last_name"
                            value="<?php echo htmlspecialchars($profile['last_name'] ?? ''); ?>"
                            class="form-input"
                        >
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone"
                        value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>"
                        class="form-input"
                    >
                </div>
                
                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea 
                        id="bio" 
                        name="bio"
                        class="form-textarea"
                    ><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div class="divider"></div>
            
            <div class="form-section">
                <h3>Preferences</h3>
                
                <div class="form-group">
                    <label for="timezone">Timezone</label>
                    <select id="timezone" name="timezone" class="form-select">
                        <option value="UTC" <?php echo $profile['timezone'] === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                        <option value="America/New_York" <?php echo $profile['timezone'] === 'America/New_York' ? 'selected' : ''; ?>>Eastern (US)</option>
                        <option value="America/Chicago" <?php echo $profile['timezone'] === 'America/Chicago' ? 'selected' : ''; ?>>Central (US)</option>
                        <option value="America/Denver" <?php echo $profile['timezone'] === 'America/Denver' ? 'selected' : ''; ?>>Mountain (US)</option>
                        <option value="America/Los_Angeles" <?php echo $profile['timezone'] === 'America/Los_Angeles' ? 'selected' : ''; ?>>Pacific (US)</option>
                        <option value="Europe/London" <?php echo $profile['timezone'] === 'Europe/London' ? 'selected' : ''; ?>>London</option>
                        <option value="Europe/Paris" <?php echo $profile['timezone'] === 'Europe/Paris' ? 'selected' : ''; ?>>Paris</option>
                        <option value="Asia/Dubai" <?php echo $profile['timezone'] === 'Asia/Dubai' ? 'selected' : ''; ?>>Dubai</option>
                        <option value="Asia/Bangkok" <?php echo $profile['timezone'] === 'Asia/Bangkok' ? 'selected' : ''; ?>>Bangkok</option>
                        <option value="Asia/Shanghai" <?php echo $profile['timezone'] === 'Asia/Shanghai' ? 'selected' : ''; ?>>Shanghai</option>
                        <option value="Asia/Tokyo" <?php echo $profile['timezone'] === 'Asia/Tokyo' ? 'selected' : ''; ?>>Tokyo</option>
                        <option value="Australia/Sydney" <?php echo $profile['timezone'] === 'Australia/Sydney' ? 'selected' : ''; ?>>Sydney</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="theme">Theme</label>
                    <select id="theme" name="theme" class="form-select">
                        <option value="light" <?php echo $profile['theme'] === 'light' ? 'selected' : ''; ?>>Light</option>
                        <option value="dark" <?php echo $profile['theme'] === 'dark' ? 'selected' : ''; ?>>Dark</option>
                    </select>
                </div>
            </div>
            
            <div class="divider"></div>
            
            <div class="form-section">
                <h3>Notifications</h3>
                
                <div class="form-group checkbox">
                    <input 
                        type="checkbox" 
                        id="notifications_enabled" 
                        name="notifications_enabled"
                        <?php echo $settings['notifications_enabled'] ? 'checked' : ''; ?>
                        class="form-checkbox"
                    >
                    <label for="notifications_enabled">Enable notifications</label>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Settings</button>
                <a href="<?php echo SITE_URL; ?>/dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
.settings-container {
    max-width: 700px;
    margin: 0 auto;
}

.form-section {
    margin-bottom: var(--spacing-lg);
}

.form-section h3 {
    font-size: var(--font-size-lg);
    margin-bottom: var(--spacing-lg);
}

.divider {
    height: 1px;
    background-color: var(--border-color);
    margin: var(--spacing-xl) 0;
}

.form-actions {
    display: flex;
    gap: var(--spacing-md);
    margin-top: var(--spacing-lg);
}

.form-actions .btn {
    flex: 1;
}
</style>

<?php include 'includes/footer.php'; ?>
