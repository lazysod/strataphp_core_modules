<?php
namespace App\Modules\User\Controllers;

use App\TokenManager;
use App\DB;
use App\User;
use App\Token;
use App\Modules\User\Helpers\CmsHelper;

// Refactored as a class for router compatibility
/**
 * User Registration Controller
 *
 * Handles new user registration with validation and security
 * Includes email verification, password validation, and CSRF protection
 */
class UserRegisterController
{
    /**
     * Handle user registration requests
     *
     * Processes both GET (display form) and POST (register user) requests
     * Validates input data and creates new user accounts
     *
     * @return void
     */
    public function index()
    {
        try {
            require_once dirname(__DIR__, 3) . '/bootstrap.php';
            $config = require dirname(__DIR__, 3) . '/app/config.php';
            if (empty($config['modules']['User']) || empty($config['modules']['User']['enabled'])) {
                header('Location: /');
                exit;
            }
            // Check if user is already logged in
            $sessionPrefix = $config['session_prefix'] ?? 'app_';
            if (isset($_SESSION[$sessionPrefix . 'user_id'])) {
                // Use CmsHelper for smart redirect based on CMS availability
                $isAdmin = isset($_SESSION[$sessionPrefix . 'admin']) && $_SESSION[$sessionPrefix . 'admin'] > 0;
                $redirect = CmsHelper::getLoggedInRedirect($isAdmin);
                header('Location: ' . $redirect);
                exit;
            }
            if (isset($config['registration_enabled']) && !$config['registration_enabled']) {
                $error = 'User registration is currently disabled.';
                $success = '';
                // Use CMS-themed registration page
                $cmsRegisterView = dirname(__DIR__, 2) . '/cms/views/user/register.php';
                if (file_exists($cmsRegisterView)) {
                    include $cmsRegisterView;
                } else {
                    include __DIR__ . '/../views/register.php';
                }
                return;
            }
            if (empty($config['modules']['User'])) {
                header('Location: /');
                exit;
            }
            $error = '';
            $success = '';
            
            // Generate CSRF token for the form
            $tm = new TokenManager();
            $token = $tm->generate();
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $result = $tm->verify($_POST['token'] ?? '');
                if ($result['status'] !== 'success') {
                    // Regenerate token and reload form with gentle message
                    $tm->renew();
                    $error = 'Your session expired or the form was open too long. The form has been refreshed—please try again.';
                    goto render;
                } else {
                    $db = new DB($config);
                    $user = new User($db, $config);
                    $userInfo = [
                        'display_name' => trim($_POST['display_name'] ?? ''),
                        'first_name' => trim($_POST['first_name'] ?? ''),
                        'second_name' => trim($_POST['second_name'] ?? ''),
                        'email' => trim($_POST['email'] ?? ''),
                        'pwd' => $_POST['password'] ?? '',
                        'confirm_pwd' => $_POST['confirm_password'] ?? '',
                    ];
                    // Password mismatch check
                    if ($userInfo['pwd'] !== $userInfo['confirm_pwd']) {
                        $error = 'Passwords do not match.';
                        goto render;
                    }
                    if (!empty($_POST['display_name'])) {
                        $userInfo['display_name'] = trim($_POST['display_name']);
                    }
                    // Server-side display name validation
                    if (!empty($userInfo['display_name'])) {
                        // Check for taken display name
                        $sql = "SELECT COUNT(*) FROM users WHERE display_name = ?";
                        $stmt = $db->getPdo()->prepare($sql);
                        $stmt->execute([$userInfo['display_name']]);
                        if ($stmt->fetchColumn() > 0) {
                            $error = 'Display name is already taken.';
                            goto render;
                        }
                        // Check for bad words (modular, from config)
                        if (!empty($config['bad_words']) && is_array($config['bad_words'])) {
                            if (in_array(strtolower($userInfo['display_name']), array_map('strtolower', $config['bad_words']))) {
                                $error = 'Display name is not allowed.';
                                goto render;
                            }
                        }
                    }
                    // Server-side email validation
                    if (!empty($userInfo['email'])) {
                        $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
                        $stmt = $db->getPdo()->prepare($sql);
                        $stmt->execute([$userInfo['email']]);
                        if ($stmt->fetchColumn() > 0) {
                            $error = 'Email address is already registered.';
                            goto render;
                        }
                    }
                    $result = $user->register($userInfo);
                    if ($result['status'] === 'success') {
                        $success = $result['message'];
                    } else {
                        $error = $result['message'];
                    }
                }
            }
            render:
            // Use CMS-themed registration page
            $cmsRegisterView = dirname(__DIR__, 2) . '/cms/views/user/register.php';
            if (file_exists($cmsRegisterView)) {
                include $cmsRegisterView;
            } else {
                include __DIR__ . '/../views/register.php';
            }
        } catch (\Exception $e) {
            $error = $e->getMessage() ?: 'An unexpected error occurred during registration. Please try again.';
            $success = '';
            
            // Generate token for the form even in error case
            $tm = new TokenManager();
            $token = $tm->generate();
            
            // Use CMS-themed registration page
            $cmsRegisterView = dirname(__DIR__, 2) . '/cms/views/user/register.php';
            if (file_exists($cmsRegisterView)) {
                include $cmsRegisterView;
            } else {
                include __DIR__ . '/../views/register.php';
            }
        }
    }
}
