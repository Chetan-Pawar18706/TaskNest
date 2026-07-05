<?php
/**
 * TaskNest - Two-Factor Authentication Setup
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin($auth);

$user_id = $auth->getUserId();
$errors = [];
$success = '';
$step = 1;
$secret = null;
$backupCodes = null;

// Check if 2FA is already enabled
$isEnabled = $auth->isTwoFactorEnabled($user_id);

if ($isEnabled) {
    // Handle disable request
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'disable') {
        if (!isset($_POST['csrf_token']) || !$auth->verifyCsrfToken($_POST['csrf_token'])) {
            $errors[] = 'Security token invalid';
        } else {
            $password = $_POST['password'] ?? '';
            $stmt = $mysqli->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if (!password_verify($password, $result['password_hash'])) {
                $errors[] = 'Incorrect password';
            } else {
                $auth->disableTwoFactor($user_id);
                $success = 'Two-factor authentication has been disabled.';
                $isEnabled = false;
            }
        }
    }
}

// Handle setup steps
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isEnabled) {
    if (!isset($_POST['csrf_token']) || !$auth->verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Security token invalid';
    } else {
        $step = (int) ($_POST['step'] ?? 1);
        
        if ($step === 1) {
            // Step 1: Generate secret and show QR
            $secret = $auth->generateTwoFactorSecret();
            $_SESSION['2fa_setup_secret'] = $secret;
            $step = 2;
        } elseif ($step === 2) {
            // Step 2: Verify code and enable
            $secret = $_SESSION['2fa_setup_secret'] ?? null;
            $code = $_POST['code'] ?? '';
            
            if (!$secret) {
                $errors[] = 'Session expired. Please start again.';
                $step = 1;
            } elseif (empty($code)) {
                $errors[] = 'Please enter the verification code';
                $step = 2;
            } elseif (!$auth->verifyTotpCode($secret, $code)) {
                $errors[] = 'Invalid code. Please try again.';
                $step = 2;
            } else {
                // Generate backup codes and enable 2FA
                $backupCodes = $auth->generateBackupCodes();
                $auth->enableTwoFactor($user_id, $secret, $backupCodes);
                unset($_SESSION['2fa_setup_secret']);
                $step = 3;
                $success = 'Two-factor authentication has been enabled!';
            }
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && !$isEnabled) {
    $step = 1;
}

$page_title = 'Two-Factor Authentication';
include __DIR__ . '/includes/header.php';
?>

<div class="tfa-container">
    <div class="card">
        <div class="card-header">
            <h2><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg> Two-Factor Authentication</h2>
        </div>
        
        <?php if ($success): ?>
        <div class="alert alert-success"><p><?php echo htmlspecialchars($success); ?></p></div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($isEnabled && $step !== 3): ?>
            <!-- 2FA is enabled - show status and disable option -->
            <div class="tfa-status">
                <div class="tfa-status-badge enabled">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <span>2FA is <strong>Enabled</strong></span>
                </div>
                <p class="tfa-description">Your account is protected with an authenticator app. You'll be asked for a code when signing in from a new device.</p>
            </div>
            
            <form method="POST" class="tfa-disable-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($auth->generateCsrfToken()); ?>">
                <input type="hidden" name="action" value="disable">
                
                <div class="tfa-warning">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <span>Disabling 2FA will make your account less secure. Enter your password to confirm.</span>
                </div>
                
                <div class="form-group">
                    <label for="password">Confirm Password</label>
                    <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="btn btn-danger btn-block">Disable Two-Factor Authentication</button>
            </form>
            
        <?php elseif ($step === 1): ?>
            <!-- Step 1: Introduction -->
            <div class="tfa-intro">
                <div class="tfa-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="48" height="48"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </div>
                <h3>Add an extra layer of security</h3>
                <p>Two-factor authentication (2FA) adds a second layer of protection to your account. Even if someone knows your password, they won't be able to sign in without the code from your phone.</p>
                
                <div class="tfa-steps">
                    <div class="tfa-step">
                        <span class="tfa-step-num">1</span>
                        <div>
                            <strong>Install an authenticator app</strong>
                            <p>Download Google Authenticator, Authy, or similar app on your phone.</p>
                        </div>
                    </div>
                    <div class="tfa-step">
                        <span class="tfa-step-num">2</span>
                        <div>
                            <strong>Scan the QR code</strong>
                            <p>We'll show you a QR code to scan with your authenticator app.</p>
                        </div>
                    </div>
                    <div class="tfa-step">
                        <span class="tfa-step-num">3</span>
                        <div>
                            <strong>Verify & activate</strong>
                            <p>Enter the 6-digit code from your app to confirm setup.</p>
                        </div>
                    </div>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($auth->generateCsrfToken()); ?>">
                    <input type="hidden" name="step" value="1">
                    <button type="submit" class="btn btn-primary btn-block">Continue Setup</button>
                </form>
            </div>
            
        <?php elseif ($step === 2): ?>
            <!-- Step 2: Scan QR Code -->
            <?php 
            $uri = $auth->getTwoFactorUri($secret);
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($uri);
            ?>
            <div class="tfa-qr-step">
                <h3>Scan QR Code</h3>
                <p>Open your authenticator app and scan this QR code:</p>
                
                <div class="tfa-qr-wrapper">
                    <img src="<?php echo $qrUrl; ?>" alt="QR Code" class="tfa-qr-code" width="200" height="200">
                </div>
                
                <div class="tfa-manual-section">
                    <p>Can't scan? Enter this code manually:</p>
                    <div class="tfa-secret-box">
                        <code><?php echo chunk_split($secret, 4, ' '); ?></code>
                        <button type="button" class="tfa-copy-btn" onclick="navigator.clipboard.writeText('<?php echo $secret; ?>').then(()=>this.textContent='Copied!')">Copy</button>
                    </div>
                </div>
                
                <div class="tfa-verify-divider">
                    <span>Enter code to verify</span>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($auth->generateCsrfToken()); ?>">
                    <input type="hidden" name="step" value="2">
                    
                    <div class="form-group">
                        <label for="code">6-Digit Code</label>
                        <input type="text" id="code" name="code" class="form-input tfa-code-input" placeholder="000000" maxlength="6" pattern="[0-9]{6}" autocomplete="off" required autofocus>
                        <small class="form-help">Enter the 6-digit code from your authenticator app</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Verify & Enable 2FA</button>
                </form>
            </div>
            
        <?php elseif ($step === 3): ?>
            <!-- Step 3: Backup Codes -->
            <div class="tfa-backup-step">
                <div class="tfa-success-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="48" height="48"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <h3>2FA Enabled Successfully!</h3>
                <p>Save these backup codes in a safe place. You can use each code <strong>once</strong> if you lose access to your authenticator app.</p>
                
                <div class="tfa-backup-codes">
                    <?php if ($backupCodes): ?>
                        <?php foreach ($backupCodes as $code): ?>
                            <span class="backup-code"><?php echo $code; ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="tfa-backup-actions">
                    <button type="button" class="btn btn-secondary" onclick="copyBackupCodes()">Copy All Codes</button>
                    <button type="button" class="btn btn-secondary" onclick="downloadBackupCodes()">Download</button>
                </div>
                
                <div class="tfa-warning">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <span>These codes won't be shown again. Store them somewhere safe now!</span>
                </div>
                
                <a href="<?php echo SITE_URL; ?>/settings.php" class="btn btn-primary btn-block">Back to Settings</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function copyBackupCodes() {
    var codes = document.querySelectorAll('.backup-code');
    var text = Array.from(codes).map(function(el) { return el.textContent; }).join('\n');
    navigator.clipboard.writeText(text).then(function() {
        alert('Backup codes copied to clipboard!');
    });
}

function downloadBackupCodes() {
    var codes = document.querySelectorAll('.backup-code');
    var text = 'TaskNest - Backup Codes\n';
    text += '========================\n\n';
    text += 'Keep these codes safe. Each code can be used once.\n\n';
    Array.from(codes).forEach(function(el) {
        text += el.textContent + '\n';
    });
    text += '\nGenerated: ' + new Date().toLocaleString();
    
    var blob = new Blob([text], { type: 'text/plain' });
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'tasknest-backup-codes.txt';
    a.click();
}

// Auto-format code input
document.getElementById('code')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
