<?php
/**
 * TaskNest - Document Vault Module
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

requireLogin($auth);

$user_id = $auth->getUserId();
$page_title = 'Documents';
$additional_css = ['documents.css'];
$additional_js = ['documents.js'];

$filters = [
    'search' => trim($_GET['search'] ?? ''),
    'category' => $_GET['category'] ?? '',
    'expiring' => isset($_GET['expiring']) ? 1 : 0,
    'expired' => isset($_GET['expired']) ? 1 : 0,
    'page' => max(1, (int) ($_GET['page'] ?? 1))
];

$categories = getDocumentCategories($mysqli, $user_id);
$docsData = getDocumentsForList($mysqli, $user_id, $filters);
$docStats = getDocumentStats($mysqli, $user_id);

include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="documents-page">
    <div class="docs-toolbar">
        <div>
            <h1 class="docs-title">Document Vault</h1>
            <p class="docs-subtitle">Securely store, organize, and manage your important documents.</p>
        </div>
        <div class="docs-toolbar-actions">
            <button class="btn btn-secondary" type="button" id="openDocCategoryModal">Manage Categories</button>
            <button class="btn btn-primary" type="button" id="openDocUploadModal">Upload Document</button>
        </div>
    </div>

    <div class="docs-summary">
        <div class="summary-card">
            <span class="summary-label">Total</span>
            <strong><?php echo (int) $docStats['total']; ?></strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Important</span>
            <strong><?php echo (int) $docStats['important']; ?></strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Expiring Soon</span>
            <strong><?php echo (int) $docStats['expiring_soon']; ?></strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Expired</span>
            <strong><?php echo (int) $docStats['expired']; ?></strong>
        </div>
    </div>

    <div class="docs-controls">
        <form class="docs-filters" id="docsFilterForm" method="get">
            <input type="text" name="search" class="form-control" placeholder="Search documents..." value="<?php echo htmlspecialchars($filters['search']); ?>">
            <select name="category" class="form-control">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo (int) $cat['id']; ?>" <?php echo (string) $filters['category'] === (string) $cat['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <label class="checkbox-inline"><input type="checkbox" name="expiring" value="1" <?php echo $filters['expiring'] ? 'checked' : ''; ?>> Expiring Soon</label>
            <label class="checkbox-inline"><input type="checkbox" name="expired" value="1" <?php echo $filters['expired'] ? 'checked' : ''; ?>> Expired</label>
            <button class="btn btn-secondary" type="submit">Apply</button>
        </form>
    </div>

    <div id="docsContainer">
        <?php if (!empty($docsData['documents'])): ?>
            <div class="docs-grid">
                <?php foreach ($docsData['documents'] as $doc):
                    $ext = strtolower(pathinfo($doc['original_name'], PATHINFO_EXTENSION));
                    $icon = '&#x1F4C4;';
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) $icon = '&#x1F5BC;';
                    elseif ($ext === 'pdf') $icon = '&#x1F4D5;';
                    elseif (in_array($ext, ['doc', 'docx'])) $icon = '&#x1F4C3;';
                    elseif (in_array($ext, ['xls', 'xlsx'])) $icon = '&#x1F4CA;';
                ?>
                    <article class="doc-card <?php echo $doc['is_important'] ? 'is-important' : ''; ?>" data-doc-id="<?php echo (int) $doc['id']; ?>">
                        <div class="doc-card-icon"><?php echo $icon; ?></div>
                        <h3><?php echo htmlspecialchars($doc['title']); ?></h3>
                        <div class="doc-desc"><?php echo htmlspecialchars($doc['description'] ?: $doc['original_name']); ?></div>
                        <div class="doc-card-meta">
                            <span><?php echo formatBytes($doc['file_size']); ?></span>
                            <?php if (!empty($doc['category_name'])): ?>
                                <span class="doc-cat-badge" style="background:<?php echo htmlspecialchars($doc['category_color'] ?? '#6366f1'); ?>20;color:<?php echo htmlspecialchars($doc['category_color'] ?? '#6366f1'); ?>"><?php echo htmlspecialchars($doc['category_name']); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($doc['expiry_date'])): ?>
                            <?php if ($doc['expiry_date'] < date('Y-m-d')): ?>
                                <div class="doc-expiry-warning">Expired: <?php echo htmlspecialchars($doc['expiry_date']); ?></div>
                            <?php elseif ($doc['expiry_date'] <= date('Y-m-d', strtotime('+30 days'))): ?>
                                <div class="doc-expiry-warning">Expiring: <?php echo htmlspecialchars($doc['expiry_date']); ?></div>
                            <?php else: ?>
                                <div class="doc-expiry-ok">Expires: <?php echo htmlspecialchars($doc['expiry_date']); ?></div>
                            <?php endif; ?>
                        <?php endif; ?>
                        <div class="doc-card-meta">
                            <span><?php echo timeAgo($doc['created_at']); ?></span>
                        </div>
                        <div class="doc-card-actions">
                            <button class="btn btn-secondary btn-sm" type="button" data-action="preview" data-id="<?php echo (int) $doc['id']; ?>">Preview</button>
                            <a class="btn btn-secondary btn-sm" href="uploads/documents/<?php echo htmlspecialchars($doc['filename']); ?>" download="<?php echo htmlspecialchars($doc['original_name']); ?>">Download</a>
                            <button class="btn btn-secondary btn-sm" type="button" data-action="edit" data-id="<?php echo (int) $doc['id']; ?>">Edit</button>
                            <button class="btn btn-danger btn-sm" type="button" data-action="delete" data-id="<?php echo (int) $doc['id']; ?>">Delete</button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">&#x1F4C1;</div>
                <h3>No documents yet</h3>
                <p>Upload your first document to keep it safe and organized.</p>
                <button class="btn btn-primary" type="button" id="emptyStateUploadDoc">Upload Document</button>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($docsData['total_pages'] > 1): ?>
        <div class="pagination">
            <?php for ($p = 1; $p <= $docsData['total_pages']; $p++): ?>
                <a class="page-link <?php echo $p === $docsData['page'] ? 'active' : ''; ?>" href="<?php echo buildDocumentPaginationUrl($filters, $p); ?>"><?php echo (int) $p; ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Upload Modal -->
<div class="modal" id="docUploadModal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="docUploadModal"></div>
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 id="docModalTitle">Upload Document</h3>
            <button class="modal-close" type="button" data-close-modal="docUploadModal">&times;</button>
        </div>
        <form id="docUploadForm" class="modal-body" enctype="multipart/form-data">
            <input type="hidden" name="document_id" id="docId">
            <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
            <div class="doc-upload-dropzone" id="docDropzone">
                <strong>Click or drag file here to upload</strong>
                <p>PDF, Images, Word, Excel, TXT &middot; Max 10MB</p>
                <input type="file" id="docFileInput" name="document" style="display:none;" accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx,.txt">
            </div>
            <div id="docFileName" style="font-size:0.85rem;color:var(--text-secondary);margin-bottom:1rem;"></div>
            <div class="form-group">
                <label for="docTitle">Title</label>
                <input type="text" id="docTitle" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="docDescription">Description</label>
                <textarea id="docDescription" name="description" class="form-control" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label for="docCategory">Category</label>
                <select id="docCategory" name="category_id" class="form-control"></select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="docExpiry">Expiry Date</label>
                    <input type="date" id="docExpiry" name="expiry_date" class="form-control">
                </div>
                <div class="form-group">
                    <label for="docReminder">Reminder Date</label>
                    <input type="date" id="docReminder" name="reminder_date" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="checkbox-inline"><input type="checkbox" name="is_important" id="docImportant" value="1"> Mark as Important</label>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-close-modal="docUploadModal">Cancel</button>
                <button class="btn btn-primary" type="submit" id="docSubmitBtn">Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal" id="docPreviewModal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="docPreviewModal"></div>
    <div class="modal-dialog modal-lg">
        <div class="modal-header">
            <h3 id="docPreviewTitle">Document Preview</h3>
            <button class="modal-close" type="button" data-close-modal="docPreviewModal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="doc-preview-container" id="docPreviewContent"></div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal" id="docCategoryModal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="docCategoryModal"></div>
    <div class="modal-dialog">
        <div class="modal-header">
            <h3>Manage Categories</h3>
            <button class="modal-close" type="button" data-close-modal="docCategoryModal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="docCategoryForm">
                <input type="hidden" name="category_id" id="docCatId">
                <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCsrfToken(); ?>">
                <div class="form-group">
                    <label for="docCatName">Name</label>
                    <input type="text" id="docCatName" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="docCatColor">Color</label>
                    <input type="color" id="docCatColor" name="color" class="form-control" value="#6366f1">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-close-modal="docCategoryModal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Save Category</button>
                </div>
            </form>
            <div class="doc-category-list" id="docCatList"></div>
        </div>
    </div>
</div>

<!-- Confirm Modal -->
<div class="modal" id="docConfirmModal" aria-hidden="true">
    <div class="modal-backdrop" data-close-modal="docConfirmModal"></div>
    <div class="modal-dialog modal-sm">
        <div class="modal-header">
            <h3 id="docConfirmTitle">Confirm</h3>
            <button class="modal-close" type="button" data-close-modal="docConfirmModal">&times;</button>
        </div>
        <div class="modal-body">
            <p id="docConfirmBody">Are you sure?</p>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-close-modal="docConfirmModal">Cancel</button>
                <button class="btn btn-danger" type="button" id="docConfirmActionBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
