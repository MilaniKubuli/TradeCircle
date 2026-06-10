<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/payfast.php';
require_login();

$user = current_user();
$pageTitle = 'Checkout';
$order = null;
$orderItems = [];
$savedAddressParts = array_filter([
    $user['address_line1'] ?? '',
    $user['address_line2'] ?? '',
    $user['suburb'] ?? '',
    $user['city'] ?? '',
    $user['province'] ?? '',
    $user['zip_code'] ?? '',
], static fn ($part) => trim((string) $part) !== '');
$savedDeliveryAddress = implode(', ', $savedAddressParts);

if (isset($_GET['order_id'])) {
    $stmt = db()->prepare('SELECT * FROM orders WHERE id = ? AND buyer_id = ?');
    $stmt->execute([(int) $_GET['order_id'], $user['id']]);
    $order = $stmt->fetch();
    if ($order) {
        $itemsStmt = db()->prepare(
            'SELECT oi.*,
                    COALESCE(l.title, oi.listing_title_snapshot, "Deleted listing") AS title,
                    COALESCE(u.full_name, oi.seller_name_snapshot, "Deleted user") AS seller_name
             FROM order_items oi
             LEFT JOIN listings l ON l.id = oi.listing_id
             LEFT JOIN users u ON u.id = oi.seller_id
             WHERE oi.order_id = ?'
        );
        $itemsStmt->execute([$order['id']]);
        $orderItems = $itemsStmt->fetchAll();
    }
}

if (is_post()) {
    verify_csrf();
    $items = cart_items();
    if (!$items) {
        flash('error', 'Your cart is empty.');
        redirect('cart.php');
    }

    $deliveryMethod = ($_POST['delivery_method'] ?? '') === 'local_delivery' ? 'local_delivery' : 'pickup';
    $deliveryAddress = trim((string) ($_POST['delivery_address'] ?? ''));
    if ($deliveryMethod === 'local_delivery' && $deliveryAddress === '') {
        flash('error', 'Delivery address is required when local delivery is selected.');
        redirect('checkout.php');
    }

    $total = cart_total($items);
    $pdo = db();
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('INSERT INTO orders (buyer_id, total_amount, delivery_method, delivery_address, buyer_notes) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([
        $user['id'],
        $total,
        $deliveryMethod,
        $deliveryMethod === 'local_delivery' ? $deliveryAddress : null,
        trim((string) ($_POST['buyer_notes'] ?? '')),
    ]);
    $orderId = (int) $pdo->lastInsertId();

    $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, listing_id, seller_id, quantity, unit_price) VALUES (?, ?, ?, ?, ?)');
    foreach ($items as $item) {
        $itemStmt->execute([
            $orderId,
            (int) $item['id'],
            (int) $item['seller_id'],
            (int) $item['cart_quantity'],
            (float) $item['price'],
        ]);
    }
    $pdo->commit();
    clear_cart();
    flash('success', 'Order created. Complete the sandbox payment.');
    redirect('checkout.php?order_id=' . $orderId);
}

$items = cart_items();
$total = cart_total($items);
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact">
    <p class="eyebrow">Secure local checkout</p>
    <h1>Checkout</h1>
</section>

<section class="section-shell">
<?php if ($order): ?>
    <div class="checkout-result">
        <div class="info-panel">
            <h2>Order #<?= (int) $order['id'] ?></h2>
            <p>Status: <span class="pill"><?= e(status_label($order['status'])) ?></span></p>
            <p>Total: <strong><?= e(money($order['total_amount'])) ?></strong></p>
            <ul class="plain-list">
                <?php foreach ($orderItems as $item): ?>
                    <li><?= e($item['title']) ?> x <?= (int) $item['quantity'] ?> from <?= e($item['seller_name']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="form-card">
            <h2>Payment options</h2>
            <p>Use PayFast Sandbox to complete the hosted test payment.</p>
            <div class="payment-actions">
                <?= payfast_form($order, $user) ?>
                <a class="button button-ghost" href="<?= e(url_for('payment_cancel.php?order_id=' . (int) $order['id'])) ?>">Cancel order</a>
            </div>
        </div>
    </div>
<?php elseif (!$items): ?>
    <div class="empty-state">
        <h2>No checkout items</h2>
        <p>Your cart is empty or the order could not be found.</p>
        <a class="button button-primary" href="<?= e(url_for('listings.php')) ?>">Browse listings</a>
    </div>
<?php else: ?>
    <div class="cart-layout">
        <form class="form-card" method="post">
            <?= csrf_field() ?>
            <h2>Delivery details</h2>
            <fieldset class="segmented">
                <legend>Delivery method</legend>
                <label><input type="radio" name="delivery_method" value="pickup" checked data-delivery-method> Pickup / meet-up</label>
                <label><input type="radio" name="delivery_method" value="local_delivery" data-delivery-method> Local delivery</label>
            </fieldset>
            <label data-delivery-address-row>Delivery address <input type="text" name="delivery_address" value="<?= e($savedDeliveryAddress ?: ($user['location'] ?? '')) ?>" data-delivery-address-input></label>
            <label>Notes for seller(s) <textarea name="buyer_notes" rows="4" placeholder="Preferred time, landmark, delivery note..."></textarea></label>
            <button class="button button-primary" type="submit">Create order</button>
        </form>
        <aside class="summary-panel">
            <h2>Checkout summary</h2>
            <?php foreach ($items as $item): ?>
                <div class="summary-row"><span><?= e($item['title']) ?> x <?= (int) $item['cart_quantity'] ?></span><strong><?= e(money((float) $item['price'] * (int) $item['cart_quantity'])) ?></strong></div>
            <?php endforeach; ?>
            <div class="summary-row total"><span>Total</span><strong><?= e(money($total)) ?></strong></div>
        </aside>
    </div>
<?php endif; ?>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
