<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Browse Listings';
$q = trim((string) ($_GET['q'] ?? ''));
$categoryId = (int) ($_GET['category'] ?? 0);
$genre = trim((string) ($_GET['genre'] ?? ''));
$sort = (string) ($_GET['sort'] ?? 'latest');
$params = [];
$where = [public_listing_where()];

if ($q !== '') {
    $where[] = '(l.title LIKE ? OR l.description LIKE ? OR l.location LIKE ?)';
    $term = '%' . $q . '%';
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($categoryId > 0) {
    $where[] = 'l.category_id = ?';
    $params[] = $categoryId;
}

$availableGenres = $categoryId > 0 ? genres_for_category($categoryId) : [];
if ($genre !== '' && in_array($genre, $availableGenres, true)) {
    $where[] = 'l.genre = ?';
    $params[] = $genre;
}

$orderBy = match ($sort) {
    'oldest' => 'l.id ASC',
    'price_low' => 'l.price ASC, l.id DESC',
    'price_high' => 'l.price DESC, l.id DESC',
    default => 'l.id DESC',
};

$sql = 'SELECT l.*, c.name AS category_name, u.full_name AS seller_name
        FROM listings l
        JOIN categories c ON c.id = l.category_id
        JOIN users u ON u.id = l.seller_id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY ' . $orderBy;
$stmt = db()->prepare($sql);
$stmt->execute($params);
$listings = $stmt->fetchAll();
$categoryRows = categories();
$genreMap = category_genres();

require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact">
    <p class="eyebrow">Marketplace</p>
    <h1>Browse goods and services from local consumers.</h1>
</section>

<section class="section-shell">
    <div class="filter-panel">
        <script type="application/json" data-genre-map><?= json_encode($genreMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?></script>
        <form class="filter-bar" action="<?= e(url_for('listings.php')) ?>" method="get">
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search listings">
            <select name="category" aria-label="Category" data-category-filter>
                <option value="0" data-category-name="">All categories</option>
                <?php foreach ($categoryRows as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" data-category-name="<?= e($category['name']) ?>" <?= $categoryId === (int) $category['id'] ? 'selected' : '' ?>>
                        <?= e($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="genre" aria-label="Genre" data-genre-filter data-selected="<?= e($genre) ?>">
                <option value="">All genres</option>
            </select>
            <select name="sort" aria-label="Sort listings">
                <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Listing number: newest first</option>
                <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Listing number: oldest first</option>
                <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: low to high</option>
                <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: high to low</option>
            </select>
            <button class="button button-primary" type="submit">Filter</button>
        </form>
    </div>

    <?php if (!$listings): ?>
        <div class="empty-state">
            <h2>No listings found</h2>
            <p>Try a different search term or category.</p>
        </div>
    <?php else: ?>
        <div class="filter-results-heading">
            <p class="eyebrow"><?= count($listings) ?> result<?= count($listings) === 1 ? '' : 's' ?></p>
        </div>
        <div class="listing-grid">
            <?php foreach ($listings as $listing): ?>
                <article class="listing-card">
                    <a href="<?= e(url_for('listing.php?id=' . (int) $listing['id'])) ?>">
                        <img src="<?= e(listing_image($listing['image_path'])) ?>" alt="<?= e($listing['title']) ?>">
                    </a>
                    <div class="listing-card-body">
                        <span class="pill"><?= e($listing['category_name']) ?></span>
                        <?php if (!empty($listing['genre'])): ?><span class="pill"><?= e($listing['genre']) ?></span><?php endif; ?>
                        <h3><a href="<?= e(url_for('listing.php?id=' . (int) $listing['id'])) ?>"><?= e($listing['title']) ?></a></h3>
                        <p><?= e(substr($listing['description'], 0, 110)) ?>...</p>
                        <div class="card-row">
                            <strong><?= e(money($listing['price'])) ?></strong>
                            <span><?= e($listing['location']) ?></span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
