<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Local C2C Marketplace';
$stmt = db()->query(
    'SELECT l.*, c.name AS category_name, u.full_name AS seller_name
     FROM listings l
     JOIN categories c ON c.id = l.category_id
     JOIN users u ON u.id = l.seller_id
     WHERE ' . public_listing_where() . '
     ORDER BY l.id DESC
     LIMIT 6'
);
$featuredListings = $stmt->fetchAll();
$categoryRows = categories();
$categoryVisuals = category_visuals();

require_once __DIR__ . '/includes/header.php';
?>
<section class="market-hero market-hero-single">
    <div class="hero-copy">
        <p class="eyebrow">Consumer-to-consumer trade</p>
        <h1>Buy, sell, and build trust with people in your community.</h1>
        <form class="search-bar" action="<?= e(url_for('listings.php')) ?>" method="get">
            <input type="search" name="q" placeholder="Search phones, textbooks, services..." aria-label="Search listings">
            <button class="button button-primary" type="submit">Search</button>
        </form>
    </div>
</section>

<section class="section-shell">
    <div class="section-heading">
        <div>
            <p class="eyebrow">Browse by need</p>
            <h2>General marketplace categories</h2>
        </div>
        <a href="<?= e(url_for('listings.php')) ?>">View all listings</a>
    </div>
    <div class="category-bubble-grid">
        <?php foreach ($categoryRows as $category): ?>
            <?php $visual = $categoryVisuals[$category['name']] ?? ['image' => DEFAULT_LISTING_IMAGE, 'accent' => '#7b4a2f']; ?>
            <a class="category-bubble" href="<?= e(url_for('listings.php?category=' . (int) $category['id'])) ?>" style="background-image: url('<?= e($visual['image']) ?>'); --category-accent: <?= e($visual['accent']) ?>;">
                <span><?= e($category['name']) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="section-shell">
    <div class="section-heading">
        <div>
            <p class="eyebrow">Picked by activity</p>
            <h2>Recommended for you</h2>
        </div>
    </div>
    <div class="listing-grid">
        <?php foreach ($featuredListings as $listing): ?>
            <article class="listing-card">
                <a href="<?= e(url_for('listing.php?id=' . (int) $listing['id'])) ?>">
                    <img src="<?= e(listing_image($listing['image_path'])) ?>" alt="<?= e($listing['title']) ?>">
                </a>
                <div class="listing-card-body">
                    <span class="pill"><?= e($listing['category_name']) ?></span>
                    <h3><a href="<?= e(url_for('listing.php?id=' . (int) $listing['id'])) ?>"><?= e($listing['title']) ?></a></h3>
                    <p><?= e(substr($listing['description'], 0, 95)) ?>...</p>
                    <div class="card-row">
                        <strong><?= e(money($listing['price'])) ?></strong>
                        <span><?= e($listing['location']) ?></span>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="trust-band">
    <div>
        <h2>Built around C2C trust</h2>
        <p>TradeCircle keeps the project goals grounded in consumer-to-consumer exchange: verified roles, seller-owned listings, safe reporting, and local seller growth.</p>
    </div>
    <a class="button button-light" href="<?= e(url_for('about.php')) ?>">Read platform goals</a>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
