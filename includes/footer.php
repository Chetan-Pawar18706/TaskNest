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
    
    <!-- JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/theme.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/toast.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/modal.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/sidebar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/dashboard.js"></script>
    
    <?php if (isset($additional_js)) { foreach ($additional_js as $js) { ?>
    <script src="<?php echo SITE_URL; ?>/assets/js/<?php echo $js; ?>"></script>
    <?php } } ?>
    
    <!-- CSRF Token as data attribute -->
    <script>
        const csrfToken = '<?php echo $auth->generateCsrfToken(); ?>';
        const siteUrl = '<?php echo SITE_URL; ?>';
        const debugMode = <?php echo DEBUG ? 'true' : 'false'; ?>;
    </script>
</body>
</html>
