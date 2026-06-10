<?php
require_once __DIR__ . '/includes/init.php';
require_role('seller');

$user = current_user();
$listingId = (int) ($_GET['id'] ?? 0);
$listing = null;

if ($listingId > 0) {
    $stmt = db()->prepare('SELECT * FROM listings WHERE id = ? AND seller_id = ?');
    $stmt->execute([$listingId, $user['id']]);
    $listing = $stmt->fetch();
    if (!$listing) {
        flash('error', 'Listing not found.');
        redirect('seller_dashboard.php');
    }
}

$pageTitle = $listing ? 'Edit Listing' : 'Create Listing';
$categoryRows = categories();
$genreMap = category_genres();
$selectedCategoryId = (int) ($_POST['category_id'] ?? $listing['category_id'] ?? ($categoryRows[0]['id'] ?? 0));
$selectedCategoryName = '';
foreach ($categoryRows as $categoryRow) {
    if ((int) $categoryRow['id'] === $selectedCategoryId) {
        $selectedCategoryName = $categoryRow['name'];
        break;
    }
}
$selectedGenre = (string) ($_POST['genre'] ?? $listing['genre'] ?? '');

if (is_post()) {
    verify_csrf();
    $data = [
        'category_id' => (int) $_POST['category_id'],
        'genre' => trim((string) ($_POST['genre'] ?? '')),
        'title' => trim((string) $_POST['title']),
        'description' => trim((string) $_POST['description']),
        'price' => (float) $_POST['price'],
        'item_condition' => (string) $_POST['item_condition'],
        'quantity' => max(0, (int) $_POST['quantity']),
        'status' => (string) $_POST['status'],
        'image_path' => trim((string) ($listing['image_path'] ?? '')),
        'location' => trim((string) $_POST['location']),
    ];
    $uploadOk = true;

    if (isset($_FILES['listing_image']) && $_FILES['listing_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['listing_image']['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'The image could not be uploaded. Please try another file.');
            $uploadOk = false;
        } elseif ($_FILES['listing_image']['size'] > 4 * 1024 * 1024) {
            flash('error', 'Listing images must be smaller than 4MB.');
            $uploadOk = false;
        } else {
            $imageInfo = getimagesize($_FILES['listing_image']['tmp_name']);
            $extension = strtolower(pathinfo((string) $_FILES['listing_image']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!$imageInfo || !in_array($extension, $allowedExtensions, true)) {
                flash('error', 'Upload a valid image file: JPG, PNG, WEBP, or GIF.');
                $uploadOk = false;
            } else {
                $uploadDir = __DIR__ . '/uploads/listings';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }
                $fileName = 'listing_' . $user['id'] . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
                $targetPath = $uploadDir . '/' . $fileName;
                if (move_uploaded_file($_FILES['listing_image']['tmp_name'], $targetPath)) {
                    $data['image_path'] = 'uploads/listings/' . $fileName;
                } else {
                    flash('error', 'The image could not be saved.');
                    $uploadOk = false;
                }
            }
        }
    }

    if ($data['title'] === '' || $data['description'] === '' || $data['price'] <= 0) {
        flash('error', 'Title, description, and a valid price are required.');
    } elseif (!in_array($data['status'], ['available', 'pending', 'sold', 'cancelled', 'hidden'], true)) {
        flash('error', 'Invalid listing status.');
    } elseif (!$uploadOk) {
        // The flash message was set during upload validation.
    } else {
        if ($listing) {
            $stmt = db()->prepare(
                'UPDATE listings SET category_id = ?, genre = ?, title = ?, description = ?, price = ?, item_condition = ?, quantity = ?, status = ?, image_path = ?, location = ? WHERE id = ? AND seller_id = ?'
            );
            $stmt->execute([
                $data['category_id'], $data['genre'], $data['title'], $data['description'], $data['price'], $data['item_condition'],
                $data['quantity'], $data['status'], $data['image_path'], $data['location'], $listing['id'], $user['id'],
            ]);
            flash('success', 'Listing updated.');
        } else {
            $stmt = db()->prepare(
                'INSERT INTO listings (seller_id, category_id, genre, title, description, price, item_condition, quantity, status, image_path, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $user['id'], $data['category_id'], $data['genre'], $data['title'], $data['description'], $data['price'], $data['item_condition'],
                $data['quantity'], $data['status'], $data['image_path'], $data['location'],
            ]);
            flash('success', 'Listing created.');
        }
        redirect('seller_dashboard.php');
    }
}

$value = fn(string $key, mixed $default = '') => e($_POST[$key] ?? $listing[$key] ?? $default);
require_once __DIR__ . '/includes/header.php';
?>
<section class="section-shell narrow">
    <div class="section-heading">
        <h1><?= $listing ? 'Edit listing' : 'Create listing' ?></h1>
    </div>
    <form class="form-card" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <script type="application/json" data-genre-map><?= json_encode($genreMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?></script>
        <label>Category
            <select name="category_id" required data-category-filter>
                <?php foreach ($categoryRows as $category): ?>
                    <option value="<?= (int) $category['id'] ?>" data-category-name="<?= e($category['name']) ?>" <?= (int) ($_POST['category_id'] ?? $listing['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>>
                        <?= e($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Genre / Type
            <select name="genre" data-genre-filter data-selected="<?= e($selectedGenre) ?>">
                <option value="">Choose type</option>
                <?php foreach (($genreMap[$selectedCategoryName] ?? []) as $genre): ?>
                    <option value="<?= e($genre) ?>" <?= $selectedGenre === $genre ? 'selected' : '' ?>><?= e($genre) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Title <input type="text" name="title" value="<?= $value('title') ?>" required></label>
        <label>Description <textarea name="description" rows="6" required><?= $value('description') ?></textarea></label>
        <label>Price <input type="number" name="price" step="0.01" min="1" value="<?= $value('price', '100.00') ?>" required></label>
        <label>Condition
            <select name="item_condition">
                <?php foreach (['new', 'like_new', 'good', 'fair', 'service'] as $condition): ?>
                    <option value="<?= e($condition) ?>" <?= ($_POST['item_condition'] ?? $listing['item_condition'] ?? 'good') === $condition ? 'selected' : '' ?>><?= e(status_label($condition)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Quantity <input type="number" name="quantity" min="0" value="<?= $value('quantity', '1') ?>"></label>
        <label>Status
            <select name="status">
                <?php foreach (['available', 'pending', 'sold', 'cancelled', 'hidden'] as $status): ?>
                    <option value="<?= e($status) ?>" <?= ($_POST['status'] ?? $listing['status'] ?? 'available') === $status ? 'selected' : '' ?>><?= e(status_label($status)) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Upload image <input type="file" name="listing_image" accept="image/*" data-image-input></label>
        <div class="upload-preview">
            <?php if ($value('image_path')): ?>
                <img src="<?= e($value('image_path')) ?>" alt="Current listing image" data-image-preview>
            <?php else: ?>
                <img src="" alt="Listing image preview" data-image-preview hidden>
            <?php endif; ?>
        </div>
        <label>Location <input type="text" name="location" value="<?= $value('location', $user['location']) ?>"></label>
        <button class="button button-primary" type="submit">Save listing</button>
    </form>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
