<?php
namespace App\Modules\Admin\Controllers;

use App\DB;
use App\User;

class OAuthUserInfoController
{
    protected $db;
    /**
     * OAuthUserInfoController constructor.
     * Initializes the database connection.
     * @throws \Exception
     */
    public function __construct()
    {
        global $config;
        $this->db = new DB($config);
    }

    /**
     * Handles OAuth2 userinfo endpoint.
     * Returns user info for a valid access token.
     * @throws \Exception
     */
    public function userinfo()
    {
        // CORS headers for all responses
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Authorization, Content-Type');
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Content-Type: application/json');
        header('X-Debug-Controller: OAuthUserInfoController');
        try {
            // Accept Bearer token in Authorization header
            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
            if (stripos($authHeader, 'Bearer ') === 0) {
                $accessToken = substr($authHeader, 7);
            } elseif (isset($_GET['access_token'])) {
                $accessToken = $_GET['access_token'];
            } else {
                // CORS headers for error
                header('Access-Control-Allow-Origin: *');
                header('Access-Control-Allow-Headers: Authorization, Content-Type');
                header('Access-Control-Allow-Methods: GET, OPTIONS');
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['error' => 'invalid_token', 'error_description' => 'No access token provided']);
                exit;
            }
            // Validate token
            $row = $this->db->fetch('SELECT * FROM oauth_tokens WHERE access_token = ?', [$accessToken]);
            $debugIsExpired = true;
            if ($row) {
                $debugIsExpired = strtotime($row['expires_at']) < time();
            }
            if (!$row || $debugIsExpired) {
                // CORS headers for error
                header('Access-Control-Allow-Origin: *');
                header('Access-Control-Allow-Headers: Authorization, Content-Type');
                header('Access-Control-Allow-Methods: GET, OPTIONS');
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['error' => 'invalid_token', 'error_description' => 'Token expired or invalid']);
                exit;
            }
            $userId = $row['user_id'];
            // Fetch user info
            $user = $this->db->fetch('SELECT id, display_name, email, avatar FROM users WHERE id = ?', [$userId]);
            if (!$user) {
                // CORS headers for error
                header('Access-Control-Allow-Origin: *');
                header('Access-Control-Allow-Headers: Authorization, Content-Type');
                header('Access-Control-Allow-Methods: GET, OPTIONS');
                header('Content-Type: application/json');
                http_response_code(404);
                echo json_encode(['error' => 'user_not_found']);
                exit;
            }
            // Return user info (only display_name, email, avatar)
            echo json_encode([
                'id' => $user['id'],
                'display_name' => $user['display_name'],
                'email' => $user['email'],
                'avatar' => $user['avatar'] ?? ''
            ]);
        } catch (\Exception $e) {
            // CORS headers for error
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Headers: Authorization, Content-Type');
            header('Access-Control-Allow-Methods: GET, OPTIONS');
            header('Content-Type: application/json');
            http_response_code(500);
            error_log('OAuth userinfo error: ' . $e->getMessage());
            echo json_encode(['error' => 'server_error', 'error_description' => 'Internal server error']);
        }
    }
}
