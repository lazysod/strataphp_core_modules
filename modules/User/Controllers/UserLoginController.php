<?php
namespace App\Modules\User\Controllers;

use App\TokenManager;
use App\DB;
use App\User;
use App\Modules\User\Helpers\CmsHelper;

// modules/user/controllers/UserLoginController.php
// Refactored as a class for router compatibility

/**
 * User Login Controller
 *
 * Handles user authentication and login functionality
 * Provides secure login with CSRF protection and session management
 */
class UserLoginController
{
    /**
     * Handle user login requests
     *
     * Processes both GET (display form) and POST (authenticate) requests
     * Includes CSRF token validation and proper error handling
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
            // Ensure session prefix is consistent
            $sessionPrefix = $config['session_prefix'] ?? 'app_';
            // Only generate token if not already set
            $tm = new TokenManager($config);
            if (empty($_SESSION[$sessionPrefix . 'token']['token_id'])) {
                $tm->generate();
            }
            if (!empty($_SESSION[$sessionPrefix . 'user_id'])) {
                $isAdmin = isset($_SESSION[$sessionPrefix . 'admin']) && $_SESSION[$sessionPrefix . 'admin'] > 0;
                if (!empty($_SESSION['oauth_login_redirect'])) {
                    $redirect = $_SESSION['oauth_login_redirect'];
                    unset($_SESSION['oauth_login_redirect']);
                    header('Location: ' . $redirect);
                    exit;
                }
                header('Location: /dashboard');
                exit;
            }
            $error = '';
            $success = '';
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $tm = new TokenManager($config);
                $result = $tm->verify($_POST['token'] ?? '');
                if ($result['status'] !== 'success') {
                    // Regenerate CSRF token and reload the form
                    $tm->renew();
                    $error = 'Invalid CSRF token. The form has been refreshed. Please try again.';
                    $viewPath = CmsHelper::getViewPath('User/login.php', __DIR__ . '/../views/login.php');
                    include $viewPath;
                    return;
                } else {
                    $db = new DB($config);
                    $user = new User($db, $config);
                    $loginInfo = [
                        'email' => trim($_POST['email'] ?? ''),
                        'pwd' => $_POST['password'] ?? '',
                        'remember' => isset($_POST['remember']) ? 1 : 0,
                    ];
                    $result = $user->login($loginInfo);
                    if ($result['status'] === 'success') {
                        // Set persistent login cookie if 'remember me' is checked
                        if ($loginInfo['remember']) {
                            require_once __DIR__ . '/../../../app/CookieManager.php';
                            $cookieManager = new \App\CookieManager($db, $config);
                            $cookieManager->set(null);
                        }
                        $isAdmin = isset($_SESSION[$sessionPrefix . 'admin']) && $_SESSION[$sessionPrefix . 'admin'] > 0;
                        if (!empty($_SESSION['oauth_login_redirect'])) {
                            $redirect = $_SESSION['oauth_login_redirect'];
                            unset($_SESSION['oauth_login_redirect']);
                            header('Location: ' . $redirect);
                            exit;
                        }
                        header('Location: /');
                        exit;
                    } else {
                        $error = 'Login failed: ' . htmlspecialchars($result['message']);
                    }
                }
            }
            $viewPath = CmsHelper::getViewPath('User/login.php', __DIR__ . '/../views/login.php');
            include $viewPath;
        } catch (\Exception $e) {
            $error = 'An unexpected error occurred. Please try again.';
            $viewPath = CmsHelper::getViewPath('User/login.php', __DIR__ . '/../views/login.php');
            include $viewPath;
        }
    }
}
