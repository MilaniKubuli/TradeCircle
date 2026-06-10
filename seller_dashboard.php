<?php
require_once __DIR__ . '/includes/init.php';
require_role('seller');

$user = current_user();
$pageTitle = 'Seller Hub';

$statsStmt = db()->prepare(
    'SELECT
        COUNT(*) AS total_listings,
        SUM(status = "available") AS active_listings,
        SUM(status = "sold") AS sold_listings,
        SUM(status = "pending") AS pending_listings
     FROM listings WHERE seller_id = ?'
);
$statsStmt->execute([$user['id']]);
$stats = $statsStmt->fetch();

$paidOrdersStmt = db()->prepare(
    'SELECT COUNT(*) FROM order_items oi
     JOIN orders o ON o.id = oi.order_id
     WHERE oi.seller_id = ? AND o.status = ?'
);
$paidOrdersStmt->execute([$user['id'], 'paid']);
$paidOrderItems = (int) $paidOrdersStmt->fetchColumn();

$listingsStmt = db()->prepare(
    'SELECT l.*, c.name AS category_name
     FROM listings l
     JOIN categories c ON c.id = l.category_id
     WHERE l.seller_id = ?
     ORDER BY l.id DESC'
);
$listingsStmt->execute([$user['id']]);
$listings = $listingsStmt->fetchAll();

$ordersStmt = db()->prepare(
    'SELECT oi.*, o.id AS order_id, o.status AS order_status,
            COALESCE(b.full_name, o.buyer_name_snapshot, "Deleted user") AS buyer_name,
            COALESCE(b.email, o.buyer_email_snapshot, "Deleted user") AS buyer_email,
            COALESCE(l.title, oi.listing_title_snapshot, "Deleted listing") AS title
     FROM order_items oi
     JOIN orders o ON o.id = oi.order_id
     LEFT JOIN users b ON b.id = o.buyer_id
     LEFT JOIN listings l ON l.id = oi.listing_id
     WHERE oi.seller_id = ?
     ORDER BY o.id DESC
     LIMIT 10'
);
$ordersStmt->execute([$user['id']]);
$sellerOrders = $ordersStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact">
    <p class="eyebrow">Seller tools</p>
    <h1>Seller hub</h1>
    <p>Create listings, track buyer order interest, and manage listing status.</p>
</section>

<section class="section-shell dashboard-grid">
    <div class="dashboard-tile"><strong><?= (int) $stats['total_listings'] ?></strong><span>Total listings</span></div>
    <div class="dashboard-tile"><strong><?= (int) $stats['active_listings'] ?></strong><span>Available</span></div>
    <div class="dashboard-tile"><strong><?= (int) $stats['pending_listings'] ?></strong><span>Pending</span></div>
    <div class="dashboard-tile"><strong><?= (int) $stats['sold_listings'] ?></strong><span>Marked sold</span></div>
    <div class="dashboard-tile"><strong><?= $paidOrderItems ?></strong><span>Paid order items</span></div>
</section>

<section class="section-shell">
    <div class="section-heading">
        <h2>Your listings</h2>
        <div class="action-row">
            <a class="button button-primary" href="<?= e(url_for('seller_listing_form.php')) ?>">Create listing</a>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Listing</th><th>Category</th><th>Price</th><th>Qty</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($listings as $listing): ?>
                <tr>
                    <td><?= e($listing['title']) ?></td>
                    <td><?= e($listing['category_name']) ?></td>
                    <td><?= e(money($listing['price'])) ?></td>
                    <td><?= (int) $listing['quantity'] ?></td>
                    <td><span class="pill"><?= e(status_label($listing['status'])) ?></span></td>
                    <td><a href="<?= e(url_for('seller_listing_form.php?id=' . (int) $listing['id'])) ?>">Edit</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="section-shell">
    <div class="section-heading"><h2>Buyer order activity</h2></div>
    <?php if (!$sellerOrders): ?>
        <div class="empty-state"><p>No buyer orders yet.</p></div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Order</th><th>Item</th><th>Buyer</th><th>Qty</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($sellerOrders as $orderItem): ?>
                    <tr>
                        <td>#<?= (int) $orderItem['order_id'] ?></td>
                        <td><?= e($orderItem['title']) ?></td>
                        <td><?= e($orderItem['buyer_name']) ?><br><small><?= e($orderItem['buyer_email']) ?></small></td>
                        <td><?= (int) $orderItem['quantity'] ?></td>
                        <td><span class="pill"><?= e(status_label($orderItem['order_status'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
