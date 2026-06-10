<?php
require_once __DIR__ . '/includes/init.php';

$listingId = (int) ($_GET['id'] ?? 0);
$listing = get_listing($listingId);

if (!$listing) {
    flash('error', 'Listing not found.');
    redirect('listings.php');
}

$pageTitle = $listing['title'];
require_once __DIR__ . '/includes/header.php';
?>
<section class="listing-detail">
    <div class="listing-media">
        <img src="<?= e(listing_image($listing['image_path'])) ?>" alt="<?= e($listing['title']) ?>">
    </div>
    <div class="listing-info">
        <span class="pill"><?= e($listing['category_name']) ?></span>
        <h1><?= e($listing['title']) ?></h1>
        <p class="price"><?= e(money($listing['price'])) ?></p>
        <p><?= nl2br(e($listing['description'])) ?></p>
        <dl class="detail-list">
            <div><dt>Seller</dt><dd><?= e($listing['seller_name']) ?></dd></div>
            <div><dt>Location</dt><dd><?= e($listing['location'] ?: $listing['seller_location']) ?></dd></div>
            <div><dt>Condition</dt><dd><?= e(status_label($listing['item_condition'])) ?></dd></div>
            <div><dt>Status</dt><dd><?= e(status_label($listing['status'])) ?></dd></div>
            <div><dt>Available</dt><dd><?= (int) $listing['quantity'] ?></dd></div>
            <?php if (!empty($listing['genre'])): ?><div><dt>Genre</dt><dd><?= e($listing['genre']) ?></dd></div><?php endif; ?>
        </dl>

        <?php if ($listing['status'] === 'available' && (int) $listing['quantity'] > 0): ?>
            <form class="inline-form" action="<?= e(url_for('cart.php')) ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="listing_id" value="<?= (int) $listing['id'] ?>">
                <label>
                    Quantity
                    <input type="number" name="quantity" value="1" min="1" max="<?= (int) $listing['quantity'] ?>">
                </label>
                <button class="button button-primary" type="submit">Add to cart</button>
            </form>
        <?php endif; ?>

        <div class="action-row">
            <a class="button button-ghost" href="<?= e(url_for('report.php?listing_id=' . (int) $listing['id'])) ?>">Report item</a>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
