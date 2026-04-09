<?php
// api/create-order.php — Create Razorpay Order
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';
require_once '../includes/payment_gateway.php';

header('Content-Type: application/json');

if (!is_logged_in() || current_role() !== 'student') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$amount = (int)($data['amount'] ?? 0); // Amount in Paise (INR * 100)
$booking_id = (int)($data['booking_id'] ?? 0);

if ($amount < 50000) { // Min 500 INR
    echo json_encode(['error' => 'Invalid amount. Minimum ₹500 required for online booking.']);
    exit;
}

try {
    $order_id = create_razorpay_order($amount, [
        'booking_id' => $booking_id,
        'student_id' => current_user_id()
    ]);
    
    if ($order_id) {
        // Record initiated payment
        $pdo->prepare("INSERT INTO payments (booking_id, payer_id, amount, gateway_order_id, commission_amount, status) VALUES (?, ?, ?, ?, ?, 'initiated')")
            ->execute([$booking_id, current_user_id(), $amount / 100, $order_id, ($amount / 100) * (COMMISSION_RATE/100)]);
        
        echo json_encode(['success' => true, 'order_id' => order_id]);
    } else {
        echo json_encode(['error' => 'Failed to create payment order.']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Gateway error: ' . $e->getMessage()]);
}
