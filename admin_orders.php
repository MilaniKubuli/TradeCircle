<?php
require_once __DIR__ . '/includes/init.php';
require_role(['admin', 'superadmin']);

$pageTitle = 'Admin Orders';
$orders = db()->query(
    'SELECT o.*, COALESCE(u.full_name, o.buyer_name_snapshot, "Deleted user") AS buyer_name,
            COALESCE(u.email, o.buyer_email_snapshot, "Deleted user") AS buyer_email
     FROM orders o
     LEFT JOIN users u ON u.id = o.buyer_id
     ORDER BY o.id DESC'
)->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact">
    <p class="eyebrow">Payment and checkout oversight</p>
    <h1>Orders</h1>
</section>
<section class="section-shell">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Order</th><th>Buyer</th><th>Total</th><th>Status</th><th>Payment ref</th></tr></thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?= (int) $order['id'] ?></td>
                    <td><?= e($order['buyer_name']) ?><br><small><?= e($order['buyer_email']) ?></small></td>
                    <td><?= e(money($order['total_amount'])) ?></td>
                    <td><span class="pill"><?= e(status_label($order['status'])) ?></span></td>
                    <td><?= e($order['payment_reference'] ?: 'Pending') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
