<?php
namespace App\Modules\OAuthClients\Controllers;

use App\DB;

class OAuthTokenController
{
    protected $db;
    public function __construct()
    {
        $config = require dirname(__DIR__, 3) . '/app/config.php';
        $this->db = new DB($config);
    }
    // /oauth/token endpoint
    public function token()
    {
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
        // Debug: log expiry check values
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
            return;
        }
        // Generate access token and store in DB
        $access_token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', time() + 3600);
        $this->db->query(
            'INSERT INTO oauth_tokens (access_token, client_id, user_id, expires_at) VALUES (?, ?, ?, ?)',
            [$access_token, $client_id, $user_id, $expires_at]
        );
        // Remove code after use
        $this->db->query('DELETE FROM oauth_codes WHERE id = ?', [$row['id']]);
        echo json_encode([
            'access_token' => $access_token,
            'token_type' => 'bearer',
            'expires_in' => 3600
        ]);
    }
    public function index()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        echo json_encode([
            'status' => 'ok',
            'message' => 'OAuthTokenController is now loaded and responding.'
        ]);
        exit;
    }
}