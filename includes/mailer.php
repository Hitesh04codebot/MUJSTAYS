<?php
// includes/mailer.php — Email Sending Wrapper (uses PHPMailer if available, else mail())

require_once __DIR__ . '/../config/config.php';

/**
 * Send an email. Uses PHPMailer if available, else native mail().
 */
function send_email(string $to, string $to_name, string $subject, string $html_body): bool {
    // Try PHPMailer first
    if (file_exists(__DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
        return send_via_phpmailer($to, $to_name, $subject, $html_body);
    }
    // LOGGING MOCK (Local Dev): Always log mail to file so developer can see it on localhost
    $log_path = __DIR__ . '/../logs/email_log.txt';
    $log_entry = "========================================\n"
               . "[" . date('Y-m-d H:i:s') . "] TO: {$to_name} <{$to}>\n"
               . "SUBJECT: {$subject}\n"
               . "BODY: " . strip_tags($html_body) . "\n"
               . "========================================\n\n";
    file_put_contents($log_path, $log_entry, FILE_APPEND);

    // Fallback to mail()
    $headers = "From: " . SITE_NAME . " <" . SMTP_USER . ">\r\n";
    $headers .= "Reply-To: " . SITE_EMAIL . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    return mail($to, $subject, $html_body, $headers);
}

function send_via_phpmailer(string $to, string $to_name, string $subject, string $html_body): bool {
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;
        $mail->setFrom(SMTP_USER, SITE_NAME);
        $mail->addAddress($to, $to_name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html_body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $e->getMessage());
        return false;
    }
}

/** Email Templates **/

function email_template(string $title, string $body_html): string {
    return <<<HTML
    <!DOCTYPE html>
    <html><head><meta charset="UTF-8">
    <style>
      body{font-family:Inter,Arial,sans-serif;background:#f8fafc;margin:0;padding:0}
      .wrap{max-width:600px;margin:30px auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.1)}
      .header{background:linear-gradient(135deg,#1A3C5E,#2E86AB);padding:30px;text-align:center}
      .header h1{color:#fff;margin:0;font-size:24px}
      .header p{color:rgba(255,255,255,.8);margin:5px 0 0}
      .body{padding:30px}
      .body h2{color:#1A3C5E;margin-top:0}
      .otp-box{background:#f0f7ff;border:2px dashed #2E86AB;border-radius:8px;padding:20px;text-align:center;margin:20px 0}
      .otp{font-size:36px;font-weight:700;letter-spacing:8px;color:#1A3C5E}
      .btn{display:inline-block;background:#2E86AB;color:#fff!important;padding:14px 30px;border-radius:8px;text-decoration:none;font-weight:600;margin:15px 0}
      .footer{background:#f8fafc;padding:20px;text-align:center;color:#666;font-size:13px;border-top:1px solid #e2e8f0}
    </style></head><body>
    <div class="wrap">
      <div class="header">
        <h1>🏠 MUJSTAYS</h1>
        <p>PG Discovery & Booking Platform for MUJ Students</p>
      </div>
      <div class="body">
        <h2>{$title}</h2>
        {$body_html}
      </div>
      <div class="footer">
        <p>© 2025 MUJSTAYS. Built for MUJ Students, Jaipur.<br>
        If you didn't request this email, please ignore it.</p>
      </div>
    </div>
    </body></html>
    HTML;
}

function send_otp_email(string $to, string $name, string $otp): bool {
    $body = <<<HTML
    <p>Hi <strong>{$name}</strong>,</p>
    <p>Here is your verification code for MUJSTAYS:</p>
    <div class="otp-box">
      <div class="otp">{$otp}</div>
      <p style="color:#666;margin:10px 0 0;font-size:14px">Valid for <strong>10 minutes</strong></p>
    </div>
    <p>Enter this code on the verification page to activate your account.</p>
    HTML;
    return send_email($to, $name, 'Your MUJSTAYS verification code is ' . $otp, email_template('Email Verification', $body));
}

function send_booking_confirmation_email(string $to, string $name, array $booking): bool {
    $pg = htmlspecialchars($booking['pg_title']);
    $room = htmlspecialchars(ucfirst($booking['room_type']));
    $date = htmlspecialchars($booking['move_in_date']);
    $amt  = format_currency($booking['total_amount']);
    $body = <<<HTML
    <p>Hi <strong>{$name}</strong>,</p>
    <p>🎉 Your booking has been <strong>confirmed</strong>!</p>
    <table style="width:100%;border-collapse:collapse;margin:15px 0">
      <tr style="background:#f0f7ff"><td style="padding:10px;font-weight:600">PG Name</td><td style="padding:10px">{$pg}</td></tr>
      <tr><td style="padding:10px;font-weight:600">Room Type</td><td style="padding:10px">{$room}</td></tr>
      <tr style="background:#f0f7ff"><td style="padding:10px;font-weight:600">Move-in Date</td><td style="padding:10px">{$date}</td></tr>
      <tr><td style="padding:10px;font-weight:600">Total Amount</td><td style="padding:10px">{$amt}</td></tr>
    </table>
    <a href="HTML . BASE_URL . /user/bookings.php" class="btn">View Booking Details</a>
    HTML;
    return send_email($to, $name, 'Booking Confirmed — ' . $booking['pg_title'], email_template('Booking Confirmed!', $body));
}

function send_booking_rejection_email(string $to, string $name, string $pg_name, string $reason): bool {
    $pg = htmlspecialchars($pg_name);
    $r  = htmlspecialchars($reason);
    $body = <<<HTML
    <p>Hi <strong>{$name}</strong>,</p>
    <p>We're sorry, your booking request for <strong>{$pg}</strong> was not accepted by the owner.</p>
    <p><strong>Reason:</strong> {$r}</p>
    <a href="HTML . BASE_URL . /explore.php" class="btn">Explore Other PGs</a>
    HTML;
    return send_email($to, $name, 'Booking Update — ' . $pg_name, email_template('Booking Not Confirmed', $body));
}

function send_password_reset_email(string $to, string $name, string $otp): bool {
    $body = <<<HTML
    <p>Hi <strong>{$name}</strong>,</p>
    <p>We received a request to reset your MUJSTAYS password.</p>
    <div class="otp-box">
      <p style="color:#666;margin:0 0 10px;font-size:14px">Your password reset OTP:</p>
      <div class="otp">{$otp}</div>
      <p style="color:#666;margin:10px 0 0;font-size:14px">Valid for <strong>10 minutes</strong></p>
    </div>
    <p>If you did not request a password reset, you can safely ignore this email.</p>
    HTML;
    return send_email($to, $name, 'Reset your MUJSTAYS password', email_template('Password Reset Request', $body));
}

/**
 * Send OTP via SMS. 
 * NOTE: For production, plug in your preferred SMS API (Twilio, Msg91, Fast2SMS, etc.)
 */
function send_otp_sms(string $phone, string $otp): bool {
    if (!$phone) return false;

    // Clean phone number
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    $message = "Your MUJSTAYS OTP is {$otp}. Ref: " . substr(md5(time()), 0, 4);
    
    // LOGGING MOCK: We log the SMS into a file so you can see it working locally without a paid API.
    // In a real scenario, you would use curl here to hit an SMS Gateway.
    $log_entry = "[" . date('Y-m-d H:i:s') . "] TO: {$phone} | MSG: {$message}\n";
    file_put_contents(__DIR__ . '/../logs/sms_log.txt', $log_entry, FILE_APPEND);

    /* 
    Example for real SMS Gateway (Fast2SMS for India):
    $url = "https://www.fast2sms.com/dev/bulkV2?authorization=YOUR_API_KEY&route=otp&variables_values=".$otp."&numbers=".$phone;
    $response = file_get_contents($url);
    return strpos($response, '"status":true') !== false;
    */

    return true; // Return true as mock success
}


function send_booking_request_email(string $to, string $name, string $pg_title, string $move_in, float $total, int $booking_id): bool {
    $pg = htmlspecialchars($pg_title);
    $amt = format_currency($total);
    $body = <<<HTML
    <p>Hi <strong>{$name}</strong>,</p>
    <p>Your booking request for <strong>{$pg}</strong> has been successfully sent to the owner!</p>
    <p>We'll notify you as soon as the owner accepts or declines your request (usually within 24 hours).</p>
    <p><strong>Move-in Date:</strong> {$move_in}<br>
    <strong>Total Amount:</strong> {$amt}</p>
    <a href="HTML . BASE_URL . /user/bookings.php" class="btn">View Bookings</a>
    HTML;
    return send_email($to, $name, 'Booking Request Sent — ' . $pg_title, email_template('Booking Request Pending', $body));
}
