<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$user = current_user();
$pageTitle = 'Seller Request';
$verificationStatus = (string) ($user['verification_status'] ?? 'unverified');

if ($user['role'] === 'seller') {
    flash('success', 'You already have seller access.');
    redirect('seller_dashboard.php');
}

if (!has_role('buyer')) {
    flash('error', 'Only buyer accounts can request seller access.');
    redirect('dashboard.php');
}

if (is_post()) {
    verify_csrf();
    $businessName = trim((string) ($_POST['business_name'] ?? ''));
    $motivation = trim((string) ($_POST['motivation'] ?? ''));
    if ($verificationStatus !== 'approved') {
        flash('error', 'Your ID must be approved by an admin before you can request seller access.');
    } elseif ($businessName === '' || $motivation === '') {
        flash('error', 'Business name and motivation are required.');
    } else {
        $stmt = db()->prepare('SELECT id FROM seller_requests WHERE user_id = ? AND status = ?');
        $stmt->execute([$user['id'], 'pending']);
        if ($stmt->fetch()) {
            flash('warning', 'You already have a pending seller request.');
        } else {
            $insert = db()->prepare('INSERT INTO seller_requests (user_id, business_name, motivation) VALUES (?, ?, ?)');
            $insert->execute([$user['id'], $businessName, $motivation]);
            flash('success', 'Seller request submitted for admin review.');
        }
        redirect('seller_request.php');
    }
}

$stmt = db()->prepare('SELECT * FROM seller_requests WHERE user_id = ? ORDER BY id DESC');
$stmt->execute([$user['id']]);
$requests = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact">
    <p class="eyebrow">Buyer to seller upgrade</p>
    <h1>Request seller access</h1>
    <p>Admins review seller requests so the platform can preserve trust between consumers.</p>
</section>
<section class="section-shell two-column">
    <form class="form-card" method="post">
        <?= csrf_field() ?>
        <?php if ($verificationStatus !== 'approved'): ?>
            <div class="flash flash-warning">Your current ID review status is <?= e(status_label($verificationStatus)) ?>. Seller requests unlock after admin approval.</div>
        <?php endif; ?>
        <label>Seller or side-hustle name <input type="text" name="business_name" required></label>
        <label>Why do you want seller access? <textarea name="motivation" rows="6" required></textarea></label>
        <button class="button button-primary" type="submit" <?= $verificationStatus !== 'approved' ? 'disabled' : '' ?>>Submit request</button>
    </form>
    <div class="info-panel">
        <h2>ID verification</h2>
        <p>Status: <span class="pill"><?= e(status_label($verificationStatus)) ?></span></p>
        <p class="form-note">Admins review the ID number saved on your profile before seller access is granted.</p>
        <h2>Request history</h2>
        <?php if (!$requests): ?><p>No requests submitted yet.</p><?php endif; ?>
        <?php foreach ($requests as $request): ?>
            <article class="mini-card">
                <strong><?= e($request['business_name']) ?></strong>
                <p>Status: <span class="pill"><?= e(status_label($request['status'])) ?></span></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
