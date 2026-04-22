<?php
namespace App\Modules\User\Controllers;

use App\DB;

/**
 * SSO Controller
 *
 * Handles Single Sign-On (SSO) management for users.
 * Includes error handling and documentation comments.
 */
class SSOController
{
    protected $db;
    protected $config;
    protected $sessionPrefix;

    public function __construct()
    {
        global $config;
        $this->config = $config;
        $this->db = new DB($config);
        $this->sessionPrefix = $config['session_prefix'] ?? 'app_';
    }

    /**
     * Show SSO dashboard for the user
     */
    public function index()
    {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (empty($this->config['sso'])) {
                include __DIR__ . '/../views/sso_disabled.php';
                return;
            }
            if (empty($_SESSION[$this->sessionPrefix . 'user_id'])) {
                header('Location: /user/login');
                exit;
            }
            $user_id = $_SESSION[$this->sessionPrefix . 'user_id'];
            // Join with oauth_clients to get client (website/app) info
            $ssos = $this->db->fetchAll('SELECT a.*, c.name AS client_name, c.client_id, c.status AS client_status FROM oauth_user_approvals a JOIN oauth_clients c ON a.client_id = c.id WHERE a.user_id = ?', [$user_id]);
            include __DIR__ . '/../views/sso.php';
        } catch (\Throwable $e) {
            error_log('SSOController error: ' . $e->getMessage());
            http_response_code(500);
            echo '<h1>SSO Error</h1>';
        }
    }

    /**
     * Revoke SSO approval for a user
     */
    public function revoke()
    {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (empty($_SESSION[$this->sessionPrefix . 'user_id'])) {
                header('Location: /user/login');
                exit;
            }
            $user_id = $_SESSION[$this->sessionPrefix . 'user_id'];
            $revoke_id = isset($_POST['revoke_id']) ? (int)$_POST['revoke_id'] : 0;
            if ($revoke_id > 0) {
                // Only update the user's approval, not the global client
                $this->db->query('UPDATE oauth_user_approvals SET status = 0 WHERE id = ? AND user_id = ?', [$revoke_id, $user_id]);
            }
            header('Location: /user/sso');
            exit;
        } catch (\Throwable $e) {
            error_log('SSOController revoke error: ' . $e->getMessage());
            http_response_code(500);
            echo '<h1>SSO Revoke Error</h1>';
        }
    }
}
