<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$orderId = (int) ($_GET['order_id'] ?? $_POST['m_payment_id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM orders WHERE id = ? AND buyer_id = ?');
$stmt->execute([$orderId, current_user()['id']]);
$order = $stmt->fetch();

if (!$order) {
    flash('error', 'Order not found.');
    redirect('dashboard.php');
}

$reference = isset($_GET['simulate']) ? 'LOCAL-SIM-' . $orderId : 'PAYFAST-RETURN-' . $orderId;
mark_order_paid($orderId, $reference);
flash('success', 'Payment completed. Order and listing records were updated.');
redirect('dashboard.php');

