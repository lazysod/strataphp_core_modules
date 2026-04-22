<?php
namespace App\Modules\User\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\TokenManager;
use App\DB;
use App\Modules\User\Helpers\CmsHelper;

/**
 * User Password Reset Request Controller
 *
 * Handles password reset request processing, validates user email,
 * generates secure reset tokens, and sends password reset emails
 *
 * @package StrataPHP\Modules\User\Controllers
 * @author StrataPHP Framework
 * @version 1.0.0
 */
class UserResetRequestController
{
    /**
     * Process password reset requests
     *
     * @return void
     */
    public function index()
    {
        try {
            require_once dirname(__DIR__, 4) . '/bootstrap.php';
            global $config;
            // Check if user is already logged in
            $prefix = $config['session_prefix'] ?? 'app_';
            if (isset($_SESSION[$prefix . 'user_id'])) {
                $isAdmin = isset($_SESSION[$prefix . 'admin']) && $_SESSION[$prefix . 'admin'] > 0;
                $redirect = CmsHelper::getLoggedInRedirect($isAdmin);
                header('Location: ' . $redirect);
                exit;
            }
            $error = '';
            $success = '';
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $tm = new TokenManager();
                $result = $tm->verify($_POST['token'] ?? '');
                if ($result['status'] !== 'success') {
                    // Regenerate token and reload form with gentle message
                    $tm->renew();
                    $error = 'Your session expired or the form was open too long. The form has been refreshed—please try again.';
                    $viewPath = CmsHelper::getViewPath('user/reset_request.php', __DIR__ . '/../views/reset_request.php');
                    include $viewPath;
                    return;
                } else {
                    $email = trim($_POST['email'] ?? '');
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $error = 'Invalid email address.';
                    } else {
                        $db = new DB($config);
                        $sql = "SELECT id FROM users WHERE email = ?";
                        $rows = $db->fetchAll($sql, [$email]);
                        if (count($rows) > 0) {
                            $userId = $rows[0]['id'];
                            $token = bin2hex(random_bytes(32));
                            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                            $sql = "INSERT INTO `reset`(`user_id`, `key`, `date`) VALUES (?, ?, ?)";
                            $stmt = $db->query($sql, [$userId, $token, $expiry]);
                            if (!$stmt || $db->affectedRows($stmt) === 0) {
                                $error = 'Failed to generate reset token. Please try again.';
                            }
                            $mail = new PHPMailer(true);
                            try {
                                $mail->isSMTP();
                                $mail->Host = $config['mail']['host'];
                                $mail->SMTPAuth = true;
                                $mail->Username = $config['mail']['username'];
                                $mail->Password = $config['mail']['password'];
                                $mail->SMTPSecure = $config['mail']['encryption'];
                                $mail->Port = $config['mail']['port'];
                                $mail->setFrom($config['mail']['from_email'], $config['site_name']);
                                $mail->addAddress($email);
                                $mail->Subject = 'Password Reset Request';
                                $resetLink = $config['base_url'] . "/user/reset?token=$token";
                                $mail->Body = "Click the following link to reset your password: $resetLink\nIf you did not request this, please ignore.";
                                $mail->send();
                            } catch (Exception $e) {
                                $error = 'Email failed: ' . $mail->ErrorInfo;
                            }
                        }
                        $success = 'If your email is registered, a reset link has been sent.';
                    }
                }
            }
            $viewPath = CmsHelper::getViewPath('user/reset_request.php', __DIR__ . '/../views/reset_request.php');
            include $viewPath;
        } catch (\Exception $e) {
            $error = 'An unexpected error occurred. Please try again.';
            $success = '';
            $viewPath = CmsHelper::getViewPath('user/reset_request.php', __DIR__ . '/../views/reset_request.php');
            include $viewPath;
        }
    }
}
