<?php
/**
 * TaskNest - Feedback Page
 */
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin($auth);

$user_id = $auth->getUserId();
$errors = [];
$success = '';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (!isset($_POST['csrf_token']) || !$auth->verifyCsrfToken($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid token']);
        exit;
    }
    
    $action = $_POST['action'] ?? '';
    
    if ($action === 'submit_feedback') {
        $result = submitFeedbackHandler($mysqli, $user_id, $_POST);
        echo json_encode($result);
        exit;
    }
    
    if ($action === 'delete_feedback') {
        $feedbackId = (int) ($_POST['feedback_id'] ?? 0);
        $result = deleteFeedbackHandler($mysqli, $feedbackId, $user_id);
        echo json_encode($result);
        exit;
    }
    
    echo json_encode(['success' => false, 'message' => 'Unknown action']);
    exit;
}

// Get user's feedback
$feedbackList = getUserFeedback($mysqli, $user_id);

$page_title = 'My Feedback';
include 'includes/header.php';
?>

<div class="feedback-page">
    <div class="feedback-header">
        <div>
            <h1 class="feedback-title">My Feedback</h1>
            <p class="feedback-subtitle">Submit feedback and track admin responses</p>
        </div>
        <button class="btn btn-primary" onclick="toggleFeedbackForm()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            New Feedback
        </button>
    </div>

    <!-- Submit Feedback Form (hidden by default) -->
    <div class="card feedback-form-card" id="feedbackFormCard" style="display:none;">
        <div class="card-header">
            <h2>Submit New Feedback</h2>
            <button class="btn-close" onclick="toggleFeedbackForm()" aria-label="Close">&times;</button>
        </div>
        
        <div id="fbSuccess" class="alert alert-success" style="display:none;">
            <p>Your feedback has been submitted successfully!</p>
        </div>
        <div id="fbError" class="alert alert-error" style="display:none;">
            <p id="fbErrorMsg"></p>
        </div>
        
        <form id="feedbackForm" onsubmit="return submitNewFeedback(event)">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($auth->generateCsrfToken()); ?>">
            <input type="hidden" name="action" value="submit_feedback">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="fb_category">Category <span style="color:#ef4444;">*</span></label>
                    <select id="fb_category" name="category" class="form-select" required>
                        <option value="">Select category</option>
                        <option value="bug">Bug Report</option>
                        <option value="feature">Feature Request</option>
                        <option value="improvement">Improvement</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="fb_subject">Subject <span style="color:#ef4444;">*</span></label>
                    <input type="text" id="fb_subject" name="subject" class="form-input" placeholder="Brief summary" required maxlength="255">
                </div>
            </div>
            
            <div class="form-group">
                <label for="fb_message">Message <span style="color:#ef4444;">*</span></label>
                <textarea id="fb_message" name="message" class="form-textarea" rows="5" placeholder="Describe your feedback in detail..." required></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="toggleFeedbackForm()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="fbSubmitBtn">Submit Feedback</button>
            </div>
        </form>
    </div>

    <!-- Feedback Stats -->
    <div class="feedback-stats">
        <div class="stat-item">
            <span class="stat-num"><?php echo count($feedbackList); ?></span>
            <span class="stat-label">Total</span>
        </div>
        <div class="stat-item">
            <span class="stat-num"><?php echo count(array_filter($feedbackList, function($f) { return $f['status'] === 'open'; })); ?></span>
            <span class="stat-label">Open</span>
        </div>
        <div class="stat-item">
            <span class="stat-num"><?php echo count(array_filter($feedbackList, function($f) { return $f['status'] === 'in_progress'; })); ?></span>
            <span class="stat-label">In Progress</span>
        </div>
        <div class="stat-item">
            <span class="stat-num"><?php echo count(array_filter($feedbackList, function($f) { return $f['status'] === 'resolved'; })); ?></span>
            <span class="stat-label">Resolved</span>
        </div>
    </div>

    <!-- Feedback List -->
    <?php if (empty($feedbackList)): ?>
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="48" height="48" style="opacity:0.4;">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <p>No feedback submitted yet.</p>
            <button class="btn btn-primary" onclick="toggleFeedbackForm()">Submit Your First Feedback</button>
        </div>
    <?php else: ?>
        <div class="feedback-list">
            <?php foreach ($feedbackList as $fb): ?>
                <div class="card feedback-item" data-id="<?php echo (int) $fb['id']; ?>">
                    <div class="feedback-item-header">
                        <div class="feedback-item-info">
                            <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                                <h3><?php echo htmlspecialchars($fb['subject']); ?></h3>
                                <span class="fb-id">#<?php echo (int) $fb['id']; ?></span>
                            </div>
                            <div class="feedback-meta">
                                <span class="feedback-category">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                        <line x1="7" y1="7" x2="7.01" y2="7"></line>
                                    </svg>
                                    <?php echo ucfirst(htmlspecialchars($fb['category'])); ?>
                                </span>
                                <span class="feedback-time">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    <?php echo timeAgo($fb['created_at']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="feedback-badges">
                            <?php if (!empty($fb['admin_reply'])): ?>
                                <span class="fb-badge fb-badge-replied" title="Admin has replied">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                    Replied
                                </span>
                            <?php elseif (!empty($fb['viewed_by_admin'])): ?>
                                <span class="fb-badge fb-badge-viewed" title="Admin has viewed your feedback">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    Viewed
                                </span>
                            <?php else: ?>
                                <span class="fb-badge fb-badge-pending" title="Waiting for admin to view">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    Pending
                                </span>
                            <?php endif; ?>
                            <span class="fb-badge fb-badge-<?php echo htmlspecialchars($fb['status']); ?>">
                                <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($fb['status']))); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="feedback-message">
                        <p><?php echo nl2br(htmlspecialchars($fb['message'])); ?></p>
                    </div>
                    
                    <?php if (!empty($fb['admin_reply'])): ?>
                        <div class="admin-reply">
                            <div class="admin-reply-header">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <path d="M12 2 3 6v6c0 5 3.5 8.5 9 10 5.5-1.5 9-5 9-10V6l-9-4Z"></path>
                                </svg>
                                <strong>Admin Reply</strong>
                                <span class="admin-reply-time">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    <?php echo date('M d, Y \a\t h:i A', strtotime($fb['admin_replied_at'])); ?>
                                    (<?php echo timeAgo($fb['admin_replied_at']); ?>)
                                </span>
                            </div>
                            <p><?php echo nl2br(htmlspecialchars($fb['admin_reply'])); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Details Section -->
                    <div class="feedback-details">
                        <div class="detail-row">
                            <span class="detail-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                Submitted
                            </span>
                            <span class="detail-value"><?php echo date('D, M d, Y', strtotime($fb['created_at'])); ?> at <?php echo date('h:i A', strtotime($fb['created_at'])); ?></span>
                        </div>
                        <?php if ($fb['updated_at'] !== $fb['created_at']): ?>
                        <div class="detail-row">
                            <span class="detail-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                                    <path d="M12 20h9"></path>
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                </svg>
                                Last Updated
                            </span>
                            <span class="detail-value"><?php echo date('D, M d, Y', strtotime($fb['updated_at'])); ?> at <?php echo date('h:i A', strtotime($fb['updated_at'])); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($fb['admin_replied_at'])): ?>
                        <div class="detail-row">
                            <span class="detail-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                                </svg>
                                Admin Replied
                            </span>
                            <span class="detail-value"><?php echo date('D, M d, Y', strtotime($fb['admin_replied_at'])); ?> at <?php echo date('h:i A', strtotime($fb['admin_replied_at'])); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="detail-row">
                            <span class="detail-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                Status
                            </span>
                            <span class="detail-value"><?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($fb['status']))); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                Admin View
                            </span>
                            <span class="detail-value">
                                <?php if (!empty($fb['viewed_by_admin'])): ?>
                                    <span style="color:#10b981;">Viewed</span>
                                <?php else: ?>
                                    <span style="color:#d97706;">Not Viewed Yet</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="feedback-item-footer">
                        <span class="feedback-date-id">Feedback ID: #<?php echo (int) $fb['id']; ?></span>
                        <?php if ($fb['status'] === 'open'): ?>
                            <button class="btn btn-sm btn-danger" onclick="deleteFeedback(<?php echo (int) $fb['id']; ?>)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                                Delete
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.feedback-page {
    max-width: 800px;
    margin: 0 auto;
}

.feedback-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.feedback-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
}

.feedback-subtitle {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin: 0.25rem 0 0;
}

.feedback-form-card .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.feedback-form-card .card-header h2 {
    margin: 0;
    font-size: 1.1rem;
}

.btn-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-muted);
    line-height: 1;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-md);
    transition: all 0.15s;
}

.btn-close:hover {
    background: var(--bg-tertiary);
    color: var(--text-primary);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
    margin-top: 1rem;
}

/* Stats */
.feedback-stats {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.feedback-stats .stat-item {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 0.75rem 1.25rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 80px;
    flex: 1;
}

.feedback-stats .stat-num {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
}

.feedback-stats .stat-label {
    font-size: 0.75rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Feedback Items */
.feedback-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.feedback-item {
    padding: 1.25rem;
}

.feedback-item-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 0.75rem;
}

.feedback-item-info h3 {
    margin: 0 0 0.25rem;
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--text-primary);
}

.feedback-meta {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    font-size: 0.8rem;
}

.feedback-category {
    color: var(--color-primary);
    font-weight: 500;
}

.feedback-time {
    color: var(--text-tertiary);
}

.feedback-badges {
    display: flex;
    gap: 0.4rem;
    flex-wrap: wrap;
    flex-shrink: 0;
}

.fb-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 600;
    white-space: nowrap;
}

.fb-badge-pending {
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
}

.fb-badge-viewed {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.fb-badge-replied {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.fb-badge-open {
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
}

.fb-badge-in_progress {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
}

.fb-badge-resolved {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.fb-badge-closed {
    background: rgba(107, 114, 128, 0.1);
    color: #6b7280;
}

.feedback-message {
    color: var(--text-secondary);
    font-size: 0.9rem;
    line-height: 1.6;
    margin-bottom: 0.75rem;
}

.feedback-message p {
    margin: 0;
}

/* Admin Reply */
.admin-reply {
    background: rgba(99, 102, 241, 0.05);
    border: 1px solid rgba(99, 102, 241, 0.15);
    border-radius: var(--radius-lg);
    padding: 1rem;
    margin-bottom: 0.75rem;
}

.admin-reply-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    color: var(--color-primary);
}

.admin-reply-header strong {
    font-size: 0.85rem;
}

.admin-reply-time {
    font-size: 0.75rem;
    color: var(--text-tertiary);
    margin-left: auto;
}

.admin-reply p {
    margin: 0;
    font-size: 0.9rem;
    color: var(--text-primary);
    line-height: 1.6;
}

/* Footer */
.feedback-item-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 0.75rem;
    border-top: 1px solid var(--border-color);
}

.feedback-date {
    font-size: 0.75rem;
    color: var(--text-tertiary);
}

.feedback-date-id {
    font-size: 0.7rem;
    color: var(--text-tertiary);
    font-family: monospace;
}

.fb-id {
    font-size: 0.7rem;
    color: var(--text-tertiary);
    background: var(--bg-secondary);
    padding: 0.1rem 0.4rem;
    border-radius: var(--radius-md);
    font-family: monospace;
}

/* Details Section */
.feedback-details {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: 0.75rem 1rem;
    margin-bottom: 0.75rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.35rem 0;
    border-bottom: 1px solid var(--border-color);
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.75rem;
    color: var(--text-secondary);
    font-weight: 500;
}

.detail-value {
    font-size: 0.75rem;
    color: var(--text-primary);
    font-weight: 500;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--text-secondary);
}

.empty-state p {
    margin: 1rem 0;
}

/* Responsive */
@media (max-width: 768px) {
    .feedback-header {
        flex-direction: column;
    }
    
    .feedback-item-header {
        flex-direction: column;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .feedback-stats {
        flex-wrap: wrap;
    }
    
    .feedback-stats .stat-item {
        min-width: calc(50% - 0.5rem);
    }
    
    .detail-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
}

@media (max-width: 480px) {
    .feedback-stats .stat-item {
        min-width: 100%;
    }
    
    .feedback-badges {
        width: 100%;
    }
    
    .feedback-details {
        padding: 0.5rem 0.75rem;
    }
}
</style>

<script>
function toggleFeedbackForm() {
    var card = document.getElementById('feedbackFormCard');
    var success = document.getElementById('fbSuccess');
    var error = document.getElementById('fbError');
    
    if (card.style.display === 'none') {
        card.style.display = 'block';
        card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
        card.style.display = 'none';
        success.style.display = 'none';
        error.style.display = 'none';
        document.getElementById('feedbackForm').reset();
    }
}

function submitNewFeedback(e) {
    e.preventDefault();
    var form = document.getElementById('feedbackForm');
    var btn = document.getElementById('fbSubmitBtn');
    var success = document.getElementById('fbSuccess');
    var error = document.getElementById('fbError');
    var errorMsg = document.getElementById('fbErrorMsg');
    
    success.style.display = 'none';
    error.style.display = 'none';
    
    var formData = new FormData(form);
    
    btn.disabled = true;
    btn.textContent = 'Submitting...';
    
    fetch('feedback.php', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            success.style.display = 'block';
            form.reset();
            setTimeout(function() { location.reload(); }, 1500);
        } else {
            errorMsg.textContent = data.message || 'Failed to submit feedback.';
            error.style.display = 'block';
        }
    })
    .catch(function() {
        errorMsg.textContent = 'An error occurred. Please try again.';
        error.style.display = 'block';
    })
    .finally(function() {
        btn.disabled = false;
        btn.textContent = 'Submit Feedback';
    });
    
    return false;
}

function deleteFeedback(id) {
    if (!confirm('Are you sure you want to delete this feedback?')) return;
    
    var formData = new FormData();
    formData.append('action', 'delete_feedback');
    formData.append('feedback_id', id);
    formData.append('csrf_token', '<?php echo htmlspecialchars($auth->generateCsrfToken()); ?>');
    
    fetch('feedback.php', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var item = document.querySelector('.feedback-item[data-id="' + id + '"]');
            if (item) {
                item.style.transition = 'opacity 0.3s, transform 0.3s';
                item.style.opacity = '0';
                item.style.transform = 'translateX(-20px)';
                setTimeout(function() { item.remove(); }, 300);
            }
        } else {
            alert(data.message || 'Failed to delete feedback.');
        }
    })
    .catch(function() {
        alert('An error occurred. Please try again.');
    });
}
</script>

<?php include 'includes/footer.php'; ?>
