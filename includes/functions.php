<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url_for(string $path = ''): string
{
    $path = ltrim($path, '/');
    return BASE_URL . ($path === '' ? '' : '/' . $path);
}

function absolute_url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return APP_URL . ($path === '' ? '' : '/' . $path);
}

function redirect(string $path): never
{
    header('Location: ' . url_for($path));
    exit;
}

function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function pull_flash(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', (string) $submitted)) {
        flash('error', 'The form expired. Please try again.');
        redirect('index.php');
    }
}

function money(float|int|string $amount): string
{
    return 'R ' . number_format((float) $amount, 2, '.', ' ');
}

function role_label(string $role): string
{
    return [
        'buyer' => 'Buyer',
        'seller' => 'Seller',
        'admin' => 'Admin',
        'superadmin' => 'Superadmin',
    ][$role] ?? ucfirst($role);
}

function status_label(string $status): string
{
    return ucwords(str_replace('_', ' ', $status));
}

function valid_basic_id(string $idNumber): bool
{
    return preg_match('/^\d{13}$/', $idNumber) === 1;
}

function valid_basic_phone(string $phone): bool
{
    return preg_match('/^\d{10}$/', $phone) === 1;
}

function id_number_exists(string $idNumber, ?int $excludeUserId = null): bool
{
    $idNumber = trim($idNumber);
    if ($idNumber === '') {
        return false;
    }

    if ($excludeUserId !== null) {
        $stmt = db()->prepare('SELECT id FROM users WHERE id_number = ? AND id <> ? LIMIT 1');
        $stmt->execute([$idNumber, $excludeUserId]);
    } else {
        $stmt = db()->prepare('SELECT id FROM users WHERE id_number = ? LIMIT 1');
        $stmt->execute([$idNumber]);
    }

    return (bool) $stmt->fetch();
}

function password_validation_errors(string $password): array
{
    $errors = [];
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if (preg_match('/[A-Z]/', $password) !== 1) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    }
    if (preg_match('/[a-z]/', $password) !== 1) {
        $errors[] = 'Password must contain at least one lowercase letter.';
    }
    if (preg_match('/[^A-Za-z0-9]/', $password) !== 1) {
        $errors[] = 'Password must contain at least one special character.';
    }

    return $errors;
}

function nav_icon(string $name): string
{
    $icons = [
        'home' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 10.8 12 3l9 7.8v9.7a.5.5 0 0 1-.5.5H15v-6H9v6H3.5a.5.5 0 0 1-.5-.5z"/></svg>',
        'browse' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v4H4zm0 6h7v8H4zm9 0h7v8h-7z"/></svg>',
        'about' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 10h2v8h-2zm0-4h2v2h-2z"/><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20m0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16"/></svg>',
        'cart' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 19.5A1.5 1.5 0 1 0 7 22a1.5 1.5 0 0 0 0-2.5m10 0A1.5 1.5 0 1 0 17 22a1.5 1.5 0 0 0 0-2.5M6.2 6l.4 2h12.1l-1.3 5.4H8L6 3H3v2h1.4l2 10.4A2 2 0 0 0 8.4 17H19v-2H8.4l-.3-1.6h10.7L21 6z"/></svg>',
        'seller' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 7h14l1 5v8H4v-8zm2 7v4h10v-4zm.4-5-.4 2h10l-.4-2zM8 3h8v2H8z"/></svg>',
        'admin' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 4 5.5V11c0 5 3.4 9.3 8 10.5 4.6-1.2 8-5.5 8-10.5V5.5zm0 2.2 6 2.6V11c0 3.8-2.4 7.2-6 8.4-3.6-1.2-6-4.6-6-8.4V6.8z"/><path d="M11 7h2v5h-2zm0 7h2v2h-2z"/></svg>',
        'profile' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10m0 2c-4.4 0-8 2.2-8 5v2h16v-2c0-2.8-3.6-5-8-5"/></svg>',
        'sun' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 2h2v3h-2zm0 17h2v3h-2zM4.2 5.6l1.4-1.4 2.1 2.1-1.4 1.4zM16.3 17.7l1.4-1.4 2.1 2.1-1.4 1.4zM2 11h3v2H2zm17 0h3v2h-3zM4.2 18.4l2.1-2.1 1.4 1.4-2.1 2.1zM16.3 6.3l2.1-2.1 1.4 1.4-2.1 2.1zM12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10"/></svg>',
        'moon' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.6 15.3A8 8 0 0 1 8.7 3.4a9 9 0 1 0 11.9 11.9"/></svg>',
        'back' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M11 5 4 12l7 7v-5h9v-4h-9z"/></svg>',
        'check' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9.2 16.2-4-4L3.8 13.6l5.4 5.4L20.5 7.7 19.1 6.3z"/></svg>',
        'pause' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 5h4v14H7zm6 0h4v14h-4z"/></svg>',
    ];

    return $icons[$name] ?? '';
}

function nav_link(string $path, string $label, string $icon, string $class = ''): string
{
    return '<a class="nav-link ' . e($class) . '" href="' . e(url_for($path)) . '">' . nav_icon($icon) . '<span>' . e($label) . '</span></a>';
}

function page_file(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
    $file = basename(rtrim($path, '/'));
    return $file === 'tradecircle' || $file === '' ? 'index.php' : $file;
}

function breadcrumbs(string $pageTitle): array
{
    $file = page_file();
    $crumbs = [['label' => 'Home', 'url' => url_for('index.php')]];

    $adminPages = ['admin_dashboard.php', 'admin_users.php', 'admin_reports.php', 'admin_seller_requests.php', 'admin_orders.php', 'admin_listings.php'];
    $sellerPages = ['seller_dashboard.php', 'seller_listing_form.php'];

    if (in_array($file, $adminPages, true) && $file !== 'admin_dashboard.php') {
        $crumbs[] = ['label' => 'Admin', 'url' => url_for('admin_dashboard.php')];
    } elseif (in_array($file, $sellerPages, true) && $file !== 'seller_dashboard.php') {
        $crumbs[] = ['label' => 'Seller Hub', 'url' => url_for('seller_dashboard.php')];
    } elseif ($file === 'listing.php' || $file === 'cart.php' || $file === 'checkout.php') {
        $crumbs[] = ['label' => 'Browse', 'url' => url_for('listings.php')];
    }

    if ($file !== 'index.php') {
        $crumbs[] = ['label' => $pageTitle, 'url' => null];
    }

    return $crumbs;
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $letters = '';
    foreach ($parts as $part) {
        if ($part !== '') {
            $letters .= strtoupper(substr($part, 0, 1));
        }
        if (strlen($letters) >= 2) {
            break;
        }
    }

    return $letters !== '' ? $letters : 'TC';
}

function categories(): array
{
    return db()->query('SELECT id, name, description FROM categories ORDER BY name')->fetchAll();
}

function category_visuals(): array
{
    return [
        'Beauty' => ['image' => 'https://images.unsplash.com/photo-1556228578-8c89e6adf883?auto=format&fit=crop&w=900&q=80', 'accent' => '#8f3d52'],
        'Books' => ['image' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=900&q=80', 'accent' => '#6f4e37'],
        'Electronics' => ['image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=900&q=80', 'accent' => '#275f5f'],
        'Fashion' => ['image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=900&q=80', 'accent' => '#8b4b32'],
        'Home' => ['image' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=900&q=80', 'accent' => '#7b5c3d'],
        'Kids' => ['image' => 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?auto=format&fit=crop&w=900&q=80', 'accent' => '#b07835'],
        'Services' => ['image' => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=900&q=80', 'accent' => '#546f42'],
        'Sports' => ['image' => 'https://images.unsplash.com/photo-1517649763962-0c623066013b?auto=format&fit=crop&w=900&q=80', 'accent' => '#6b5d2f'],
    ];
}

function category_genres(): array
{
    return [
        'Beauty' => ['Skincare', 'Haircare', 'Fragrance', 'Grooming'],
        'Books' => ['Textbooks', 'Fiction', 'Business', 'Study Guides'],
        'Electronics' => ['Phones', 'Computers', 'Audio', 'Gaming', 'Accessories'],
        'Fashion' => ['Shoes', 'Bags', 'Streetwear', 'Formalwear', 'Accessories'],
        'Home' => ['Furniture', 'Kitchen', 'Appliances', 'Decor'],
        'Kids' => ['Toys', 'School', 'Baby Gear', 'Clothing'],
        'Services' => ['Beauty Service', 'Repairs', 'Tutoring', 'Delivery'],
        'Sports' => ['Cycling', 'Fitness', 'Team Sports', 'Outdoor'],
    ];
}

function genres_for_category(int $categoryId): array
{
    $stmt = db()->prepare('SELECT name FROM categories WHERE id = ?');
    $stmt->execute([$categoryId]);
    $name = (string) $stmt->fetchColumn();

    return category_genres()[$name] ?? [];
}

function get_listing(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT l.*, c.name AS category_name, u.full_name AS seller_name, u.location AS seller_location, u.email AS seller_email
         FROM listings l
         JOIN categories c ON c.id = l.category_id
         JOIN users u ON u.id = l.seller_id
         WHERE l.id = ?'
    );
    $stmt->execute([$id]);
    $listing = $stmt->fetch();

    return $listing ?: null;
}

function public_listing_where(): string
{
    return "l.status = 'available' AND l.quantity > 0";
}

function listing_image(?string $imagePath): string
{
    $imagePath = trim((string) $imagePath);
    return $imagePath !== '' ? $imagePath : DEFAULT_LISTING_IMAGE;
}

function get_cart_count(): int
{
    if (function_exists('is_logged_in') && is_logged_in()) {
        $stmt = db()->prepare('SELECT COALESCE(SUM(quantity), 0) AS qty FROM cart_items WHERE user_id = ?');
        $stmt->execute([current_user()['id']]);
        return (int) $stmt->fetchColumn();
    }

    return array_sum($_SESSION['guest_cart'] ?? []);
}

function add_to_cart(int $listingId, int $quantity = 1): void
{
    $quantity = max(1, $quantity);
    $listing = get_listing($listingId);

    if (!$listing || $listing['status'] !== 'available' || (int) $listing['quantity'] <= 0) {
        flash('error', 'That listing is not available.');
        return;
    }

    $quantity = min($quantity, (int) $listing['quantity']);

    if (function_exists('is_logged_in') && is_logged_in()) {
        $stmt = db()->prepare('SELECT id, quantity FROM cart_items WHERE user_id = ? AND listing_id = ?');
        $stmt->execute([current_user()['id'], $listingId]);
        $existing = $stmt->fetch();

        if ($existing) {
            $newQty = min((int) $existing['quantity'] + $quantity, (int) $listing['quantity']);
            $update = db()->prepare('UPDATE cart_items SET quantity = ? WHERE id = ?');
            $update->execute([$newQty, $existing['id']]);
        } else {
            $insert = db()->prepare('INSERT INTO cart_items (user_id, listing_id, quantity) VALUES (?, ?, ?)');
            $insert->execute([current_user()['id'], $listingId, $quantity]);
        }
    } else {
        $_SESSION['guest_cart'] ??= [];
        $_SESSION['guest_cart'][$listingId] = min(($_SESSION['guest_cart'][$listingId] ?? 0) + $quantity, (int) $listing['quantity']);
    }

    flash('success', 'Item added to cart.');
}

function cart_items(): array
{
    if (function_exists('is_logged_in') && is_logged_in()) {
        $stmt = db()->prepare(
            'SELECT ci.id AS cart_item_id, ci.quantity AS cart_quantity, l.*, c.name AS category_name, u.full_name AS seller_name, u.email AS seller_email
             FROM cart_items ci
             JOIN listings l ON l.id = ci.listing_id
             JOIN categories c ON c.id = l.category_id
             JOIN users u ON u.id = l.seller_id
             WHERE ci.user_id = ?
             ORDER BY ci.id DESC'
        );
        $stmt->execute([current_user()['id']]);
        return $stmt->fetchAll();
    }

    $cart = $_SESSION['guest_cart'] ?? [];
    if (!$cart) {
        return [];
    }

    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare(
        "SELECT l.*, c.name AS category_name, u.full_name AS seller_name, u.email AS seller_email
         FROM listings l
         JOIN categories c ON c.id = l.category_id
         JOIN users u ON u.id = l.seller_id
         WHERE l.id IN ($placeholders)"
    );
    $stmt->execute($ids);
    $items = [];
    foreach ($stmt->fetchAll() as $listing) {
        $listing['cart_item_id'] = null;
        $listing['cart_quantity'] = $cart[$listing['id']] ?? 1;
        $items[] = $listing;
    }

    return $items;
}

function cart_total(array $items): float
{
    $total = 0.0;
    foreach ($items as $item) {
        $total += (float) $item['price'] * (int) $item['cart_quantity'];
    }

    return $total;
}

function update_cart_quantity(int $listingId, int $quantity): void
{
    $quantity = max(0, $quantity);

    if (function_exists('is_logged_in') && is_logged_in()) {
        if ($quantity === 0) {
            $stmt = db()->prepare('DELETE FROM cart_items WHERE user_id = ? AND listing_id = ?');
            $stmt->execute([current_user()['id'], $listingId]);
            return;
        }

        $listing = get_listing($listingId);
        $quantity = min($quantity, max(1, (int) ($listing['quantity'] ?? 1)));
        $stmt = db()->prepare('UPDATE cart_items SET quantity = ? WHERE user_id = ? AND listing_id = ?');
        $stmt->execute([$quantity, current_user()['id'], $listingId]);
        return;
    }

    if ($quantity === 0) {
        unset($_SESSION['guest_cart'][$listingId]);
        return;
    }

    $listing = get_listing($listingId);
    $_SESSION['guest_cart'][$listingId] = min($quantity, max(1, (int) ($listing['quantity'] ?? 1)));
}

function clear_cart(): void
{
    if (function_exists('is_logged_in') && is_logged_in()) {
        $stmt = db()->prepare('DELETE FROM cart_items WHERE user_id = ?');
        $stmt->execute([current_user()['id']]);
        return;
    }

    unset($_SESSION['guest_cart']);
}

function merge_guest_cart_into_user(int $userId): void
{
    $guestCart = $_SESSION['guest_cart'] ?? [];
    if (!$guestCart) {
        return;
    }

    foreach ($guestCart as $listingId => $quantity) {
        $stmt = db()->prepare('SELECT id, quantity FROM cart_items WHERE user_id = ? AND listing_id = ?');
        $stmt->execute([$userId, (int) $listingId]);
        $existing = $stmt->fetch();
        $listing = get_listing((int) $listingId);
        if (!$listing) {
            continue;
        }
        $maxQty = (int) $listing['quantity'];
        if ($existing) {
            $newQty = min((int) $existing['quantity'] + (int) $quantity, $maxQty);
            db()->prepare('UPDATE cart_items SET quantity = ? WHERE id = ?')->execute([$newQty, $existing['id']]);
        } else {
            db()->prepare('INSERT INTO cart_items (user_id, listing_id, quantity) VALUES (?, ?, ?)')->execute([$userId, (int) $listingId, min((int) $quantity, $maxQty)]);
        }
    }

    unset($_SESSION['guest_cart']);
}

function mark_order_paid(int $orderId, string $paymentReference = 'SIMULATED'): void
{
    $pdo = db();
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? FOR UPDATE');
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if (!$order || $order['status'] === 'paid') {
        $pdo->commit();
        return;
    }

    $pdo->prepare('UPDATE orders SET status = ?, payment_reference = ? WHERE id = ?')
        ->execute(['paid', $paymentReference, $orderId]);

    $items = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
    $items->execute([$orderId]);
    foreach ($items->fetchAll() as $item) {
        $pdo->prepare('UPDATE listings SET quantity = GREATEST(quantity - ?, 0), status = IF(GREATEST(quantity - ?, 0) = 0, "sold", status) WHERE id = ?')
            ->execute([(int) $item['quantity'], (int) $item['quantity'], (int) $item['listing_id']]);
        $pdo->prepare('UPDATE order_items SET item_status = ? WHERE id = ?')->execute(['paid', (int) $item['id']]);
    }

    $pdo->prepare('INSERT INTO payments (order_id, provider, payment_reference, amount, status) VALUES (?, ?, ?, ?, ?)')
        ->execute([$orderId, 'PayFast Sandbox', $paymentReference, $order['total_amount'], 'complete']);

    $pdo->commit();
}
