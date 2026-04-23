<?php
namespace App\Modules\Admin\Controllers;

use App\DB;

class OAuthAuthorizeController
{
    protected $db;
    /**
     * OAuthAuthorizeController constructor.
     * Initializes the database connection.
     * @throws \Exception
     */
    public function __construct()
    {
        global $config;
        $this->db = new DB($config);
    }

    // /oauth/authorize?client_id=...&redirect_uri=...&response_type=code&state=...
    /**
     * Handles OAuth2 authorization requests.
     * Shows consent screen and generates authorization code.
     * @throws \Exception
     */
    public function authorize()
    {
        try {
            $client_id = $_GET['client_id'] ?? '';
            $redirect_uri = $_GET['redirect_uri'] ?? '';
            $response_type = $_GET['response_type'] ?? '';
            $state = $_GET['state'] ?? '';
            $error = '';
            $client = $this->db->fetch('SELECT * FROM oauth_clients WHERE client_id = ?', [$client_id]);
            if (!$client || $client['redirect_uri'] !== $redirect_uri || !isset($client['status']) || (int)$client['status'] !== 1) {
                $error = 'Invalid client, redirect URI, or client inactive.';
            }
            if ($response_type !== 'code') {
                $error = 'Unsupported response type.';
            }
            if ($error) {
                include __DIR__ . '/../views/oauth_clients/authorize_error.php';
                return;
            }
            // If user is not logged in, redirect to login (or show login form)
            $sessionPrefix = $GLOBALS['config']['session_prefix'] ?? 'app_';
            if (!isset($_SESSION[$sessionPrefix . 'user_id'])) {
                $_SESSION['oauth_login_redirect'] = $_SERVER['REQUEST_URI'];
                header('Location: /user/login');
                exit;
            }
            // Show consent screen
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // User approved, generate code
                $code = bin2hex(random_bytes(32));
                $user_id = $_SESSION[$sessionPrefix . 'user_id'];
                $expires_at = date('Y-m-d H:i:s', time() + 300);
                // Insert or update approval record
                $scopes = isset($_POST['scopes']) ? $_POST['scopes'] : null;
                $existing = $this->db->fetch('SELECT id FROM oauth_user_approvals WHERE user_id = ? AND client_id = ?', [$user_id, $client['id']]);
                if ($existing) {
                    $this->db->query('UPDATE oauth_user_approvals SET scopes = ?, status = 1, approved_at = NOW() WHERE id = ?', [$scopes, $existing['id']]);
                } else {
                    $this->db->query('INSERT INTO oauth_user_approvals (user_id, client_id, scopes, status) VALUES (?, ?, ?, 1)', [$user_id, $client['id'], $scopes]);
                }
                $this->db->query(
                    'INSERT INTO oauth_codes (code, client_id, user_id, expires_at) VALUES (?, ?, ?, ?)',
                    [$code, $client_id, $user_id, $expires_at]
                );
                $redirect = $redirect_uri . '?code=' . $code . ($state ? '&state=' . urlencode($state) : '');
                header('Location: ' . $redirect);
                exit;
            }
            include __DIR__ . '/../views/oauth_clients/authorize.php';
        } catch (\Exception $e) {
            error_log('OAuth authorize error: ' . $e->getMessage());
            include __DIR__ . '/../views/oauth_clients/authorize_error.php';
        }
    }
}
