<?php
namespace App\Modules\User\Controllers;

use App\DB;
use App\TokenManager;
use App\Modules\User\Helpers\CmsHelper;

/**
 * User Password Reset Controller
 *
 * Handles password reset functionality with token validation
 * Processes password reset requests and validates reset tokens
 */
class UserResetController
{
    /**
     * Handle password reset requests
     *
     * Validates reset tokens and processes password updates
     * Includes security validation and proper error handling
     *
     * @return void
     */
    public function index()
    {
        try {
                require_once dirname(__DIR__, 4) . '/bootstrap.php';
                global $config;
            $localConfig = include dirname(__DIR__, 3) . '/app/config.php';
            
            // Check if user is already logged in
            $prefix = $config['session_prefix'] ?? 'app_';
            if (isset($_SESSION[$prefix . 'user_id'])) {
                // Use CmsHelper for smart redirect based on CMS availability
                $isAdmin = isset($_SESSION[$prefix . 'admin']) && $_SESSION[$prefix . 'admin'] > 0;
                $redirect = CmsHelper::getLoggedInRedirect($isAdmin);
                header('Location: ' . $redirect);
                exit;
            }
            
            $error = '';
            $success = '';
        // Validate token and get user_id
            $db = new DB($config);
            $token = $_GET['token'] ?? '';
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $token = $_POST['reset_token'] ?? $token;
            }
            if (empty($token)) {
                $error = 'Invalid or missing token.';
            } else {
                $sql = "SELECT `id`, `user_id`, `key`, `date` FROM `reset` WHERE `key` = ?";
                $rows = $db->fetchAll($sql, [$token]);
                if (count($rows) === 0) {
                    $error = 'Invalid or expired token.';
                } else {
                    $userId = $rows[0]['user_id'];
                    $expiry = $rows[0]['date'];
                    if (strtotime($expiry) < time()) {
                        $error = 'This reset link has expired.';
                    } else {
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $tm = new TokenManager();
                            $result = $tm->verify($_POST['token'] ?? '');
                            if ($result['status'] !== 'success') {
                                $error = 'Invalid CSRF token. Please refresh and try again.';
                            } else {
                                $pwd = $_POST['pwd'] ?? '';
                                $pwd2 = $_POST['pwd2'] ?? '';
                                if ($pwd !== $pwd2) {
                                    $error = 'Passwords do not match.';
                                } elseif (strlen($pwd) < 6) {
                                    $error = 'Password must be at least 6 characters.';
                                } else {
                                    // Update password in DB, invalidate token
                                    $success = 'Your password has been reset.';
                                    $hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);
                                    $sql = "UPDATE users SET pwd = ? WHERE id = ?";
                                    $db->query($sql, [$hashedPwd, $userId]);
                                    // Invalidate the token
                                    $sql = "DELETE FROM reset WHERE `key` = ?";
                                    $db->query($sql, [$token]);
                                }
                            }
                        }
                    }
                }
            }
            $viewPath = CmsHelper::getViewPath('user/reset.php', __DIR__ . '/../views/reset.php');
            include $viewPath;
        } catch (\Exception $e) {
            $error = 'An unexpected error occurred. Please try again.';
            $success = '';
            $viewPath = CmsHelper::getViewPath('user/reset.php', __DIR__ . '/../views/reset.php');
            include $viewPath;
        }
    }
}
