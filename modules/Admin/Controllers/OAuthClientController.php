<?php
namespace App\Modules\Admin\Controllers;

use App\DB;

class OAuthClientController
{
    /**
     * Delete an OAuth client by ID.
     * @param int $id Client ID
     */
    public function delete($id)
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                $_SESSION['error'] = 'Invalid client ID.';
                header('Location: /admin/oauth-clients');
                exit;
            }
            // Optional: Add CSRF check here for extra security
            $deleted = $this->db->query("DELETE FROM oauth_clients WHERE id = ?", [$id]);
            if ($deleted) {
                $_SESSION['success'] = 'OAuth client deleted.';
            } else {
                $_SESSION['error'] = 'Failed to delete OAuth client.';
            }
            header('Location: /admin/oauth-clients');
            exit;
        } catch (\Exception $e) {
            error_log('OAuthClientController delete error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to delete OAuth client.';
            header('Location: /admin/oauth-clients');
            exit;
        }
    }

    protected $db;

    /**
     * OAuthClientController constructor.
     * Initializes the database connection.
     * @throws \Exception
     */
    public function __construct()
    {
        global $config;
        // Use global $config if available and valid
        if (isset($config) && is_array($config) && isset($config['db'])) {
            $this->db = new DB($config);
            return;
        }
        // Fallback: try to load config file directly
        $configPath = dirname(__DIR__, 4) . '/app/config.php';
        $loadedConfig = file_exists($configPath) ? require $configPath : [];
        if (isset($loadedConfig['db'])) {
            $this->db = new DB($loadedConfig['db']);
            return;
        }
        // If still missing, throw clear error
        throw new \Exception('OAuthClientController: Unable to load database config');
    }


    /**
     * Edit an existing OAuth client.
     * @param int $id Client ID
     * @throws \Exception
     */
    public function edit($id)
    {
        try {
            $client = $this->db->fetch("SELECT * FROM oauth_clients WHERE id = ?", [$id]);
            if (!$client) {
                header('Location: /admin/oauth-clients');
                exit;
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $name = trim($_POST['name'] ?? '');
                $redirect_uri = trim($_POST['redirect_uri'] ?? '');
                $data_shared = trim($_POST['data_shared'] ?? '');
                $this->db->query(
                    "UPDATE oauth_clients SET name = ?, redirect_uri = ?, data_shared = ? WHERE id = ?",
                    [$name, $redirect_uri, $data_shared, $id]
                );
                $_SESSION['success'] = 'OAuth client updated.';
                header('Location: /admin/oauth-clients');
                exit;
            }
            include __DIR__ . '/../views/oauth_clients/edit.php';
        } catch (\Exception $e) {
            error_log('OAuthClientController edit error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to edit OAuth client.';
            header('Location: /admin/oauth-clients');
            exit;
        }
    }

    /**
     * List all OAuth clients.
     * @throws \Exception
     */
    public function index()
    {
        try {
            $clients = $this->db->fetchAll("SELECT * FROM oauth_clients ORDER BY id DESC");
            include __DIR__ . '/../views/oauth_clients/list.php';
        } catch (\Exception $e) {
            error_log('OAuthClientController index error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to load OAuth clients.';
            header('Location: /admin');
            exit;
        }
    }

    /**
     * Add a new OAuth client.
     * @throws \Exception
     */
    public function add()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $name = trim($_POST['name'] ?? '');
                $redirect_uri = trim($_POST['redirect_uri'] ?? '');
                $data_shared = trim($_POST['data_shared'] ?? '');
                $client_id = bin2hex(random_bytes(16));
                $client_secret = bin2hex(random_bytes(32));
                $this->db->query(
                    "INSERT INTO oauth_clients (name, client_id, client_secret, redirect_uri, data_shared) VALUES (?, ?, ?, ?, ?)",
                    [$name, $client_id, $client_secret, $redirect_uri, $data_shared]
                );
                header('Location: /admin/oauth-clients');
                exit;
            }
            include __DIR__ . '/../views/oauth_clients/add.php';
        } catch (\Exception $e) {
            error_log('OAuthClientController add error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to add OAuth client.';
            header('Location: /admin/oauth-clients');
            exit;
        }
    }
}