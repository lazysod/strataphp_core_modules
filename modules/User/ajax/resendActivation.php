<?php
/**
 * AJAX endpoint to resend activation email.
 * Handles CSRF protection, user lookup, activation key generation, and email sending.
 * Returns JSON response.
 */

// AJAX endpoint to resend activation email
header('Content-Type: application/json');
session_start();
require_once dirname(__DIR__, 3) . '/bootstrap.php';
use App\DB;
use App\User;
use PHPMailer\PHPMailer\PHPMailer;

// CSRF protection
if (empty($_POST['token']) || !isset($_SESSION['app_token']['token_id']) || !hash_equals($_SESSION['app_token']['token_id'], $_POST['token'])) {
    echo json_encode(['status' => 'fail', 'message' => 'Invalid or missing CSRF token.']);
    exit;
}

$email = trim($_POST['email'] ?? '');
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'fail', 'message' => 'Please enter a valid email address.']);
    exit;
}

$db = new DB($config);
$userModel = new User($db, $config);
$sql = "SELECT * FROM users WHERE email = ? AND active = 0";
$user = $db->fetch($sql, [$email]);
if (!$user) {
    echo json_encode(['status' => 'fail', 'message' => 'No inactive user found with that email.']);
    exit;
}
$userId = $user['id'];
// Generate new activation key and expiry
$activationKey = bin2hex(random_bytes(32));
$entryDate = date('Y-m-d H:i:s');
$expiryDate = date('Y-m-d H:i:s', strtotime('+1 day'));
$db->query("DELETE FROM user_activation WHERE user_id = ?", [$userId]);
$db->query("INSERT INTO user_activation (user_id, activation_key, entry_date, expiry_date) VALUES (?, ?, ?, ?)", [$userId, $activationKey, $entryDate, $expiryDate]);
// Send activation email
$activationLink = $config['base_url'] . "/user/activate?key=$activationKey";
if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mailConfig = $config['mail'];
        $mail->Host = $mailConfig['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $mailConfig['username'];
        $mail->Password = $mailConfig['password'];
        $mail->SMTPSecure = $mailConfig['encryption'];
        $mail->Port = $mailConfig['port'];
        $mail->setFrom($mailConfig['from_email'], $config['site_name'] ?? 'Site');
        $mail->addAddress($email);
        $mail->Subject = 'Activate Your Account';
        $mail->Body = "You requested a new activation link. Please activate your account by clicking the link below:\n$activationLink\nIf you did not register, please ignore this email.";
        $mail->send();
    } catch (\Exception $e) {
        echo json_encode(['status' => 'fail', 'message' => 'Failed to send activation email.']);
        exit;
    }
}
echo json_encode(['status' => 'success', 'message' => 'A new activation link has been sent to your email.']);
