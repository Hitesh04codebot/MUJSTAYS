<?php
// api/payment-callback.php — Handle client-side success response
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

$razorpay_order_id = $data['razorpay_order_id'] ?? '';
$razorpay_payment_id = $data['razorpay_payment_id'] ?? '';
$razorpay_signature = $data['razorpay_signature'] ?? '';

if (!$razorpay_order_id || !$razorpay_payment_id || !$razorpay_signature) {
    echo json_encode(['error' => 'Incomplete payment response.']);
    exit;
}

try {
    // Verify signature
    if (verify_razorpay_signature($razorpay_order_id, $razorpay_payment_id, $razorpay_signature)) {
        // Find initiated payment
        $stmt = $pdo->prepare("SELECT id, booking_id, amount FROM payments WHERE gateway_order_id = ? AND status = 'initiated'");
        $stmt->execute([$razorpay_order_id]);
        $p = $stmt->fetch();
        
        if ($p) {
            // Update payment status
            $pdo->prepare("UPDATE payments SET status = 'success', gateway_payment_id = ?, paid_at = NOW() WHERE id = ?")
                ->execute([$razorpay_payment_id, $p['id']]);
            
            // Update booking status - student has paid advance
            $pdo->prepare("UPDATE bookings SET advance_paid = ?, status = 'confirmed' WHERE id = ?")
                ->execute([$p['amount'], $p['booking_id']]);
            
            // Notify owner
            $stmt_o = $pdo->prepare("SELECT owner_id FROM bookings WHERE id = ?");
            $stmt_o->execute([$p['booking_id']]);
            $owner_id = $stmt_o->fetchColumn();
            
            create_notification($pdo, $owner_id, 'payment_received', 'Payment Received! 🎉', 'Student has paid the security deposit for your PG. The booking is now confirmed.', '/owner/bookings.php');
            
            echo json_encode(['success' => true, 'message' => 'Payment successful! Your booking is confirmed.']);
        } else {
            echo json_encode(['error' => 'Payment record not found.']);
        }
    } else {
        // Potential tampering
        $pdo->prepare("UPDATE payments SET status = 'failed' WHERE gateway_order_id = ? AND status = 'initiated'")
            ->execute([$razorpay_order_id]);
        
        echo json_encode(['error' => 'Invalid payment signature. Potential tampering detected.']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'System error: ' . $e->getMessage()]);
}
