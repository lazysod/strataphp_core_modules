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
        $requestedScope = $_GET['scope'] ?? 'basic';

        // Generate state if not present
        if (empty($_SESSION['oauth_state'])) {
            $_SESSION['oauth_state'] = bin2hex(random_bytes(16));
        }
        $state = $_SESSION['oauth_state'];

        // Validate client
        $client = $db->fetch('SELECT * FROM oauth_clients WHERE client_id = ? AND status = 1', [$client_id]);
        if (!$client) {
            http_response_code(400);
            echo 'Invalid client.';
            return;
        }
        // Validate redirect URI (exact match, host check)
        if ($client['redirect_uri'] !== $redirect_uri) {
            http_response_code(400); die('Invalid redirect_uri');
        }
        if (parse_url($client['redirect_uri'], PHP_URL_HOST) !== parse_url($redirect_uri, PHP_URL_HOST)) {
            http_response_code(400); die('Invalid redirect_uri host');
        }
        // Scope validation
        $allowedScopes = explode(' ', $client['allowed_scopes'] ?? 'basic');
        $scopes = array_intersect(explode(' ', $requestedScope), $allowedScopes);
        if (empty($scopes)) {
            http_response_code(400); die('Invalid scope');
        }
        $_SESSION['oauth_scope'] = implode(' ', $scopes);
        // Only support response_type=code
        if ($response_type !== 'code') {
            http_response_code(400);
            echo 'Invalid response type.';
            return;
        }
        // Require login
        $sessionPrefix = $config['session_prefix'] ?? 'app_';
        if (empty($_SESSION[$sessionPrefix . 'user_id'])) {
            // Redirect to login, then back to this page (safe)
            $_SESSION['oauth_login_redirect'] = '/oauth/authorize?' . http_build_query($_GET);
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
            // CSRF on form (if TokenManager available)
            // TokenManager::check($_POST['csrf_token']);
            // State check
            if (!hash_equals($_SESSION['oauth_state'] ?? '', $_POST['state'] ?? '')) {
                http_response_code(400); die('State mismatch');
            }
            // PKCE enforcement
            $code_challenge = $_GET['code_challenge'] ?? null;
            $code_challenge_method = $_GET['code_challenge_method'] ?? 'plain';
            if ($client['is_public'] && empty($code_challenge)) {
                http_response_code(400); die('PKCE required for public clients');
            }
            if ($code_challenge && !in_array($code_challenge_method, ['S256', 'plain'], true)) {
                http_response_code(400); die('Unsupported code_challenge_method');
            }
            if ($code_challenge_method === 'S256' && !preg_match('/^[A-Za-z0-9\-_]{43}$/', $code_challenge)) {
                http_response_code(400); die('Invalid S256 challenge');
            }
            // Rate limit
            $recent = $db->fetch('SELECT COUNT(*) as c FROM oauth_codes WHERE user_id = ? AND created_at > NOW() - INTERVAL 1 MINUTE', [$user_id]);
            if ($recent['c'] > 5) {
                http_response_code(429); die('Too many authorization attempts');
            }
            // Generate hashed code
            $rawCode = bin2hex(random_bytes(32));
            $codeHash = hash('sha256', $rawCode);
            $expires = date('Y-m-d H:i:s', time() + 300);
            $db->query('INSERT INTO oauth_codes (code, client_id, user_id, expires_at, code_challenge, code_challenge_method, scope) VALUES (?, ?, ?, ?, ?, ?, ?)', [
                $codeHash, $client_id, $user_id, $expires, $code_challenge, $code_challenge_method, $_SESSION['oauth_scope'] ?? 'basic'
            ]);
            unset($_SESSION['oauth_state'], $_SESSION['oauth_scope']);
            // Redirect back to client
            $redirect = $redirect_uri . (strpos($redirect_uri, '?') === false ? '?' : '&') . 'code=' . $rawCode . '&state=' . urlencode($_POST['state']);
            header('Location: ' . $redirect);
            exit;
        }
        // Use view for consent form
        include dirname(__DIR__) . '/views/authorize.php';
    }
}
