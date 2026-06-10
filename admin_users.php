<?php
require_once __DIR__ . '/includes/init.php';
require_role(['admin', 'superadmin']);

$admin = current_user();
$pageTitle = 'Admin Users';
$identityStatuses = ['unverified', 'pending', 'approved', 'rejected'];

if (is_post()) {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    if (isset($_POST['delete_user'])) {
        $userId = (int) ($_POST['delete_user'] ?? 0);
        $target = db()->prepare('SELECT id, full_name, email, role FROM users WHERE id = ?');
        $target->execute([$userId]);
        $targetUser = $target->fetch();
        $targetRole = (string) ($targetUser['role'] ?? '');
        if (!$targetUser || $userId === (int) $admin['id'] || !can_manage_role($targetRole)) {
            flash('error', 'You cannot delete that user.');
        } else {
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $buyerSnapshot = (string) $targetUser['full_name'];
                $buyerEmailSnapshot = (string) $targetUser['email'];

                $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?')->execute([$userId]);
                $pdo->prepare('DELETE FROM seller_requests WHERE user_id = ?')->execute([$userId]);

                $pdo->prepare(
                    'UPDATE orders
                     SET buyer_name_snapshot = COALESCE(buyer_name_snapshot, ?),
                         buyer_email_snapshot = COALESCE(buyer_email_snapshot, ?)
                     WHERE buyer_id = ?'
                )->execute([$buyerSnapshot, $buyerEmailSnapshot, $userId]);

                $pdo->prepare(
                    'UPDATE order_items oi
                     JOIN orders o ON o.id = oi.order_id
                     SET oi.item_status = ?
                     WHERE o.buyer_id = ? AND o.status <> ?'
                )->execute(['cancelled', $userId, 'paid']);

                $pdo->prepare('UPDATE orders SET status = ? WHERE buyer_id = ? AND status <> ?')
                    ->execute(['cancelled', $userId, 'paid']);

                $pdo->prepare('UPDATE orders SET buyer_id = NULL WHERE buyer_id = ? AND status = ?')
                    ->execute([$userId, 'paid']);

                $pdo->prepare(
                    'UPDATE order_items oi
                     JOIN orders o ON o.id = oi.order_id
                     JOIN listings l ON l.id = oi.listing_id
                     JOIN users s ON s.id = oi.seller_id
                     SET oi.seller_name_snapshot = COALESCE(oi.seller_name_snapshot, s.full_name),
                         oi.seller_email_snapshot = COALESCE(oi.seller_email_snapshot, s.email),
                         oi.listing_title_snapshot = COALESCE(oi.listing_title_snapshot, l.title)
                     WHERE oi.seller_id = ?'
                )->execute([$userId]);

                $pdo->prepare(
                    'UPDATE order_items oi
                     JOIN orders o ON o.id = oi.order_id
                     SET oi.item_status = ?
                     WHERE oi.seller_id = ? AND o.status <> ?'
                )->execute(['cancelled', $userId, 'paid']);

                $pdo->prepare(
                    'UPDATE orders o
                     SET o.status = ?
                     WHERE o.status <> ?
                       AND EXISTS (
                           SELECT 1 FROM order_items oi
                           WHERE oi.order_id = o.id AND oi.seller_id = ?
                       )'
                )->execute(['cancelled', 'paid', $userId]);

                $pdo->prepare(
                    'DELETE p FROM payments p
                     JOIN orders o ON o.id = p.order_id
                     WHERE o.buyer_id = ? AND o.status <> ?'
                )->execute([$userId, 'paid']);

                $pdo->prepare(
                    'DELETE p FROM payments p
                     JOIN orders o ON o.id = p.order_id
                     JOIN order_items oi ON oi.order_id = o.id
                     WHERE oi.seller_id = ? AND o.status <> ?'
                )->execute([$userId, 'paid']);

                $pdo->prepare(
                    'UPDATE order_items oi
                     JOIN orders o ON o.id = oi.order_id
                     SET oi.seller_id = NULL,
                         oi.listing_id = NULL
                     WHERE oi.seller_id = ? AND o.status = ?'
                )->execute([$userId, 'paid']);

                $pdo->prepare(
                    'DELETE oi FROM order_items oi
                     JOIN orders o ON o.id = oi.order_id
                     WHERE (oi.seller_id = ? OR o.buyer_id = ?) AND o.status <> ?'
                )->execute([$userId, $userId, 'paid']);

                $pdo->prepare('DELETE FROM orders WHERE buyer_id = ? AND status <> ?')
                    ->execute([$userId, 'paid']);

                $pdo->prepare('DELETE FROM listings WHERE seller_id = ?')->execute([$userId]);
                $pdo->prepare('UPDATE reports SET reporter_id = NULL WHERE reporter_id = ?')->execute([$userId]);
                $pdo->prepare('UPDATE reports SET admin_id = NULL WHERE admin_id = ?')->execute([$userId]);
                $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);

                $pdo->commit();
                flash('success', 'User permanently deleted. Pending activity was cancelled and paid history was kept as deleted-user history.');
            } catch (Throwable $exception) {
                $pdo->rollBack();
                error_log('User delete failed: ' . $exception->getMessage());
                flash('error', 'User could not be deleted: ' . $exception->getMessage());
            }
        }
    } elseif ($action === 'create') {
        $role = (string) ($_POST['role'] ?? 'buyer');
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $idNumber = trim((string) ($_POST['id_number'] ?? ''));
        $location = trim((string) ($_POST['location'] ?? ''));
        $addressLine1 = trim((string) ($_POST['address_line1'] ?? ''));
        $addressLine2 = trim((string) ($_POST['address_line2'] ?? ''));
        $suburb = trim((string) ($_POST['suburb'] ?? ''));
        $city = trim((string) ($_POST['city'] ?? ''));
        $province = trim((string) ($_POST['province'] ?? ''));
        $zipCode = trim((string) ($_POST['zip_code'] ?? ''));
        $verificationStatus = (string) ($_POST['verification_status'] ?? 'unverified');
        $errors = [];

        if ($fullName === '') {
            $errors[] = 'Full name is required.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email is required.';
        }
        if ($phone !== '' && !valid_basic_phone($phone)) {
            $errors[] = 'Phone number must be exactly 10 digits.';
        }
        if ($idNumber !== '' && !valid_basic_id($idNumber)) {
            $errors[] = 'ID number must be exactly 13 digits.';
        } elseif ($idNumber !== '' && id_number_exists($idNumber)) {
            $errors[] = 'That ID number is already registered.';
        }
        if ($zipCode !== '' && !preg_match('/^\d{4}$/', $zipCode)) {
            $errors[] = 'Zip code must be exactly 4 digits.';
        }
        if (!in_array($verificationStatus, $identityStatuses, true)) {
            $errors[] = 'Choose a valid ID review status.';
        }
        if ($role === 'seller' && $verificationStatus !== 'approved') {
            $errors[] = 'Seller accounts need approved ID review status.';
        }
        $errors = array_merge($errors, password_validation_errors($password));

        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'That email is already registered.';
        }

        foreach ($errors as $error) {
            flash('error', $error);
        }
        if (!can_assign_role($role)) {
            flash('error', 'You cannot create that role.');
        } elseif (!$errors) {
            $stmt = db()->prepare('INSERT INTO users (full_name, email, password, role, phone, id_number, location, address_line1, address_line2, suburb, city, province, zip_code, status, verification_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $fullName,
                $email,
                $password,
                $role,
                $phone,
                $idNumber !== '' ? $idNumber : null,
                $location,
                $addressLine1,
                $addressLine2,
                $suburb,
                $city,
                $province,
                $zipCode,
                'active',
                $verificationStatus,
            ]);
            flash('success', 'User created.');
        }
    } elseif ($action === 'bulk_update') {
        $roles = $_POST['role'] ?? [];
        $statuses = $_POST['status'] ?? [];
        $verificationStatuses = $_POST['verification_status'] ?? [];
        $updated = 0;
        $sellerRoleAdjusted = 0;
        $targetStmt = db()->prepare('SELECT id, role FROM users WHERE id = ?');
        $updateStmt = db()->prepare('UPDATE users SET role = ?, status = ?, verification_status = ? WHERE id = ? AND id <> ?');
        foreach ($roles as $userId => $role) {
            $userId = (int) $userId;
            $role = (string) $role;
            $status = (string) ($statuses[$userId] ?? 'active');
            $verificationStatus = (string) ($verificationStatuses[$userId] ?? 'unverified');
            $targetStmt->execute([$userId]);
            $target = $targetStmt->fetch();
            if (!$target || $userId === (int) $admin['id']) {
                continue;
            }
            if (!can_manage_role((string) $target['role']) || !can_assign_role($role, $userId) || !in_array($status, ['active', 'suspended'], true) || !in_array($verificationStatus, $identityStatuses, true)) {
                continue;
            }
            if ($role === 'seller' && $verificationStatus !== 'approved') {
                $role = 'buyer';
                $sellerRoleAdjusted++;
            }
            $updateStmt->execute([$role, $status, $verificationStatus, $userId, $admin['id']]);
            $updated++;
        }
        flash('success', $updated . ' user changes saved.');
        if ($sellerRoleAdjusted > 0) {
            flash('warning', $sellerRoleAdjusted . ' seller role change was kept as buyer because ID review was not approved.');
        }
    }
    redirect('admin_users.php');
}

$users = db()->query('SELECT * FROM users ORDER BY role, full_name')->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero compact">
    <p class="eyebrow">RBAC</p>
    <h1>User management</h1>
    <p>Admins manage buyers and sellers. The superadmin can also manage admins.</p>
</section>
<section class="section-shell two-column wide">
    <form class="form-card" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <h2>Create user</h2>
        <label>Full name <input type="text" name="full_name" required></label>
        <label>Email <input type="email" name="email" required></label>
        <label>Password <input type="password" name="password" minlength="8" required></label>
        <small>Use 8+ characters with uppercase, lowercase, and a special character.</small>
        <label>Phone <input type="text" name="phone" inputmode="numeric" pattern="\d{10}" maxlength="10"></label>
        <label>ID number <input type="text" name="id_number" inputmode="numeric" pattern="\d{13}" maxlength="13"></label>
        <label>Location <input type="text" name="location"></label>
        <label>Address line 1 <input type="text" name="address_line1"></label>
        <label>Address line 2 <input type="text" name="address_line2"></label>
        <label>Suburb <input type="text" name="suburb"></label>
        <label>City <input type="text" name="city"></label>
        <label>Province <input type="text" name="province"></label>
        <label>Zip code <input type="text" name="zip_code" inputmode="numeric" pattern="\d{4}" maxlength="4"></label>
        <label>ID review status
            <select class="admin-select" name="verification_status">
                <?php foreach ($identityStatuses as $status): ?>
                    <option value="<?= e($status) ?>"><?= e(status_label($status)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Role
            <select class="admin-select" name="role">
                <?php foreach (assignable_roles() as $role): ?>
                    <option value="<?= e($role) ?>"><?= e(role_label($role)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button class="button button-primary" type="submit">Create user</button>
    </form>
    <form class="table-wrap" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="bulk_update">
        <table>
            <thead><tr><th>User</th><th>Contact</th><th>ID review</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($users as $userRow): ?>
                <tr>
                    <td><?= e($userRow['full_name']) ?><br><small><?= e($userRow['email']) ?></small></td>
                    <td>
                        <?= e($userRow['phone'] ?: 'No phone') ?><br>
                        <small><?= e($userRow['location'] ?: 'No location') ?></small><br>
                        <small><?= e(trim(($userRow['suburb'] ?? '') . ' ' . ($userRow['city'] ?? '')) ?: 'No delivery suburb/city') ?></small>
                    </td>
                    <td>
                        <?= e($userRow['id_number'] ?: 'No ID') ?><br>
                        <?php if (can_manage_role($userRow['role']) && (int) $userRow['id'] !== (int) $admin['id']): ?>
                            <select class="admin-select admin-select-wide" name="verification_status[<?= (int) $userRow['id'] ?>]">
                                <?php foreach ($identityStatuses as $status): ?>
                                    <option value="<?= e($status) ?>" <?= ($userRow['verification_status'] ?? 'unverified') === $status ? 'selected' : '' ?>><?= e(status_label($status)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <span class="pill"><?= e(status_label($userRow['verification_status'] ?? 'unverified')) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= e(role_label($userRow['role'])) ?></td>
                    <td><?= e(status_label($userRow['status'])) ?></td>
                    <td>
                        <?php if (can_manage_role($userRow['role']) && (int) $userRow['id'] !== (int) $admin['id']): ?>
                            <div class="table-form">
                                <select class="admin-select" name="role[<?= (int) $userRow['id'] ?>]">
                                    <?php foreach (assignable_roles((int) $userRow['id']) as $role): ?>
                                        <option value="<?= e($role) ?>" <?= $userRow['role'] === $role ? 'selected' : '' ?>><?= e(role_label($role)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="user-actions" data-status-group>
                                <input type="hidden" name="status[<?= (int) $userRow['id'] ?>]" value="<?= e($userRow['status']) ?>" data-status-value>
                                <button class="status-icon-button status-active-button <?= $userRow['status'] === 'active' ? 'is-selected' : '' ?>" type="button" data-status-choice="active" data-tooltip="Active" aria-label="Set user active"><?= nav_icon('check') ?></button>
                                <button class="status-icon-button status-paused-button <?= $userRow['status'] === 'suspended' ? 'is-selected' : '' ?>" type="button" data-status-choice="suspended" data-tooltip="Suspended" aria-label="Suspend user"><?= nav_icon('pause') ?></button>
                                <button class="delete-button" type="submit" name="delete_user" value="<?= (int) $userRow['id'] ?>" data-confirm-button="Permanently delete this user and cancel their pending activity?">Delete</button>
                            </div>
                        <?php else: ?>
                            <span class="muted">Protected</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="bulk-actions">
            <button class="button button-primary" type="submit">Save all changes</button>
        </div>
    </form>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
