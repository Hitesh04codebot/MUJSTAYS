<?php
// api/book-pg.php — Create booking
session_start();
require_once '../config/config.php';
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_once '../includes/helpers.php';
require_once '../includes/mailer.php';

header('Content-Type: application/json');
if (!is_logged_in() || current_role()!=='student') { echo json_encode(['error'=>'Unauthorized']); exit; }
if (!verify_csrf()) { echo json_encode(['error'=>'Invalid CSRF']); exit; }

$pg_id    = (int)($_POST['pg_id']??0);
$rt_id    = (int)($_POST['room_type_id']??0);
$mov_in   = sanitize($_POST['move_in_date']??'');
$duration = max(1,(int)($_POST['duration_months']??1));
$pay_opt  = in_array($_POST['payment_option']??'',['online','offline'])?$_POST['payment_option']:'offline';
$uid      = current_user_id();

if (!$pg_id||!$rt_id||!$mov_in) { echo json_encode(['error'=>'Missing required fields.']); exit; }
if (strtotime($mov_in)===false||strtotime($mov_in)<strtotime('+2 days')) { echo json_encode(['error'=>'Move-in date must be at least 3 days from today.']); exit; }

// Validate room
$rt=$pdo->prepare("SELECT rt.*,p.owner_id,p.title AS pg_title FROM room_types rt JOIN pg_listings p ON p.id=rt.pg_id WHERE rt.id=? AND rt.pg_id=? AND p.status='approved'");
$rt->execute([$rt_id,$pg_id]); $rt=$rt->fetch();
if (!$rt) { echo json_encode(['error'=>'Invalid room selection.']); exit; }
if ($rt['available_beds']<=0) { echo json_encode(['error'=>'This room type is full.']); exit; }

// Prevent duplicate pending booking
$dup=$pdo->prepare("SELECT id FROM bookings WHERE student_id=? AND pg_id=? AND status IN ('pending','confirmed')");
$dup->execute([$uid,$pg_id]);
if ($dup->fetch()) { echo json_encode(['error'=>'You already have an active booking for this PG.']); exit; }

$total = ($rt['price_per_month']*$duration)+$rt['security_deposit'];
$advance= $pay_opt==='online' ? $rt['security_deposit'] : 0;

$pdo->prepare("INSERT INTO bookings (student_id,pg_id,room_type_id,owner_id,move_in_date,duration_months,total_amount,advance_paid,booking_type,status) VALUES (?,?,?,?,?,?,?,?,?,'pending')")
    ->execute([$uid,$pg_id,$rt_id,$rt['owner_id'],$mov_in,$duration,$total,$advance,$pay_opt]);
$booking_id=$pdo->lastInsertId();

// Notify owner
create_notification($pdo,$rt['owner_id'],'booking_request','New Booking Request 🔔','A student has sent a booking request for "'.$rt['pg_title'].'". Review it now.','/owner/bookings.php');

// Send request confirmation email
$student=$pdo->prepare("SELECT * FROM users WHERE id=?"); $student->execute([$uid]); $student=$student->fetch();
send_booking_request_email($student['email'],$student['name'],$rt['pg_title'],$mov_in,$total,$booking_id);

echo json_encode(['success'=>true,'booking_id'=>$booking_id,'message'=>'Booking request sent! The owner will confirm within 24 hours.']);
