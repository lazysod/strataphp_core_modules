<?php
namespace App\Modules\User\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Email Test Controller
 *
 * Provides email testing functionality for administrators to verify
 * SMTP configuration and email delivery
 *
 * @package StrataPHP\Modules\User\Controllers
 * @author StrataPHP Framework
 * @version 1.0.0
 */
class EmailTestController
{
    /**
     * Display email test form and handle test email sending
     *
     * @return void
     */
    public function index()
    {
        require_once dirname(__DIR__, 3) . '/app/bootstrap.php';
        include_once dirname(__DIR__, 3) . '/vendor/autoload.php';
        global $config;
        $error = '';
        $success = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $to = trim($_POST['to'] ?? '');
            $subject = trim($_POST['subject'] ?? 'Test Email');
            $body = trim($_POST['body'] ?? '');
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                $error = 'Invalid recipient email.';
            } else {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = $config['mail']['host'];
                    $mail->SMTPAuth = true;
                    $mail->Username = $config['mail']['username'];
                    $mail->Password = $config['mail']['password'];
                    $mail->SMTPSecure = $config['mail']['encryption'];
                    $mail->Port = $config['mail']['port'];
                    $mail->setFrom($config['admin_email'], $config['site_name']);
                    $mail->addAddress($to);
                    $mail->Subject = $subject;
                    $mail->Body = $body;
                    $mail->send();
                    $success = 'Test email sent successfully!';
                } catch (Exception $e) {
                    $error = 'Email failed: ' . $mail->ErrorInfo;
                }
            }
        }
        include __DIR__ . '/../views/email_test.php';
    }
}
