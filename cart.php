<?php
require_once __DIR__ . '/includes/init.php';

if (is_post()) {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    $listingId = (int) ($_POST['listing_id'] ?? 0);
    if ($action === 'add') {
        add_to_cart($listingId, (int) ($_POST['quantity'] ?? 1));
    } elseif ($action === 'update') {
        update_cart_quantity($listingId, (int) ($_POST['quantity'] ?? 0));
        flash('success', 'Cart updated.');
    } elseif ($action === 'remove') {
        update_cart_quantity($listingId, 0);
        flash('success', 'Item removed from cart.');
    }
    redirect('cart.php');
}

$items = cart_items();
$total = cart_total($items);
$pageTitle = 'Cart';
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact">
    <p class="eyebrow">Checkout starts here</p>
    <h1>Your cart</h1>
    <p>Review selected items before continuing to local checkout.</p>
</section>
<section class="section-shell">
    <?php if (!$items): ?>
        <div class="empty-state">
            <h2>Your cart is empty</h2>
            <p>Browse available listings and add items from local sellers.</p>
            <a class="button button-primary" href="<?= e(url_for('listings.php')) ?>">Browse listings</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">
            <div class="table-wrap">
                <table class="cart-table">
                    <thead><tr><th>Item</th><th>Seller</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><a href="<?= e(url_for('listing.php?id=' . (int) $item['id'])) ?>"><?= e($item['title']) ?></a></td>
                            <td><?= e($item['seller_name']) ?></td>
                            <td><?= e(money($item['price'])) ?></td>
                            <td>
                                <form class="qty-form" method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="listing_id" value="<?= (int) $item['id'] ?>">
                                    <input type="number" name="quantity" value="<?= (int) $item['cart_quantity'] ?>" min="1" max="<?= (int) $item['quantity'] ?>">
                                    <button type="submit">Update</button>
                                </form>
                            </td>
                            <td><?= e(money((float) $item['price'] * (int) $item['cart_quantity'])) ?></td>
                            <td>
                                <form method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="listing_id" value="<?= (int) $item['id'] ?>">
                                    <button class="link-button" type="submit">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <aside class="summary-panel">
                <h2>Order summary</h2>
                <div class="summary-row"><span>Items</span><strong><?= get_cart_count() ?></strong></div>
                <div class="summary-row"><span>Total</span><strong><?= e(money($total)) ?></strong></div>
                <?php if (is_logged_in()): ?>
                    <a class="button button-primary checkout-action" href="<?= e(url_for('checkout.php')) ?>">Continue to checkout</a>
                <?php else: ?>
                    <p class="form-note">You can browse as a visitor, but checkout requires login.</p>
                    <a class="button button-primary checkout-action" href="<?= e(url_for('login.php')) ?>">Login to checkout</a>
                <?php endif; ?>
            </aside>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
