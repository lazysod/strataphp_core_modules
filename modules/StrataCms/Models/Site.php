<?php
namespace App\Modules\StrataCms\Models;

use App\DB;

/**
 * Class Site
 *
 * Handles site management and API key validation for the StrataPHP CMS module.
 */
class Site {
    /**
     * Get a site by ID.
     * @param int $id
     * @return array|null
     */
    public function getById($id)
    {
        try {
            return $this->db->fetch("SELECT * FROM sites WHERE id = ?", [$id]);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Update a site (name, headless).
     * @param int $id
     * @param string $name
     * @param int $headless
     * @return bool
     */
    public function updateSite($id, $name, $headless)
    {
        try {
            return $this->db->query("UPDATE sites SET name = ?, headless = ?, updated_at = NOW() WHERE id = ?", [$name, $headless, $id]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @var DB Database connection
     */
    private $db;

    /**
     * Site constructor.
     * @param array|null $config
     */
    public function __construct($config = null)
    {
        if ($config) {
            $this->db = new DB($config);
        } else {
            $localConfig = include __DIR__ . '/../../../app/config.php';
            $this->db = new DB($localConfig);
        }
    }

    /**
     * Delete a site by ID.
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        try {
            return $this->db->query("DELETE FROM sites WHERE id = ?", [$id]);
        } catch (\Throwable $e) {
            // Log error or handle as needed
            return false;
        }
    }

    /**
     * Get all sites.
     * @return array
     */
    public function getAll()
    {
        try {
            return $this->db->fetchAll("SELECT * FROM sites ORDER BY created_at DESC");
        } catch (\Throwable $e) {
            // Log error or handle as needed
            return [];
        }
    }

    /**
     * Create a new site.
     * @param string $name
     * @param string $apiKey
     * @return bool|int Insert ID or false on failure
     */
    public function create($name, $apiKey)
    {
        return $this->createWithHeadless($name, $apiKey, 0);
    }

    public function createWithHeadless($name, $apiKey, $headless = 0)
    {
        try {
            return $this->db->query("INSERT INTO sites (name, api_key, status, headless) VALUES (?, ?, 'active', ?)", [$name, $apiKey, $headless]);
        } catch (\Throwable $e) {
            // Log error or handle as needed
            return false;
        }
    }

    /**
     * Update API key for a site.
     * @param int $id
     * @param string $apiKey
     * @return bool
     */
    public function updateApiKey($id, $apiKey)
    {
        try {
            return $this->db->query("UPDATE sites SET api_key = ?, updated_at = NOW() WHERE id = ?", [$apiKey, $id]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get site by API key (active only)
     */
    public function getByApiKey($apiKey)
    {
        try {
            return $this->db->fetch("SELECT * FROM sites WHERE api_key = ? AND status = 'active'", [$apiKey]);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
