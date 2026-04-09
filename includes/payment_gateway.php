<?php
// includes/payment_gateway.php — Razorpay Integration

require_once __DIR__ . '/../config/config.php';

/**
 * Create a Razorpay order for a booking
 * Returns order_id on success, false on failure
 */
function create_razorpay_order(int $amount_rupees, string $receipt, string $notes_pg = ''): array|false {
    $url     = 'https://api.razorpay.com/v1/orders';
    $payload = json_encode([
        'amount'          => $amount_rupees * 100, // INR paise
        'currency'        => 'INR',
        'receipt'         => $receipt,
        'notes'           => ['pg_name' => $notes_pg],
        'payment_capture' => 1,
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_USERPWD        => RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    ]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        error_log('Razorpay Order Error: ' . $response);
        return false;
    }

    return json_decode($response, true);
}

/**
 * Verify Razorpay payment signature
 * Returns true if valid, false otherwise
 */
function verify_razorpay_payment(string $order_id, string $payment_id, string $signature): bool {
    $expected = hash_hmac('sha256', $order_id . '|' . $payment_id, RAZORPAY_KEY_SECRET);
    return hash_equals($expected, $signature);
}
