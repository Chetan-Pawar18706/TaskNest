<?php
/**
 * TaskNest - Header Layout
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo SITE_DESCRIPTION; ?>">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/assets/images/logo-dark.png">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/variables.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/reset.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/styles.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/components.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/theme.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/responsive.css">
    
    <?php if (isset($additional_css)) { foreach ($additional_css as $css) { ?>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/<?php echo $css; ?>">
    <?php } } ?>
</head>
<body data-theme="<?php echo $auth->isLoggedIn() ? ($auth->getUser()['theme'] ?? 'light') : 'light'; ?>">
    <!-- Toast Notifications -->
    <div id="toastContainer" class="toast-container"></div>
    
    <!-- Modal Base -->
    <div id="modalContainer" class="modal-container"></div>
    
    <?php if ($auth->isLoggedIn()) { ?>
    <!-- Main Layout with Sidebar -->
    <div class="app-layout">
        <?php include __DIR__ . '/sidebar.php'; ?>
        <main class="main-content">
            <?php include __DIR__ . '/navbar.php'; ?>
            <div class="page-content">
    <?php } else { ?>
    <!-- Guest Layout -->
    <div class="guest-layout">
    <?php } ?>
