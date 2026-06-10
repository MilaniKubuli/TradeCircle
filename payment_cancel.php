<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$orderId = (int) ($_GET['order_id'] ?? 0);
$stmt = db()->prepare('UPDATE orders SET status = ? WHERE id = ? AND buyer_id = ? AND status = ?');
$stmt->execute(['cancelled', $orderId, current_user()['id'], 'payment_pending']);
flash('warning', 'The order was cancelled.');
redirect('dashboard.php');

