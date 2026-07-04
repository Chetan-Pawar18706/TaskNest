<?php
/**
 * TaskNest - Smart Notes Module
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

requireLogin($auth);

$user_id = $auth->getUserId();
$page_title = 'Notes';
$additional_css = ['notes.css'];
$additional_js = ['notes.js'];

$filters = [
    'search' => trim($_GET['search'] ?? ''),
    'category' => $_GET['category'] ?? '',
    'show_archived' => isset($_GET['show_archived']) ? 1 : 0,
    'show_deleted' => isset($_GET['show_deleted']) ? 1 : 0,
    'page' => max(1, (int) ($_GET['page'] ?? 1))
];

$categories = getNoteCategories($mysqli, $user_id);
$notesData = getNotesForList($mysqli, $user_id, $filters);
$noteStats = getNoteStats($mysqli, $user_id);

include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="notes-page">
    <div class="notes-toolbar">
        <div>
            <h1 class="notes-title">Notes</h1>
            <p class="notes-subtitle">Capture ideas, organize thoughts, and never lose information.</p>
        </div>
        <div class="notes-toolbar-actions">
            <a href="<?php echo SITE_URL; ?>/notes-categories.php" class="btn btn-secondary">Manage Categories</a>
            <a href="<?php echo SITE_URL; ?>/notes-add.php" class="btn btn-primary">Add Note</a>
        </div>
    </div>

    <div class="notes-summary">
        <div class="summary-card">
            <span class="summary-label">Total</span>
            <strong><?php echo (int) $noteStats['total']; ?></strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Pinned</span>
            <strong><?php echo (int) $noteStats['pinned']; ?></strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Archived</span>
            <strong><?php echo (int) $noteStats['archived']; ?></strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Trashed</span>
            <strong><?php echo (int) $noteStats['deleted']; ?></strong>
        </div>
    </div>

    <div class="notes-controls">
        <form class="notes-filters" id="notesFilterForm" method="get">
            <input type="text" name="search" class="form-control" placeholder="Search notes..." value="<?php echo htmlspecialchars($filters['search']); ?>">
            <select name="category" class="form-control">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo (int) $cat['id']; ?>" <?php echo (string) $filters['category'] === (string) $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <label class="checkbox-inline"><input type="checkbox" name="show_archived" value="1" <?php echo $filters['show_archived'] ? 'checked' : ''; ?>> Show Archived</label>
            <label class="checkbox-inline"><input type="checkbox" name="show_deleted" value="1" <?php echo $filters['show_deleted'] ? 'checked' : ''; ?>> Show Trash</label>
            <button class="btn btn-secondary" type="submit">Apply</button>
        </form>
    </div>

    <div class="notes-actions-row">
        <div class="bulk-actions">
            <button class="btn btn-secondary" type="button" id="bulkArchiveBtn">Archive</button>
            <button class="btn btn-secondary" type="button" id="bulkDeleteBtn">Delete</button>
        </div>
        <div class="page-info">
            <span><?php echo (int) $notesData['total']; ?> notes</span>
        </div>
    </div>

    <div id="notesContainer">
        <?php if (!empty($notesData['notes'])): ?>
            <div class="notes-grid">
                <?php foreach ($notesData['notes'] as $note): ?>
                    <article class="note-card <?php echo $note['is_pinned'] ? 'is-pinned' : ''; ?>" data-note-id="<?php echo (int) $note['id']; ?>">
                        <div class="note-card-top">
                            <input type="checkbox" class="note-checkbox" value="<?php echo (int) $note['id']; ?>">
                            <?php if ($note['is_pinned']): ?>
                                <span class="note-pin-icon" title="Pinned">&#x1F4CC;</span>
                            <?php endif; ?>
                        </div>
                        <h3><?php echo htmlspecialchars($note['title']); ?></h3>
                        <div class="note-content-preview"><?php echo strip_tags($note['content'] ?? ''); ?></div>
                        <div class="note-card-meta">
                            <span><?php echo timeAgo($note['updated_at']); ?></span>
                            <?php if (!empty($note['category_name'])): ?>
                                <span class="note-category-badge" style="background:<?php echo htmlspecialchars($note['category_color'] ?? '#6366f1'); ?>20;color:<?php echo htmlspecialchars($note['category_color'] ?? '#6366f1'); ?>"><?php echo htmlspecialchars($note['category_name']); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($note['file_path'])): ?>
                                <a href="<?php echo SITE_URL; ?>/<?php echo htmlspecialchars($note['file_path']); ?>" target="_blank" style="color:var(--primary);text-decoration:underline;font-size:0.8rem;">📎 Attachment</a>
                            <?php endif; ?>
                        </div>
                        <div class="note-card-actions">
                            <a class="btn btn-secondary btn-sm" href="<?php echo SITE_URL; ?>/notes-edit.php?id=<?php echo (int) $note['id']; ?>">Edit</a>
                            <button class="btn btn-secondary btn-sm" type="button" onclick="togglePinNote(<?php echo (int) $note['id']; ?>)"><?php echo $note['is_pinned'] ? 'Unpin' : 'Pin'; ?></button>
                            <button class="btn btn-secondary btn-sm" type="button" onclick="toggleArchiveNote(<?php echo (int) $note['id']; ?>)"><?php echo $note['is_archived'] ? 'Unarchive' : 'Archive'; ?></button>
                            <?php if ($filters['show_deleted']): ?>
                                <button class="btn btn-danger btn-sm" type="button" onclick="ConfirmModal.show('Delete Forever', 'Permanently delete this note?', function(){ permanentDeleteNote(<?php echo (int) $note['id']; ?>); })">Delete Forever</button>
                            <?php else: ?>
                                <button class="btn btn-danger btn-sm" type="button" onclick="ConfirmModal.show('Delete Note', 'Are you sure?', function(){ deleteNote(<?php echo (int) $note['id']; ?>); })">Delete</button>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">&#x1F4DD;</div>
                <h3>No notes yet</h3>
                <p>Create your first note to start capturing ideas.</p>
                <a href="<?php echo SITE_URL; ?>/notes-add.php" class="btn btn-primary">Add Note</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($notesData['total_pages'] > 1): ?>
        <div class="pagination">
            <?php for ($p = 1; $p <= $notesData['total_pages']; $p++): ?>
                <a class="page-link <?php echo $p === $filters['page'] ? 'active' : ''; ?>" href="<?php echo buildNotePaginationUrl($filters, $p); ?>"><?php echo (int) $p; ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
