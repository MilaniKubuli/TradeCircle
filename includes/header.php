<?php
declare(strict_types=1);

$pageTitle = $pageTitle ?? APP_NAME;
$user = current_user();
$cartCount = get_cart_count();
$crumbs = breadcrumbs($pageTitle);
$logoFile = __DIR__ . '/../assets/images/logo.png';
$hasLogo = is_file($logoFile) && filesize($logoFile) > 0;
$flashMessages = pull_flash();
?>
<!doctype html>
<html lang="en" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(url_for('assets/css/styles.css')) ?>">
    <script src="<?= e(url_for('assets/js/app.js')) ?>" defer></script>
</head>
<body class="page-<?= e(str_replace(['.php', '_'], ['', '-'], page_file())) ?>">
<header class="site-header">
    <nav class="nav-shell" aria-label="Main navigation">
        <a class="brand" href="<?= e(url_for('index.php')) ?>">
            <span class="brand-mark <?= $hasLogo ? '' : 'brand-fallback' ?>">
                <?php if ($hasLogo): ?>
                    <img src="<?= e(url_for('assets/images/logo.png')) ?>" alt="<?= e(APP_NAME) ?> logo">
                <?php endif; ?>
            </span>
            <span>
                <strong><?= e(APP_NAME) ?></strong>
            </span>
        </a>
        <button class="nav-toggle" type="button" data-nav-toggle aria-label="Open navigation"><span></span><span></span><span></span></button>
        <div class="nav-links" data-nav-menu>
            <?= nav_link('index.php', 'Home', 'home') ?>
            <?= nav_link('listings.php', 'Browse', 'browse') ?>
            <?= nav_link('about.php', 'About', 'about') ?>
            <?= nav_link('cart.php', 'Cart' . ($cartCount ? ' (' . $cartCount . ')' : ''), 'cart') ?>
            <?php if ($user): ?>
                <div class="mobile-profile-summary">
                    <?= nav_icon('profile') ?>
                    <span>
                        <strong><?= e($user['full_name']) ?></strong>
                        <small><?= e(role_label($user['role'])) ?></small>
                    </span>
                </div>
                <?php if ($user['role'] === 'seller'): ?>
                    <?= nav_link('seller_dashboard.php', 'Seller Hub', 'seller') ?>
                <?php endif; ?>
                <div class="profile-menu">
                    <button class="profile-trigger" type="button" data-profile-toggle aria-expanded="false">
                        <?= nav_icon('profile') ?>
                        <span><?= e(initials($user['full_name'])) ?></span>
                    </button>
                    <div class="profile-dropdown" data-profile-menu>
                        <strong><?= e($user['full_name']) ?></strong>
                        <small><?= e(role_label($user['role'])) ?></small>
                        <a href="<?= e(url_for('dashboard.php')) ?>">Dashboard</a>
                        <a href="<?= e(url_for('profile.php')) ?>">Profile</a>
                        <?php if ($user['role'] === 'seller'): ?>
                            <a href="<?= e(url_for('seller_dashboard.php')) ?>">Seller Hub</a>
                            <a href="<?= e(url_for('seller_listing_form.php')) ?>">Create Listing</a>
                        <?php endif; ?>
                        <?php if (is_admin()): ?>
                            <a href="<?= e(url_for('admin_dashboard.php')) ?>">Admin Dashboard</a>
                            <a href="<?= e(url_for('admin_users.php')) ?>">Manage Users</a>
                            <a href="<?= e(url_for('admin_reports.php')) ?>">Reports</a>
                            <a href="<?= e(url_for('admin_seller_requests.php')) ?>">Seller Requests</a>
                            <a href="<?= e(url_for('admin_listings.php')) ?>">Listings</a>
                            <a href="<?= e(url_for('admin_orders.php')) ?>">Orders</a>
                        <?php endif; ?>
                        <div class="profile-footer-actions">
                            <button class="theme-toggle" type="button" data-theme-toggle aria-label="Toggle colour theme">
                                <span data-theme-icon="sun"><?= nav_icon('sun') ?></span>
                                <span data-theme-icon="moon"><?= nav_icon('moon') ?></span>
                            </button>
                            <a class="logout-link" href="<?= e(url_for('logout.php')) ?>">Logout</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="mobile-profile-summary">
                    <?= nav_icon('profile') ?>
                    <strong>Visitor</strong>
                </div>
                <button class="theme-toggle" type="button" data-theme-toggle aria-label="Toggle colour theme">
                    <span data-theme-icon="sun"><?= nav_icon('sun') ?></span>
                    <span data-theme-icon="moon"><?= nav_icon('moon') ?></span>
                </button>
                <?= nav_link('login.php', 'Login', 'profile') ?>
                <a class="button button-primary" href="<?= e(url_for('register.php')) ?>">Register</a>
            <?php endif; ?>
        </div>
        <button class="nav-backdrop" type="button" data-nav-backdrop aria-label="Close navigation"></button>
    </nav>
</header>
<main>
    <?php if ($flashMessages): ?>
        <div class="flash-wrap" aria-live="polite">
            <?php foreach ($flashMessages as $message): ?>
                <div class="flash flash-<?= e($message['type']) ?>"><?= e($message['message']) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if (!in_array(page_file(), ['index.php', ''], true)): ?>
        <nav class="breadcrumb-shell" aria-label="Breadcrumb">
            <button class="back-button" type="button" data-back-button data-home-url="<?= e(url_for('index.php')) ?>"><?= nav_icon('back') ?><span>Back</span></button>
            <ol class="breadcrumbs">
                <?php foreach ($crumbs as $crumb): ?>
                    <li>
                        <?php if ($crumb['url']): ?>
                            <a href="<?= e($crumb['url']) ?>"><?= e($crumb['label']) ?></a>
                        <?php else: ?>
                            <span><?= e($crumb['label']) ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </nav>
    <?php endif; ?>
