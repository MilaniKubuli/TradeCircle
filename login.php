<?php
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$pageTitle = 'Login';
$error = '';

if (is_post()) {
    verify_csrf();
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || $password !== (string) $user['password']) {
        $error = 'Invalid email or password.';
    } elseif ($user['status'] !== 'active') {
        $error = 'This account is suspended.';
    } else {
        login_user($user);
        flash('success', 'Logged in successfully.');
        redirect('dashboard.php');
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="auth-shell login-shell">
    <div>
        <p class="eyebrow">Welcome back</p>
        <h1>Log in to TradeCircle</h1>
    </div>
    <form class="form-card" method="post">
        <?= csrf_field() ?>
        <?php if ($error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endif; ?>
        <label>Email <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required></label>
        <label>Password <input type="password" name="password" required></label>
        <button class="button button-primary" type="submit">Login</button>
        <p class="form-note">Need an account? <a href="<?= e(url_for('register.php')) ?>">Register</a></p>
    </form>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
