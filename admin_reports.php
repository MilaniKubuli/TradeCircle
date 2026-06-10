<?php
require_once __DIR__ . '/includes/init.php';
require_role(['admin', 'superadmin']);

$pageTitle = 'Reports';

if (is_post()) {
    verify_csrf();
    $reportId = (int) ($_POST['report_id'] ?? 0);
    $status = (string) ($_POST['status'] ?? 'reviewing');
    $notes = trim((string) ($_POST['admin_notes'] ?? ''));
    if (in_array($status, ['reviewing', 'resolved', 'dismissed'], true)) {
        db()->prepare('UPDATE reports SET status = ?, admin_id = ?, admin_notes = ? WHERE id = ?')
            ->execute([$status, current_user()['id'], $notes, $reportId]);
        if (isset($_POST['hide_listing'])) {
            $listingId = (int) ($_POST['listing_id'] ?? 0);
            db()->prepare('UPDATE listings SET status = ? WHERE id = ?')->execute(['hidden', $listingId]);
        }
        flash('success', 'Report updated.');
    }
    redirect('admin_reports.php');
}

$reports = db()->query(
    'SELECT r.*, l.title, u.full_name AS reporter_name
     FROM reports r
     JOIN listings l ON l.id = r.listing_id
     LEFT JOIN users u ON u.id = r.reporter_id
     WHERE r.status IN ("open", "reviewing")
     ORDER BY r.id DESC'
)->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact">
    <p class="eyebrow">Moderation</p>
    <h1>Listing reports</h1>
</section>
<section class="section-shell">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Listing</th><th>Reporter</th><th>Reason</th><th>Status</th><th>Admin action</th></tr></thead>
            <tbody>
            <?php foreach ($reports as $report): ?>
                <tr>
                    <td><a href="<?= e(url_for('listing.php?id=' . (int) $report['listing_id'])) ?>"><?= e($report['title']) ?></a></td>
                    <td><?= e($report['reporter_name'] ?: 'Visitor') ?></td>
                    <td><?= e($report['reason']) ?><br><small><?= e($report['details']) ?></small></td>
                    <td><span class="pill"><?= e(status_label($report['status'])) ?></span></td>
                    <td>
                        <form class="report-form" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="report_id" value="<?= (int) $report['id'] ?>">
                            <input type="hidden" name="listing_id" value="<?= (int) $report['listing_id'] ?>">
                            <select name="status">
                                <?php foreach (['reviewing', 'resolved', 'dismissed'] as $status): ?>
                                    <option value="<?= e($status) ?>" <?= $report['status'] === $status ? 'selected' : '' ?>><?= e(status_label($status)) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="admin_notes" value="<?= e($report['admin_notes']) ?>" placeholder="Notes">
                            <label class="checkbox-line"><input type="checkbox" name="hide_listing" value="1"> Hide listing</label>
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
