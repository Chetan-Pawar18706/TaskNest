<?php
/**
 * TaskNest - Footer Layout
 */
?>
<?php if ($auth->isLoggedIn()) { ?>
            </div><!-- .page-content -->
        </main><!-- .main-content -->
    </div><!-- .app-layout -->
<?php } else { ?>
    </div><!-- .guest-layout -->
<?php } ?>
    
    <!-- CSRF Token & Config -->
    <script>
        const csrfToken = '<?php echo $auth->generateCsrfToken(); ?>';
        const siteUrl = '<?php echo SITE_URL; ?>';
        const debugMode = <?php echo DEBUG ? 'true' : 'false'; ?>;
    </script>

    <!-- Global Confirm Modal -->
    <div class="modal" id="confirmModal" aria-hidden="true">
        <div class="modal-backdrop" data-close-modal="confirmModal"></div>
        <div class="modal-dialog modal-sm">
            <div class="modal-header">
                <h3 id="confirmModalTitle">Confirm Delete</h3>
                <button class="modal-close" type="button" data-close-modal="confirmModal">&times;</button>
            </div>
            <div class="modal-body">
                <p id="confirmModalBody">Are you sure you want to delete this item?</p>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-close-modal="confirmModal">Cancel</button>
                    <button class="btn btn-danger" type="button" id="confirmActionBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    window.ConfirmModal = {
        _callback: null,
        show: function(title, message, callback, btnText) {
            document.getElementById('confirmModalTitle').textContent = title || 'Confirm';
            document.getElementById('confirmModalBody').textContent = message || 'Are you sure?';
            document.getElementById('confirmActionBtn').textContent = btnText || 'Delete';
            this._callback = callback;
            document.getElementById('confirmModal').classList.add('active');
        },
        hide: function() {
            document.getElementById('confirmModal').classList.remove('active');
            this._callback = null;
        }
    };
    document.getElementById('confirmActionBtn').addEventListener('click', function() {
        if (window.ConfirmModal._callback) window.ConfirmModal._callback();
        window.ConfirmModal.hide();
    });
    document.querySelectorAll('#confirmModal [data-close-modal]').forEach(function(el) {
        el.addEventListener('click', function() { window.ConfirmModal.hide(); });
    });
    document.getElementById('confirmModal').querySelector('.modal-backdrop').addEventListener('click', function() {
        window.ConfirmModal.hide();
    });
    </script>

    <!-- Core JS -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/theme.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/toast.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/modal.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/sidebar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <?php if (isset($additional_js)) { foreach ($additional_js as $js) { ?>
    <script src="<?php echo SITE_URL; ?>/assets/js/<?php echo $js; ?>"></script>
    <?php } } ?>
</body>
</html>
