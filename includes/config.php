<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_name('tradecircle_session');
    session_start();
}

date_default_timezone_set('Africa/Johannesburg');

define('APP_NAME', 'Trade Circle');
define('APP_TAGLINE', 'A trusted consumer-to-consumer marketplace for local trade.');

$hostName = $_SERVER['HTTP_HOST'] ?? 'localhost';
$hostOnly = strtolower(explode(':', $hostName)[0]);
$isProduction = !in_array($hostOnly, ['localhost', '127.0.0.1', '::1'], true);

define('BASE_URL', $isProduction ? '' : '/tradecircle');
define('APP_URL', ($isProduction ? 'https://' . $hostName : 'http://localhost/tradecircle'));

define('DB_HOST', $isProduction ? 'sql102.infinityfree.com' : '127.0.0.1');
define('DB_PORT', $isProduction ? 3306 : 3307);
define('DB_NAME', $isProduction ? 'if0_42104906_tradecircle_db' : 'tradecircle');
define('DB_USER', $isProduction ? 'if0_42104906' : 'root');
define('DB_PASS', $isProduction ? 'nanishiteruno' : '');
define('DB_CHARSET', 'utf8mb4');

define('PAYFAST_SANDBOX', true);
define('PAYFAST_MERCHANT_ID', '10049261');
define('PAYFAST_MERCHANT_KEY', 'fcjxj6b6wv9lg');
define('PAYFAST_PASSPHRASE', 'Nanishiteruno1/');

define('DEFAULT_LISTING_IMAGE', 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=900&q=80');
