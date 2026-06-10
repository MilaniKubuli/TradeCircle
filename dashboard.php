<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$user = current_user();
$pageTitle = 'Dashboard';

$orders = [];
if ($user['role'] === 'buyer' || $user['role'] === 'seller') {
    $stmt = db()->prepare('SELECT * FROM orders WHERE buyer_id = ? ORDER BY id DESC LIMIT 5');
    $stmt->execute([$user['id']]);
    $orders = $stmt->fetchAll();
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact">
    <p class="eyebrow"><?= e(role_label($user['role'])) ?> dashboard</p>
    <h1>Hello, <?= e($user['full_name']) ?>.</h1>
    <p>Manage your TradeCircle activity from one place.</p>
</section>

<section class="section-shell dashboard-grid">
    <a class="dashboard-tile" href="<?= e(url_for('cart.php')) ?>">
        <strong>Cart</strong>
        <span><?= get_cart_count() ?> item(s) waiting</span>
    </a>
    <a class="dashboard-tile" href="<?= e(url_for('profile.php')) ?>">
        <strong>Profile</strong>
        <span>Update contact and location details</span>
    </a>
    <?php if ($user['role'] === 'buyer'): ?>
        <a class="dashboard-tile" href="<?= e(url_for('seller_request.php')) ?>">
            <strong>Seller request</strong>
            <span>Ask admin to upgrade your buyer account</span>
        </a>
    <?php endif; ?>
    <?php if ($user['role'] === 'seller'): ?>
        <a class="dashboard-tile" href="<?= e(url_for('seller_dashboard.php')) ?>">
            <strong>Seller hub</strong>
            <span>Create and manage your listings</span>
        </a>
    <?php endif; ?>
    <?php if (is_admin()): ?>
        <a class="dashboard-tile" href="<?= e(url_for('admin_dashboard.php')) ?>">
            <strong>Admin site</strong>
            <span>RBAC, reports, seller requests, and platform management</span>
        </a>
    <?php endif; ?>
</section>

<?php if ($orders): ?>
<section class="section-shell">
    <div class="section-heading">
        <h2>Recent orders</h2>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Order</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?= (int) $order['id'] ?></td>
                    <td><?= e(money($order['total_amount'])) ?></td>
                    <td><span class="pill"><?= e(status_label($order['status'])) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
