<?php
require_once __DIR__ . '/includes/init.php';

http_response_code(200);

$orderId = (int) ($_POST['m_payment_id'] ?? 0);
$status = strtoupper((string) ($_POST['payment_status'] ?? ''));
$reference = (string) ($_POST['pf_payment_id'] ?? ('ITN-' . $orderId));

if ($orderId > 0 && $status === 'COMPLETE') {
    mark_order_paid($orderId, $reference);
}

echo 'OK';

