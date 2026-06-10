<?php
require_once __DIR__ . '/includes/init.php';
require_role(['admin', 'superadmin']);

$pageTitle = 'Admin Dashboard';
$counts = [
    'users' => db()->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'reports' => db()->query('SELECT COUNT(*) FROM reports WHERE status IN ("open", "reviewing")')->fetchColumn(),
    'seller_requests' => db()->query('SELECT COUNT(*) FROM seller_requests WHERE status = "pending"')->fetchColumn(),
    'orders' => db()->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
];
$categoryCounts = db()->query(
    'SELECT c.name, COUNT(l.id) AS total
     FROM categories c
     LEFT JOIN listings l ON l.category_id = c.id
     GROUP BY c.id, c.name
     ORDER BY c.name'
)->fetchAll();
$roleCounts = db()->query('SELECT role AS name, COUNT(*) AS total FROM users GROUP BY role ORDER BY role')->fetchAll();
$orderStatusCounts = db()->query('SELECT status AS name, COUNT(*) AS total FROM orders GROUP BY status ORDER BY status')->fetchAll();
$maxCategory = max(1, ...array_map(fn($row) => (int) $row['total'], $categoryCounts));
$maxRole = max(1, ...array_map(fn($row) => (int) $row['total'], $roleCounts));
$maxOrderStatus = max(1, ...array_map(fn($row) => (int) $row['total'], $orderStatusCounts ?: [['total' => 0]]));

require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact">
    <p class="eyebrow">Organization admin website</p>
    <h1>TradeCircle admin</h1>
</section>
<section class="section-shell dashboard-grid">
    <a class="dashboard-tile" href="<?= e(url_for('admin_users.php')) ?>"><strong><?= (int) $counts['users'] ?></strong><span>Users and RBAC</span></a>
    <a class="dashboard-tile" href="<?= e(url_for('admin_reports.php')) ?>"><strong><?= (int) $counts['reports'] ?></strong><span>Open reports</span></a>
    <a class="dashboard-tile" href="<?= e(url_for('admin_seller_requests.php')) ?>"><strong><?= (int) $counts['seller_requests'] ?></strong><span>Seller requests</span></a>
    <a class="dashboard-tile" href="<?= e(url_for('admin_orders.php')) ?>"><strong><?= (int) $counts['orders'] ?></strong><span>Orders</span></a>
    <a class="dashboard-tile" href="<?= e(url_for('admin_listings.php')) ?>"><strong>Listings</strong><span>Moderate marketplace stock</span></a>
</section>

<section class="section-shell">
    <div class="section-heading">
        <div>
            <p class="eyebrow">Admin analytics</p>
            <h2>Platform activity</h2>
        </div>
    </div>
    <div class="chart-grid">
        <article class="chart-card">
            <h3>Listings by category</h3>
            <?php foreach ($categoryCounts as $row): ?>
                <?php $width = round(((int) $row['total'] / $maxCategory) * 100); ?>
                <div class="bar-row">
                    <span><?= e($row['name']) ?></span>
                    <span class="bar-track"><span class="bar-fill" style="width: <?= $width ?>%"></span></span>
                    <strong><?= (int) $row['total'] ?></strong>
                </div>
            <?php endforeach; ?>
        </article>
        <article class="chart-card">
            <h3>Users by role</h3>
            <?php foreach ($roleCounts as $row): ?>
                <?php $width = round(((int) $row['total'] / $maxRole) * 100); ?>
                <div class="bar-row">
                    <span><?= e(role_label($row['name'])) ?></span>
                    <span class="bar-track"><span class="bar-fill" style="width: <?= $width ?>%"></span></span>
                    <strong><?= (int) $row['total'] ?></strong>
                </div>
            <?php endforeach; ?>
        </article>
        <article class="chart-card">
            <h3>Orders by status</h3>
            <?php if (!$orderStatusCounts): ?><p class="form-note">No orders yet.</p><?php endif; ?>
            <?php foreach ($orderStatusCounts as $row): ?>
                <?php $width = round(((int) $row['total'] / $maxOrderStatus) * 100); ?>
                <div class="bar-row">
                    <span><?= e(status_label($row['name'])) ?></span>
                    <span class="bar-track"><span class="bar-fill" style="width: <?= $width ?>%"></span></span>
                    <strong><?= (int) $row['total'] ?></strong>
                </div>
            <?php endforeach; ?>
        </article>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
