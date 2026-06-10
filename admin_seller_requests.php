<?php
require_once __DIR__ . '/includes/init.php';
require_role(['admin', 'superadmin']);

$pageTitle = 'Seller Requests';

if (is_post()) {
    verify_csrf();
    $requestId = (int) ($_POST['request_id'] ?? 0);
    $status = (string) ($_POST['status'] ?? 'pending');
    if (in_array($status, ['approved', 'rejected'], true)) {
        $stmt = db()->prepare(
            'SELECT sr.*, u.verification_status
             FROM seller_requests sr
             JOIN users u ON u.id = sr.user_id
             WHERE sr.id = ?'
        );
        $stmt->execute([$requestId]);
        $request = $stmt->fetch();
        if ($request) {
            if ($status === 'approved' && $request['verification_status'] !== 'approved') {
                flash('error', 'This seller request cannot be approved until the user ID is approved.');
            } else {
                db()->prepare('UPDATE seller_requests SET status = ? WHERE id = ?')
                    ->execute([$status, $requestId]);
                if ($status === 'approved') {
                    db()->prepare('UPDATE users SET role = ? WHERE id = ?')->execute(['seller', $request['user_id']]);
                }
                flash('success', 'Seller request updated.');
            }
        }
    }
    redirect('admin_seller_requests.php');
}

$requests = db()->query(
    'SELECT sr.*, u.full_name, u.email, u.location, u.id_number, u.verification_status
     FROM seller_requests sr
     JOIN users u ON u.id = sr.user_id
     ORDER BY sr.id DESC'
)->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact">
    <p class="eyebrow">Seller access</p>
    <h1>Seller requests</h1>
</section>
<section class="section-shell">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Buyer</th><th>ID review</th><th>Business</th><th>Motivation</th><th>Status</th><th>Review</th></tr></thead>
            <tbody>
            <?php foreach ($requests as $request): ?>
                <tr>
                    <td><?= e($request['full_name']) ?><br><small><?= e($request['email']) ?></small></td>
                    <td><?= e($request['id_number'] ?: 'No ID') ?><br><span class="pill"><?= e(status_label($request['verification_status'])) ?></span></td>
                    <td><?= e($request['business_name']) ?></td>
                    <td><?= e($request['motivation']) ?></td>
                    <td><span class="pill"><?= e(status_label($request['status'])) ?></span></td>
                    <td>
                        <?php if ($request['status'] === 'pending'): ?>
                            <form class="table-form" method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                                <button name="status" value="approved" type="submit">Approve</button>
                                <button name="status" value="rejected" type="submit">Reject</button>
                            </form>
                        <?php else: ?>
                            <span class="pill"><?= e(status_label($request['status'])) ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
