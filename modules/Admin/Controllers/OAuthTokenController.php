<?php
namespace App\Modules\Admin\Controllers;

use App\DB;

class OAuthTokenController
{
    protected $db;
    /**
     * OAuthTokenController constructor.
     * Initializes the database connection.
     * @throws \Exception
     */
    public function __construct()
    {
        global $config;
        $this->db = new DB($config);
    }


    /**
     * Handles OAuth2 token requests.
     * Issues access tokens after validating client and code.
     * @throws \Exception
     */
    public function token()
    {
        try {
            // CORS headers
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Headers: Authorization, Content-Type');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
            header('Content-Type: application/json');
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit;
            }
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'invalid_request']);
                exit;
            }
            $client_id = $_POST['client_id'] ?? '';
            $client_secret = $_POST['client_secret'] ?? '';
            $code = $_POST['code'] ?? '';
            // Validate client
            $client = $this->db->fetch('SELECT * FROM oauth_clients WHERE client_id = ? AND client_secret = ?', [$client_id, $client_secret]);
            if (!$client || !isset($client['status']) || (int)$client['status'] !== 1) {
                http_response_code(401);
                echo json_encode(['error' => 'invalid_client_or_inactive']);
                exit;
            }
            // Validate code from DB
            $row = $this->db->fetch('SELECT * FROM oauth_codes WHERE code = ? AND client_id = ?', [$code, $client_id]);
            if (!$row || strtotime($row['expires_at']) < time()) {
                http_response_code(400);
                echo json_encode(['error' => 'invalid_grant']);
                exit;
            }
            $user_id = $row['user_id'];
            // Check for existing valid token
            $existingToken = $this->db->fetch('SELECT * FROM oauth_tokens WHERE client_id = ? AND user_id = ? AND expires_at > ?', [$client_id, $user_id, date('Y-m-d H:i:s', time())]);
            if ($existingToken) {
                // Remove code after use
                $this->db->query('DELETE FROM oauth_codes WHERE id = ?', [$row['id']]);
                echo json_encode([
                    'access_token' => $existingToken['access_token'],
                    'token_type' => 'bearer',
                    'expires_in' => strtotime($existingToken['expires_at']) - time()
                ]);
                exit;
            }
            // Generate new token
            $access_token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', time() + 3600);
            $this->db->query('INSERT INTO oauth_tokens (access_token, client_id, user_id, expires_at) VALUES (?, ?, ?, ?)', [$access_token, $client_id, $user_id, $expires_at]);
            // Remove code after use
            $this->db->query('DELETE FROM oauth_codes WHERE id = ?', [$row['id']]);
            echo json_encode([
                'access_token' => $access_token,
                'token_type' => 'bearer',
                'expires_in' => 3600
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            error_log('OAuth token error: ' . $e->getMessage());
            echo json_encode(['error' => 'server_error', 'error_description' => 'Internal server error']);
        }
    }
}