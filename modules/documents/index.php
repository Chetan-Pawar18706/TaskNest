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
            <a class="btn btn-secondary" href="<?php echo SITE_URL; ?>/documents-categories.php">Manage Categories</a>
            <a class="btn btn-primary" href="<?php echo SITE_URL; ?>/documents-upload.php">Upload Document</a>
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
                            <a class="btn btn-secondary btn-sm" href="<?php echo SITE_URL; ?>/uploads/documents/<?php echo htmlspecialchars($doc['filename']); ?>" target="_blank">Preview</a>
                            <a class="btn btn-secondary btn-sm" href="<?php echo SITE_URL; ?>/uploads/documents/<?php echo htmlspecialchars($doc['filename']); ?>" download="<?php echo htmlspecialchars($doc['original_name']); ?>">Download</a>
                            <button class="btn btn-secondary btn-sm" type="button" onclick="window.location.href='<?php echo SITE_URL; ?>/documents-edit.php?id=<?php echo (int) $doc['id']; ?>'">Edit</button>
                            <button class="btn btn-danger btn-sm" type="button" onclick="ConfirmModal.show('Delete Document', 'Are you sure you want to delete this document?', function(){ deleteDocument(<?php echo (int) $doc['id']; ?>); })">Delete</button>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">&#x1F4C1;</div>
                <h3>No documents yet</h3>
                <p>Upload your first document to keep it safe and organized.</p>
                <a class="btn btn-primary" href="<?php echo SITE_URL; ?>/documents-upload.php">Upload Document</a>
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



<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
