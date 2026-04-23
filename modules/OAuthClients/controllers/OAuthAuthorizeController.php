<?php
namespace App\Modules\OAuthClients\Controllers;

use App\DB;

class OAuthAuthorizeController
{
    public function index()
    {
        require_once dirname(__DIR__, 3) . '/bootstrap.php';
        global $config;
        $db = new DB($config);

        // Get query params
        $client_id = $_GET['client_id'] ?? '';
        $redirect_uri = $_GET['redirect_uri'] ?? '';
        $response_type = $_GET['response_type'] ?? '';
        $state = $_GET['state'] ?? '';

        // Validate client
        $client = $db->fetch('SELECT * FROM oauth_clients WHERE client_id = ? AND status = 1', [$client_id]);
        if (!$client) {
            http_response_code(400);
            echo 'Invalid client.';
            return;
        }
        // Validate redirect URI
        if (rtrim($client['redirect_uri'], '/') !== rtrim($redirect_uri, '/')) {
            http_response_code(400);
            echo 'Invalid redirect URI.';
            return;
        }
        // Only support response_type=code
        if ($response_type !== 'code') {
            http_response_code(400);
            echo 'Invalid response type.';
            return;
        }
        // Require login
        $sessionPrefix = $config['session_prefix'] ?? 'app_';
        if (empty($_SESSION[$sessionPrefix . 'user_id'])) {
            // Redirect to login, then back to this page
            $_SESSION['oauth_login_redirect'] = $_SERVER['REQUEST_URI'];
            header('Location: /user/login');
            exit;
        }
        $user_id = $_SESSION[$sessionPrefix . 'user_id'];
        // Handle Cancel (deny)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deny'])) {
            $redirect = $redirect_uri . (strpos($redirect_uri, '?') === false ? '?' : '&') . 'error=access_denied' . ($state ? '&state=' . urlencode($state) : '');
            header('Location: ' . $redirect);
            exit;
        }
        // Show consent screen (simple)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve'])) {
            // Generate code
            $code = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 300);
            $db->query('INSERT INTO oauth_codes (code, client_id, user_id, expires_at) VALUES (?, ?, ?, ?)', [$code, $client_id, $user_id, $expires]);
            // Redirect back to client
            $redirect = $redirect_uri . (strpos($redirect_uri, '?') === false ? '?' : '&') . 'code=' . $code . ($state ? '&state=' . urlencode($state) : '');
            header('Location: ' . $redirect);
            exit;
        }
        // Use view for consent form
        include dirname(__DIR__) . '/views/authorize.php';
    }
}
