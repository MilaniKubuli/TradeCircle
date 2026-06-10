<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';

function payfast_generate_signature(array $data, ?string $passphrase = null): string
{
    $output = '';
    foreach ($data as $key => $value) {
        if ($value !== '') {
            $output .= $key . '=' . urlencode(trim((string) $value)) . '&';
        }
    }

    $parameterString = rtrim($output, '&');
    if ($passphrase !== null && $passphrase !== '') {
        $parameterString .= '&passphrase=' . urlencode(trim($passphrase));
    }

    return md5($parameterString);
}

function payfast_host(): string
{
    return PAYFAST_SANDBOX ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
}

function payfast_payment_data(array $order, array $user): array
{
    $nameParts = preg_split('/\s+/', trim($user['full_name']));
    $firstName = $nameParts[0] ?? 'TradeCircle';
    $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : 'Customer';

    $data = [
        'merchant_id' => trim(PAYFAST_MERCHANT_ID),
        'merchant_key' => trim(PAYFAST_MERCHANT_KEY),
        'return_url' => absolute_url('payment_success.php?order_id=' . (int) $order['id']),
        'cancel_url' => absolute_url('payment_cancel.php?order_id=' . (int) $order['id']),
        'notify_url' => absolute_url('payment_notify.php'),
        'name_first' => $firstName,
        'name_last' => $lastName,
        'email_address' => $user['email'],
        'm_payment_id' => (string) $order['id'],
        'amount' => number_format((float) $order['total_amount'], 2, '.', ''),
        'item_name' => 'TradeCircle order #' . (int) $order['id'],
        'item_description' => 'Consumer-to-consumer marketplace purchase',
    ];
    $data['signature'] = payfast_generate_signature($data, PAYFAST_PASSPHRASE);

    return $data;
}

function payfast_form(array $order, array $user): string
{
    $data = payfast_payment_data($order, $user);
    $html = '<form class="payment-form" action="https://' . e(payfast_host()) . '/eng/process" method="post">';
    foreach ($data as $name => $value) {
        $html .= '<input type="hidden" name="' . e($name) . '" value="' . e($value) . '">';
    }
    $html .= '<button class="button button-primary" type="submit">Pay with PayFast Sandbox</button>';
    $html .= '</form>';

    return $html;
}
