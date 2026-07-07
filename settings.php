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
$settings = $stmt->get_result()->fetch_assoc() ?: ['notifications_enabled' => 0];

// Get user profile
$stmt = safePrepare($mysqli, "SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Quick AJAX theme save
    if (isset($_POST['action']) && $_POST['action'] === 'save_theme') {
        header('Content-Type: application/json');
        if (!isset($_POST['csrf_token']) || !$auth->verifyCsrfToken($_POST['csrf_token'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid token']);
            exit;
        }
        $theme = in_array($_POST['theme'] ?? '', ['light', 'dark']) ? $_POST['theme'] : 'light';
        $stmt = safePrepare($mysqli, "UPDATE users SET theme = ? WHERE id = ?");
        $stmt->bind_param("si", $theme, $user_id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        exit;
    }

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
        $avatar_url = $profile['avatar_url'] ?? '';
        
        // Handle profile picture upload
        if (!empty($_FILES['profile_picture']['name'])) {
            $allowed = ['png','jpg','jpeg','gif','webp'];
            $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $errors[] = 'Profile picture must be PNG, JPG, GIF, or WebP.';
            } elseif ($_FILES['profile_picture']['size'] > 5 * 1024 * 1024) {
                $errors[] = 'Profile picture must be less than 5MB.';
            } else {
                $dir = __DIR__ . '/uploads/profile/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $filename = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $dir . $filename)) {
                    // Delete old avatar if exists
                    if ($avatar_url && strpos($avatar_url, 'uploads/profile/') === 0 && file_exists(__DIR__ . '/' . $avatar_url)) {
                        unlink(__DIR__ . '/' . $avatar_url);
                    }
                    $avatar_url = 'uploads/profile/' . $filename;
                } else {
                    $errors[] = 'Failed to upload profile picture.';
                }
            }
        } elseif (!empty($_POST['remove_picture'])) {
            if ($avatar_url && strpos($avatar_url, 'uploads/profile/') === 0 && file_exists(__DIR__ . '/' . $avatar_url)) {
                unlink(__DIR__ . '/' . $avatar_url);
            }
            $avatar_url = '';
        }
        
        if (empty($errors)) {
            // Update user profile
            $stmt = safePrepare($mysqli, "
                UPDATE users 
                SET first_name = ?, last_name = ?, phone = ?, bio = ?, timezone = ?, theme = ?, avatar_url = ?
                WHERE id = ?
            ");
            $stmt->bind_param("sssssssi", $first_name, $last_name, $phone, $bio, $timezone, $theme, $avatar_url, $user_id);
            
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
                
                // Apply theme immediately via JS
                echo '<script>localStorage.setItem("tasknest-theme", "' . htmlspecialchars($theme) . '"); document.body.setAttribute("data-theme", "' . htmlspecialchars($theme) . '"); document.documentElement.classList.remove("light","dark"); document.documentElement.classList.add("' . htmlspecialchars($theme) . '");</script>';
                
                // Refresh profile data
                $stmt = safePrepare($mysqli, "SELECT * FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $profile = $stmt->get_result()->fetch_assoc();
            } else {
                $errors[] = 'Failed to save settings';
            }
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
        
        <form method="POST" class="settings-form" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($auth->generateCsrfToken()); ?>">
            
            <div class="form-section">
                <h3>Personal Information</h3>
                
                <div class="form-group">
                    <label>Profile Picture</label>
                    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:0.25rem;">
                        <div style="width:56px;height:56px;border-radius:50%;overflow:hidden;background:var(--bg-secondary);display:flex;align-items:center;justify-content:center;border:2px solid var(--border-color);flex-shrink:0;">
                            <?php if (!empty($profile['avatar_url'])): ?>
                                <img src="<?php echo SITE_URL; ?>/<?php echo htmlspecialchars($profile['avatar_url']); ?>" alt="Profile" style="width:100%;height:100%;object-fit:cover;">
                            <?php else: ?>
                                <img src="<?php echo SITE_URL; ?>/assets/images/default-avatar.svg" alt="Profile" style="width:100%;height:100%;object-fit:cover;">
                            <?php endif; ?>
                        </div>
                        <div style="flex:1;">
                            <input type="file" id="profile_picture" name="profile_picture" accept="image/png,image/jpeg,image/gif,image/webp" style="font-size:0.8rem;">
                            <small style="color:var(--text-muted);display:block;margin-top:0.15rem;">Max 5MB. PNG, JPG, GIF, WebP.</small>
                            <?php if (!empty($profile['avatar_url'])): ?>
                            <label style="display:flex;align-items:center;gap:0.25rem;font-size:0.75rem;margin-top:0.25rem;cursor:pointer;color:var(--danger);">
                                <input type="checkbox" name="remove_picture" value="1"> Remove
                            </label>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
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
            
            <div class="divider"></div>
            
            <div class="form-section">
                <h3>Security</h3>
                
                <?php $tfaEnabled = $auth->isTwoFactorEnabled($user_id); ?>
                <div class="tfa-settings-row">
                    <div>
                        <strong>Two-Factor Authentication</strong>
                        <p class="tfa-settings-desc">Add an extra layer of security to your account</p>
                    </div>
                    <?php if ($tfaEnabled): ?>
                        <span class="badge badge-success">Enabled</span>
                    <?php else: ?>
                        <span class="badge badge-warning">Disabled</span>
                    <?php endif; ?>
                </div>
                
                <a href="<?php echo SITE_URL; ?>/2fa-setup.php" class="btn btn-secondary" style="margin-top:0.5rem;">
                    <?php echo $tfaEnabled ? 'Manage 2FA' : 'Enable 2FA'; ?>
                </a>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Settings</button>
                <a href="<?php echo SITE_URL; ?>/dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    
    <!-- Feedback Link -->
    <div class="card" style="margin-top:var(--spacing-lg);">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h3 style="margin:0 0 0.25rem;font-size:1rem;">Send Feedback</h3>
                <p style="color:var(--text-secondary);font-size:0.85rem;margin:0;">
                    Found a bug? Have a feature request? Let us know!
                </p>
            </div>
            <a href="<?php echo SITE_URL; ?>/feedback.php" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                Go to Feedback
            </a>
        </div>
    </div>
</div>

<style>
.settings-container {
    max-width: 700px;
    margin: 0 auto;
}

.form-section {
    margin-bottom: var(--spacing-sm);
}

.form-section h3 {
    font-size: 0.95rem;
    margin-bottom: var(--spacing-sm);
}

.divider {
    height: 1px;
    background-color: var(--border-color);
    margin: var(--spacing-sm) 0;
}

.form-actions {
    display: flex;
    gap: var(--spacing-sm);
    margin-top: var(--spacing-sm);
}

.form-actions .btn {
    flex: 1;
    height: 38px;
    font-size: 0.875rem;
}
</style>

<?php include 'includes/footer.php'; ?>
