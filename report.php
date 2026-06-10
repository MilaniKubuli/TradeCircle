<?php
require_once __DIR__ . '/includes/init.php';

$listingId = (int) ($_GET['listing_id'] ?? $_POST['listing_id'] ?? 0);
$listing = get_listing($listingId);
if (!$listing) {
    flash('error', 'Listing not found.');
    redirect('listings.php');
}

$pageTitle = 'Report Listing';
$errors = [];

if (is_post()) {
    verify_csrf();
    $reason = trim((string) ($_POST['reason'] ?? ''));
    $details = trim((string) ($_POST['details'] ?? ''));
    if ($reason === '') {
        $errors[] = 'Choose a report reason.';
    }
    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO reports (listing_id, reporter_id, reason, details) VALUES (?, ?, ?, ?)');
        $stmt->execute([$listingId, current_user()['id'] ?? null, $reason, $details]);
        flash('success', 'Report submitted for admin review.');
        redirect('listing.php?id=' . $listingId);
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="section-shell narrow">
    <div class="section-heading">
        <div>
            <p class="eyebrow">Community safety</p>
            <h1>Report listing</h1>
            <p><?= e($listing['title']) ?></p>
        </div>
    </div>
    <form class="form-card" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="listing_id" value="<?= (int) $listing['id'] ?>">
        <?php foreach ($errors as $error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endforeach; ?>
        <label>Reason
            <select name="reason" required>
                <option value="">Choose reason</option>
                <option>Possible scam or fake item</option>
                <option>Unsafe or prohibited item</option>
                <option>Incorrect price or misleading description</option>
                <option>Seller behavior concern</option>
                <option>Other</option>
            </select>
        </label>
        <label>Details <textarea name="details" rows="5" placeholder="Explain what admin should review."></textarea></label>
        <button class="button button-primary" type="submit">Submit report</button>
    </form>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

