<?php
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

if (isset($_GET['restart'])) {
    unset($_SESSION['pending_registration']);
    redirect('register.php');
}

$pageTitle = 'Register';
$errors = [];
$pendingRegistration = $_SESSION['pending_registration'] ?? null;
$otpGenerated = false;

if (is_post()) {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? 'start');

    if ($action === 'start') {
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
        $roleInterest = (string) ($_POST['role_interest'] ?? 'buyer');

        if ($fullName === '') {
            $errors[] = 'Full name is required.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email is required.';
        }
        if (!valid_basic_phone($phone)) {
            $errors[] = 'Phone number must be exactly 10 digits.';
        }
        if (!valid_basic_id($idNumber)) {
            $errors[] = 'ID number must be exactly 13 digits.';
        } elseif (id_number_exists($idNumber)) {
            $errors[] = 'That ID number is already registered.';
        }
        if ($location === '') {
            $errors[] = 'Location is required.';
        }
        if ($addressLine1 === '') {
            $errors[] = 'Address line 1 is required.';
        }
        if ($suburb === '') {
            $errors[] = 'Suburb is required.';
        }
        if ($city === '') {
            $errors[] = 'City is required.';
        }
        if ($province === '') {
            $errors[] = 'Province is required.';
        }
        if (!preg_match('/^\d{4}$/', $zipCode)) {
            $errors[] = 'Zip code must be exactly 4 digits.';
        }
        if (!in_array($roleInterest, ['buyer', 'seller'], true)) {
            $errors[] = 'Choose buyer or seller interest.';
        }
        $errors = array_merge($errors, password_validation_errors($password));

        $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'That email is already registered.';
        }

        if (!$errors) {
            $otp = (string) random_int(100000, 999999);
            $_SESSION['pending_registration'] = [
                'full_name' => $fullName,
                'email' => $email,
                'password' => $password,
                'phone' => $phone,
                'id_number' => $idNumber,
                'location' => $location,
                'address_line1' => $addressLine1,
                'address_line2' => $addressLine2,
                'suburb' => $suburb,
                'city' => $city,
                'province' => $province,
                'zip_code' => $zipCode,
                'role_interest' => $roleInterest,
                'otp' => $otp,
            ];
            $pendingRegistration = $_SESSION['pending_registration'];
            $otpGenerated = true;
        }
    } elseif ($action === 'verify') {
        $pendingRegistration = $_SESSION['pending_registration'] ?? null;
        $submittedOtp = trim((string) ($_POST['otp'] ?? ''));

        if (!$pendingRegistration) {
            $errors[] = 'Registration session expired. Please start again.';
        } elseif (!hash_equals((string) $pendingRegistration['otp'], $submittedOtp)) {
            $errors[] = 'The OTP does not match. Please try again.';
        } else {
            $stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$pendingRegistration['email']]);
            if ($stmt->fetch()) {
                $errors[] = 'That email is already registered.';
            } elseif (id_number_exists((string) $pendingRegistration['id_number'])) {
                $errors[] = 'That ID number is already registered.';
            } else {
                $stmt = db()->prepare(
                    'INSERT INTO users (full_name, email, password, role, phone, id_number, location, address_line1, address_line2, suburb, city, province, zip_code, status, verification_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([
                    $pendingRegistration['full_name'],
                    $pendingRegistration['email'],
                    $pendingRegistration['password'],
                    'buyer',
                    $pendingRegistration['phone'],
                    $pendingRegistration['id_number'],
                    $pendingRegistration['location'],
                    $pendingRegistration['address_line1'],
                    $pendingRegistration['address_line2'],
                    $pendingRegistration['suburb'],
                    $pendingRegistration['city'],
                    $pendingRegistration['province'],
                    $pendingRegistration['zip_code'],
                    'active',
                    'pending',
                ]);

                $userId = (int) db()->lastInsertId();
                unset($_SESSION['pending_registration']);
                $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
                $stmt->execute([$userId]);
                login_user($stmt->fetch());
                $message = 'Welcome to Trade Circle. Your account is active for browsing while your ID is pending admin review.';
                if (($pendingRegistration['role_interest'] ?? 'buyer') === 'seller') {
                    $message .= ' Seller access can be requested after your ID is approved.';
                }
                flash('success', $message);
                redirect('dashboard.php');
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="auth-shell">
    <div>
        <p class="eyebrow">Join Trade Circle</p>
        <h1><?= $pendingRegistration ? 'Verify your OTP' : 'Create a C2C account' ?></h1>
        <p><?= $pendingRegistration ? 'Enter the 6-digit registration code to activate browsing access.' : 'Create an account first.' ?></p>
    </div>
    <form class="form-card" method="post">
        <?= csrf_field() ?>
        <?php foreach ($errors as $error): ?>
            <div class="flash flash-error"><?= e($error) ?></div>
        <?php endforeach; ?>

        <?php if ($pendingRegistration): ?>
            <input type="hidden" name="action" value="verify">
            <div class="otp-panel">
                <h2>Prototype OTP</h2>
                <p>Use this code for testing: <strong><?= e($pendingRegistration['otp']) ?></strong></p>
                <?php if ($otpGenerated): ?>
                    <p class="form-note">A new 6-digit OTP was generated for this registration.</p>
                <?php endif; ?>
            </div>
            <label>6-digit OTP <input type="text" name="otp" inputmode="numeric" pattern="\d{6}" maxlength="6" required></label>
            <button class="button button-primary" type="submit">Verify and create account</button>
            <p class="form-note"><a href="<?= e(url_for('register.php?restart=1')) ?>">Start registration again</a></p>
        <?php else: ?>
            <input type="hidden" name="action" value="start">
            <label>Full name <input type="text" name="full_name" value="<?= e($_POST['full_name'] ?? '') ?>" required></label>
            <label>Email <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required></label>
            <label>Password <input type="password" name="password" minlength="8" required></label>
            <small>Use at least 8 characters, one uppercase letter, one lowercase letter, and one special character.</small>
            <label>Phone <input type="text" name="phone" value="<?= e($_POST['phone'] ?? '') ?>" inputmode="numeric" pattern="\d{10}" maxlength="10" required></label>
            <label>ID number <input type="text" name="id_number" value="<?= e($_POST['id_number'] ?? '') ?>" inputmode="numeric" pattern="\d{13}" maxlength="13" required></label>
            <label>Location <input type="text" name="location" value="<?= e($_POST['location'] ?? '') ?>" placeholder="Soweto, Tembisa, Midrand..." required></label>
            <label>Address line 1 <input type="text" name="address_line1" value="<?= e($_POST['address_line1'] ?? '') ?>" placeholder="Street address" required></label>
            <label>Address line 2 <input type="text" name="address_line2" value="<?= e($_POST['address_line2'] ?? '') ?>" placeholder="Apartment, complex, unit number"></label>
            <label>Suburb <input type="text" name="suburb" value="<?= e($_POST['suburb'] ?? '') ?>" required></label>
            <label>City <input type="text" name="city" value="<?= e($_POST['city'] ?? '') ?>" required></label>
            <label>Province <input type="text" name="province" value="<?= e($_POST['province'] ?? '') ?>" required></label>
            <label>Zip code <input type="text" name="zip_code" value="<?= e($_POST['zip_code'] ?? '') ?>" inputmode="numeric" pattern="\d{4}" maxlength="4" required></label>
            <fieldset class="segmented">
                <legend>Account goal</legend>
                <label><input type="radio" name="role_interest" value="buyer" <?= ($_POST['role_interest'] ?? 'buyer') === 'buyer' ? 'checked' : '' ?>> Buy and browse</label>
                <label><input type="radio" name="role_interest" value="seller" <?= ($_POST['role_interest'] ?? '') === 'seller' ? 'checked' : '' ?>> Sell after ID approval</label>
            </fieldset>
            <button class="button button-primary" type="submit">Send OTP</button>
            <p class="form-note">Already registered? <a href="<?= e(url_for('login.php')) ?>">Log in</a></p>
        <?php endif; ?>
    </form>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
