<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    static $cachedUser = null;
    if ($cachedUser && (int) $cachedUser['id'] === (int) $_SESSION['user_id']) {
        return $cachedUser;
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([(int) $_SESSION['user_id']]);
    $user = $stmt->fetch();
    $cachedUser = $user ?: null;

    return $cachedUser;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    merge_guest_cart_into_user((int) $user['id']);
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function has_role(array|string $roles): bool
{
    $user = current_user();
    if (!$user) {
        return false;
    }

    return in_array($user['role'], (array) $roles, true);
}

function is_admin(): bool
{
    return has_role(['admin', 'superadmin']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('warning', 'Please log in to continue.');
        redirect('login.php');
    }
}

function require_role(array|string $roles): void
{
    require_login();
    if (!has_role($roles)) {
        flash('error', 'You do not have permission to access that page.');
        redirect('dashboard.php');
    }
}

function can_manage_role(string $targetRole): bool
{
    $user = current_user();
    if (!$user) {
        return false;
    }
    if ($user['role'] === 'superadmin') {
        return true;
    }

    return $user['role'] === 'admin' && in_array($targetRole, ['buyer', 'seller'], true);
}

function assignable_roles(?int $targetUserId = null): array
{
    $user = current_user();
    if (!$user) {
        return [];
    }

    if ($user['role'] !== 'superadmin') {
        return ['buyer', 'seller'];
    }

    $roles = ['buyer', 'seller', 'admin'];
    if ($targetUserId !== null && $targetUserId === (int) $user['id']) {
        $roles[] = 'superadmin';
    }

    return $roles;
}

function can_assign_role(string $targetRole, ?int $targetUserId = null): bool
{
    return in_array($targetRole, assignable_roles($targetUserId), true);
}
