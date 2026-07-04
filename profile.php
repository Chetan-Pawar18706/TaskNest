<?php
/**
 * TaskNest - User Profile
 */

require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin($auth);

$user = $auth->getUser();
$user_id = $auth->getUserId();
$errors = [];
$success = '';

// Get full user profile
$stmt = safePrepare($mysqli, "SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

$page_title = 'My Profile';
include 'includes/header.php';
?>

<div class="profile-container">
    <div class="card">
        <div class="card-header">
            <h2><?php echo $page_title; ?></h2>
        </div>
        
        <div class="card-body">
            <div class="profile-header">
                <img src="<?php echo htmlspecialchars($profile['avatar_url'] ?? getGravatarUrl($profile['email'])); ?>" alt="Profile picture" class="profile-avatar">
                <div class="profile-info">
                    <h3><?php echo htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']); ?></h3>
                    <p><?php echo htmlspecialchars($profile['email']); ?></p>
                    <p>Joined <?php echo formatDate($profile['created_at'], 'M d, Y'); ?></p>
                </div>
            </div>
            
            <div class="divider"></div>
            
            <div class="profile-details">
                <div class="detail-row">
                    <label>Username</label>
                    <p><?php echo htmlspecialchars($profile['username']); ?></p>
                </div>
                
                <div class="detail-row">
                    <label>Email</label>
                    <p><?php echo htmlspecialchars($profile['email']); ?></p>
                </div>
                
                <div class="detail-row">
                    <label>Phone</label>
                    <p><?php echo htmlspecialchars($profile['phone'] ?? 'Not provided'); ?></p>
                </div>
                
                <div class="detail-row">
                    <label>Timezone</label>
                    <p><?php echo htmlspecialchars($profile['timezone']); ?></p>
                </div>
                
                <div class="detail-row">
                    <label>Bio</label>
                    <p><?php echo htmlspecialchars($profile['bio'] ?? 'No bio provided'); ?></p>
                </div>
            </div>
            
            <div class="divider"></div>
            
            <div class="profile-actions">
                <a href="<?php echo SITE_URL; ?>/settings.php" class="btn btn-primary">Edit Profile</a>
                <a href="<?php echo SITE_URL; ?>/dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<style>
.profile-container {
    max-width: 600px;
    margin: 0 auto;
}

.profile-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-lg);
    margin-bottom: var(--spacing-lg);
}

.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: var(--radius-full);
    object-fit: cover;
    border: 3px solid var(--color-primary);
}

.profile-info h3 {
    margin: 0 0 var(--spacing-sm) 0;
}

.profile-info p {
    margin: 0;
    color: var(--text-secondary);
}

.divider {
    height: 1px;
    background-color: var(--border-color);
    margin: var(--spacing-lg) 0;
}

.profile-details {
    margin-bottom: var(--spacing-lg);
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: var(--spacing-md) 0;
    border-bottom: 1px solid var(--border-color);
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-row label {
    font-weight: var(--font-weight-semibold);
}

.detail-row p {
    margin: 0;
    color: var(--text-secondary);
}

.profile-actions {
    display: flex;
    gap: var(--spacing-md);
}

.profile-actions .btn {
    flex: 1;
}
</style>

<?php include 'includes/footer.php'; ?>
