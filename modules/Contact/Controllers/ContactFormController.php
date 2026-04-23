<?php
namespace App\Modules\Contact\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\App;
use App\Token;

/**
 * Class ContactFormController
 * Handles display and submission of the contact form.
 */
class ContactFormController
{
    /**
     * Display the contact form.
     */
    public function index()
    {
        $page_title = 'Contact Us';
        if (empty($_SESSION[PREFIX . 'csrf_token'])) {
            $_SESSION[PREFIX . 'csrf_token'] = Token::generate(32);
        }
        $csrf_token = $_SESSION[PREFIX . 'csrf_token'];
        include __DIR__ . '/../views/contact_form.php';
    }
    /**
     * Handle contact form submission and send email.
     */
    public function submit()
    {
        $page_title = 'Contact Us';
        $success = false;
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $tokenValid = isset($_POST['csrf_token']) && isset($_SESSION[PREFIX . 'csrf_token']) && hash_equals($_SESSION[PREFIX . 'csrf_token'], $_POST['csrf_token']);
                if (!$tokenValid) {
                    $error = 'Invalid CSRF token.';
                } else {
                    $name = trim($_POST['name'] ?? '');
                    $email = trim($_POST['email'] ?? '');
                    $phone = trim($_POST['phone'] ?? '');
                    $message = trim($_POST['message'] ?? '');
                    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                    if ($name && $email && $message) {
                        $mail = new PHPMailer(true);
                        try {
                            $mail->isSMTP();
                            $mailConfig = App::config('mail');
                            $mail->Host = $mailConfig['host'];
                            $mail->SMTPAuth = true;
                            $mail->Username = $mailConfig['username'];
                            $mail->Password = $mailConfig['password'];
                            $mail->SMTPSecure = $mailConfig['encryption'];
                            $mail->Port = $mailConfig['port'];
                            $mail->setFrom(App::config('admin_email'), $name);
                            $mail->addAddress(App::config('form_email'));
                            $mail->Subject = 'Contact Form Submission';
                            $mail->Body = "Name: $name\nEmail: $email\nPhone: $phone\nIP: $ip\nMessage:\n$message";
                            $mail->send();
                            $success = true;
                        } catch (Exception $e) {
                            $error = 'Mailer Error: ' . $mail->ErrorInfo;
                        }
                    } else {
                        $error = 'Please fill in all required fields.';
                    }
                }
            } catch (\Throwable $e) {
                $error = 'Server error. Please try again later.';
            }
        }
        include __DIR__ . '/../views/contact_form.php';
    }
}
