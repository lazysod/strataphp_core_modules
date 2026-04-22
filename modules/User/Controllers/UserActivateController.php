<?php
namespace App\Modules\User\Controllers;

use App\DB;

/**
 * User Account Activation Controller
 *
 * Handles user account activation via email verification links
 * Validates activation tokens and activates user accounts
 */
class UserActivateController
{
    /**
     * Handle user account activation
     *
     * Validates activation keys and activates user accounts
     * Includes proper error handling and security validation
     *
     * @return void
     */
    public function index()
    {
        try {
            require_once dirname(__DIR__, 3) . '/bootstrap.php';
            global $config;
            $success = '';
            $error = '';
            $key = $_GET['key'] ?? '';
            if (!$key) {
                $error = 'Invalid activation link.';
                include __DIR__ . '/../views/activate.php';
                return;
            }
            $db = new DB($config);
            $sql = "SELECT * FROM user_activation WHERE activation_key = ?";
            $rows = $db->fetchAll($sql, [$key]);
            if (count($rows) === 0) {
                $error = 'Invalid or expired activation link.';
                include __DIR__ . '/../views/activate.php';
                return;
            }
            $activation = $rows[0];
            $expiry = $activation['expiry_date'] ?? '';
            $expiry_ts = $expiry ? strtotime($expiry) : false;
            if (!$expiry_ts || $expiry_ts < time()) {
                $error = 'This activation link has expired or is invalid.';
                include __DIR__ . '/../views/activate.php';
                return;
            }
            // Activate user
            $db->query('UPDATE users SET active = 1 WHERE id = ?', [$activation['user_id']]);
            $db->query('DELETE FROM user_activation WHERE id = ?', [$activation['id']]);
            $success = 'Your account has been activated! You can now <a href="/user/login">login</a>.';
            include __DIR__ . '/../views/activate.php';
        } catch (\Exception $e) {
            $error = 'An unexpected error occurred during activation. Please try again.';
            $success = '';
            include __DIR__ . '/../views/activate.php';
        }
    }
}
