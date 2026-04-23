<?php
namespace App\Modules\OAuthClients\Controllers;

use App\DB;

/**
 * Controller for managing OAuth clients.
 * Handles listing, adding, and error management for OAuth clients.
 */
class OAuthClientsController
{
    protected $db;
    /**
     * OAuthClientsController constructor.
     * Initializes DB connection from injected instance, global config, or config file.
     * Throws exception if DB config is missing.
     * @param DB|null $db Optional injected DB instance
     * @throws \Exception
     */
    public function __construct($db = null)
    {
        if ($db) {
            // Log usage of injected DB instance
            error_log('OAuthClientsController: using injected DB instance');
            $this->db = $db;
        } else {
            try {
                // Try global $config first
                global $config;
                if (isset($config) && isset($config['db'])) {
                    error_log('OAuthClientsController: using global $config');
                    $this->db = new DB($config);
                    return;
                }
                // fallback: load config from file
                $configPath = dirname(__DIR__, 4) . '/app/config.php';
                $configFile = file_exists($configPath) ? require $configPath : [];
                error_log('OAuthClientsController: loaded config file!: ');
                if (!isset($configFile['db'])) {
                    error_log('Database config missing in OAuthClientsController');
                    throw new \Exception('Database config missing');
                }
                $this->db = new DB($configFile);
            } catch (\Exception $e) {
                error_log('Error initializing DB in OAuthClientsController: ' . $e->getMessage());
                throw $e;
            }
        }
    }

    /**
     * List all OAuth clients.
     * Displays client list view.
     */
    // ...existing code...
}
