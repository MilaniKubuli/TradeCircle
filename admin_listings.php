<?php
require_once __DIR__ . '/includes/init.php';
require_role(['admin', 'superadmin']);

$pageTitle = 'Admin Listings';

if (is_post()) {
    verify_csrf();
    $listingId = (int) ($_POST['listing_id'] ?? 0);
    $status = (string) ($_POST['status'] ?? 'available');
    if (in_array($status, ['available', 'pending', 'sold', 'cancelled', 'hidden'], true)) {
        db()->prepare('UPDATE listings SET status = ? WHERE id = ?')->execute([$status, $listingId]);
        flash('success', 'Listing status updated.');
    }
    redirect('admin_listings.php');
}

$listings = db()->query(
    'SELECT l.*, c.name AS category_name, u.full_name AS seller_name
     FROM listings l
     JOIN categories c ON c.id = l.category_id
     JOIN users u ON u.id = l.seller_id
     ORDER BY l.id DESC'
)->fetchAll();

$statusCounts = db()->query(
    'SELECT status, COUNT(*) AS total FROM listings GROUP BY status'
)->fetchAll(PDO::FETCH_KEY_PAIR);
$totalListings = array_sum(array_map('intval', $statusCounts));

require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact">
    <p class="eyebrow">Marketplace moderation</p>
    <h1>Listing management</h1>
</section>
<section class="section-shell">
    <div class="admin-summary-grid">
        <div class="dashboard-tile"><strong><?= (int) $totalListings ?></strong><span>Total listings</span></div>
        <?php foreach (['available', 'pending', 'sold', 'cancelled', 'hidden'] as $summaryStatus): ?>
            <div class="dashboard-tile"><strong><?= (int) ($statusCounts[$summaryStatus] ?? 0) ?></strong><span><?= e(status_label($summaryStatus)) ?></span></div>
        <?php endforeach; ?>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>No.</th><th>Listing</th><th>Seller</th><th>Category</th><th>Price</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($listings as $listing): ?>
                <tr>
                    <td>#<?= (int) $listing['id'] ?></td>
                    <td><?= e($listing['title']) ?></td>
                    <td><?= e($listing['seller_name']) ?></td>
                    <td><?= e($listing['category_name']) ?></td>
                    <td><?= e(money($listing['price'])) ?></td>
                    <td><span class="pill"><?= e(status_label($listing['status'])) ?></span></td>
                    <td>
                        <form class="table-form" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="listing_id" value="<?= (int) $listing['id'] ?>">
                            <select name="status">
                                <?php foreach (['available', 'pending', 'sold', 'cancelled', 'hidden'] as $status): ?>
                                    <option value="<?= e($status) ?>" <?= $listing['status'] === $status ? 'selected' : '' ?>><?= e(status_label($status)) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Save</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
