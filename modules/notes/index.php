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
            <button class="btn btn-secondary" type="button" id="openNoteCategoryModal">Manage Categories</button>
            <button class="btn btn-primary" type="button" id="openNoteModal">Add Note</button>
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
                        </div>
                        <div class="note-card-actions">
                            <button class="btn btn-secondary btn-sm" type="button" data-action="view" data-id="<?php echo (int) $note['id']; ?>">View</button>
                            <button class="btn btn-secondary btn-sm" type="button" data-action="edit" data-id="<?php echo (int) $note['id']; ?>">Edit</button>
                            <button class="btn btn-secondary btn-sm" type="button" data-action="pin" data-id="<?php echo (int) $note['id']; ?>"><?php echo $note['is_pinned'] ? 'Unpin' : 'Pin'; ?></button>
                            <button class="btn btn-secondary btn-sm" type="button" data-action="archive" data-id="<?php echo (int) $note['id']; ?>"><?php echo $note['is_archived'] ? 'Unarchive' : 'Archive'; ?></button>
                            <?php if ($filters['show_deleted']): ?>
                                <button class="btn btn-secondary btn-sm" type="button" data-action="restore" data-id="<?php echo (int) $note['id']; ?>">Restore</button>
                                <button class="btn btn-danger btn-sm" type="button" data-action="permanent_delete" data-id="<?php echo (int) $note['id']; ?>">Delete Forever</button>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-sm" type="button" data-action="duplicate" data-id="<?php echo (int) $note['id']; ?>">Duplicate</button>
                                <button class="btn btn-danger btn-sm" type="button" data-action="delete" data-id="<?php echo (int) $note['id']; ?>">Delete</button>
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
                <button class="btn btn-primary" type="button" id="emptyStateAddNote">Add Note</button>
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

<!-- Note Modal -->
<div class="modal" id="noteModal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="noteModal"></div>
    <div class="modal-dialog modal-lg">
        <div class="modal-header">
            <h3 id="noteModalTitle">Add Note</h3>
            <button class="modal-close" type="button" data-close-modal="noteModal">&times;</button>
        </div>
        <form id="noteForm" class="modal-body note-modal-body">
            <input type="hidden" name="note_id" id="noteId">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <div class="form-group">
                <label for="noteTitle">Title</label>
                <input type="text" id="noteTitle" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="noteCategory">Category</label>
                <select id="noteCategory" name="category_id" class="form-control"></select>
            </div>
            <div class="form-group">
                <label>Content</label>
                <div class="rich-text-toolbar" id="richTextToolbar">
                    <button type="button" data-cmd="bold" title="Bold"><strong>B</strong></button>
                    <button type="button" data-cmd="italic" title="Italic"><em>I</em></button>
                    <button type="button" data-cmd="underline" title="Underline"><u>U</u></button>
                    <button type="button" data-cmd="insertUnorderedList" title="Bullet List">&#8226; List</button>
                    <button type="button" data-cmd="insertOrderedList" title="Numbered List">1. List</button>
                    <button type="button" data-cmd="formatBlock" data-val="h2" title="Heading">H2</button>
                    <button type="button" data-cmd="formatBlock" data-val="h3" title="Subheading">H3</button>
                    <button type="button" data-cmd="formatBlock" data-val="p" title="Paragraph">P</button>
                </div>
                <div class="note-editor" id="noteEditor" contenteditable="true"></div>
                <input type="hidden" name="content" id="noteContent">
            </div>
            <div class="form-group">
                <label>Images</label>
                <input type="file" id="noteImageInput" accept="image/*" multiple style="display:none;">
                <button type="button" class="btn btn-secondary btn-sm" id="addNoteImageBtn">Upload Image</button>
                <div class="note-images-preview" id="noteImagesPreview"></div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="checkbox-inline"><input type="checkbox" name="is_pinned" id="noteIsPinned" value="1"> Pin this note</label>
                </div>
                <div class="form-group">
                    <label class="checkbox-inline"><input type="checkbox" name="is_archived" id="noteIsArchived" value="1"> Archive this note</label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-close-modal="noteModal">Cancel</button>
                <button class="btn btn-primary" type="submit">Save Note</button>
            </div>
        </form>
    </div>
</div>

<!-- View Note Modal -->
<div class="modal" id="noteViewModal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="noteViewModal"></div>
    <div class="modal-dialog modal-lg">
        <div class="modal-header">
            <h3 id="noteViewTitle">View Note</h3>
            <button class="modal-close" type="button" data-close-modal="noteViewModal">&times;</button>
        </div>
        <div class="modal-body note-modal-body">
            <div id="noteViewContent" class="note-view-content"></div>
            <div id="noteViewImages" class="note-images-preview" style="margin-top:1rem;"></div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal" id="noteCategoryModal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="noteCategoryModal"></div>
    <div class="modal-dialog">
        <div class="modal-header">
            <h3>Manage Categories</h3>
            <button class="modal-close" type="button" data-close-modal="noteCategoryModal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="noteCategoryForm">
                <input type="hidden" name="category_id" id="noteCategoryId">
                <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
                <div class="form-group">
                    <label for="noteCategoryName">Name</label>
                    <input type="text" id="noteCategoryName" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="noteCategoryColor">Color</label>
                    <input type="color" id="noteCategoryColor" name="color" class="form-control" value="#6366f1">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-close-modal="noteCategoryModal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Save Category</button>
                </div>
            </form>
            <div class="note-category-list" id="noteCategoryList"></div>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal" id="noteConfirmModal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="noteConfirmModal"></div>
    <div class="modal-dialog modal-sm">
        <div class="modal-header">
            <h3 id="noteConfirmTitle">Confirm</h3>
            <button class="modal-close" type="button" data-close-modal="noteConfirmModal">&times;</button>
        </div>
        <div class="modal-body">
            <p id="noteConfirmBody">Are you sure?</p>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-close-modal="noteConfirmModal">Cancel</button>
                <button class="btn btn-danger" type="button" id="noteConfirmActionBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.noteNestInitialFilters = <?php echo json_encode($filters, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
</script>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
