<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$user = current_user();
$pageTitle = 'Profile';
$errors = [];

if (is_post()) {
    verify_csrf();
    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $location = trim((string) ($_POST['location'] ?? ''));
    $addressLine1 = trim((string) ($_POST['address_line1'] ?? ''));
    $addressLine2 = trim((string) ($_POST['address_line2'] ?? ''));
    $suburb = trim((string) ($_POST['suburb'] ?? ''));
    $city = trim((string) ($_POST['city'] ?? ''));
    $province = trim((string) ($_POST['province'] ?? ''));
    $zipCode = trim((string) ($_POST['zip_code'] ?? ''));
    $bio = trim((string) ($_POST['bio'] ?? ''));

    if ($fullName === '') {
        $errors[] = 'Full name is required.';
    }
    if ($phone !== '' && !valid_basic_phone($phone)) {
        $errors[] = 'Phone number must be exactly 10 digits.';
    }
    if ($zipCode !== '' && !preg_match('/^\d{4}$/', $zipCode)) {
        $errors[] = 'Zip code must be exactly 4 digits.';
    }

    if (!$errors) {
        $stmt = db()->prepare('UPDATE users SET full_name = ?, phone = ?, location = ?, address_line1 = ?, address_line2 = ?, suburb = ?, city = ?, province = ?, zip_code = ?, bio = ? WHERE id = ?');
        $stmt->execute([
            $fullName,
            $phone,
            $location,
            $addressLine1,
            $addressLine2,
            $suburb,
            $city,
            $province,
            $zipCode,
            $bio,
            $user['id'],
        ]);
        flash('success', 'Profile updated.');
        redirect('profile.php');
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="section-shell narrow">
    <div class="section-heading">
        <h1>Profile</h1>
    </div>
    <form class="form-card" method="post">
        <?= csrf_field() ?>
        <?php foreach ($errors as $error): ?>
            <div class="flash flash-error"><?= e($error) ?></div>
        <?php endforeach; ?>
        <label>Full name <input type="text" name="full_name" value="<?= e($user['full_name']) ?>" required></label>
        <label>Email <input type="email" value="<?= e($user['email']) ?>" disabled></label>
        <label>Phone <input type="text" name="phone" value="<?= e($user['phone']) ?>" inputmode="numeric" pattern="\d{10}" maxlength="10"></label>
        <label>ID number <input type="text" value="<?= e($user['id_number'] ?? '') ?>" disabled></label>
        <p class="form-note">ID review status: <span class="pill"><?= e(status_label($user['verification_status'] ?? 'unverified')) ?></span></p>
        <label>Location <input type="text" name="location" value="<?= e($user['location']) ?>"></label>
        <label>Address line 1 <input type="text" name="address_line1" value="<?= e($user['address_line1'] ?? '') ?>"></label>
        <label>Address line 2 <input type="text" name="address_line2" value="<?= e($user['address_line2'] ?? '') ?>"></label>
        <label>Suburb <input type="text" name="suburb" value="<?= e($user['suburb'] ?? '') ?>"></label>
        <label>City <input type="text" name="city" value="<?= e($user['city'] ?? '') ?>"></label>
        <label>Province <input type="text" name="province" value="<?= e($user['province'] ?? '') ?>"></label>
        <label>Zip code <input type="text" name="zip_code" value="<?= e($user['zip_code'] ?? '') ?>" inputmode="numeric" pattern="\d{4}" maxlength="4"></label>
        <label>Bio <textarea name="bio" rows="4"><?= e($user['bio']) ?></textarea></label>
        <button class="button button-primary" type="submit">Save profile</button>
    </form>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
