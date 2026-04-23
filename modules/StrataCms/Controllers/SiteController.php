<?php

namespace App\Modules\StrataCms\Controllers;

use App\DB;
use App\Modules\StrataCms\Models\Site;

/**
 * Handles listing, creating, updating, and deleting sites, as well as API key management for the StrataPHP CMS module.
 */
class SiteController
{
    /** @var DB */
    private $db;
    /** @var array */
    private $config;

    /**
     * SiteController constructor. Initializes DB and config.
     */
    public function __construct()
    {
        if (!defined('STRPHP_ROOT')) {
            define('STRPHP_ROOT', true);
        }
        $this->config = include dirname(__DIR__, 3) . '/app/config.php';
        $this->db = new DB($this->config);
    }

    /**
     * Set the active site (from dropdown)
     */
    public function setActive()
    {
        try {
            $activeSiteId = isset($_POST['active_site_id']) ? (int)$_POST['active_site_id'] : null;
            if (!$activeSiteId) {
                $_SESSION['error'] = 'No site selected.';
                header('Location: /admin/cms/sites');
                exit;
            }
            // Update config table (assumes table: config, columns: config_key, config_value)
            $this->db->query("REPLACE INTO config (config_key, config_value) VALUES ('active_site_id', ?)", [$activeSiteId]);
            $_SESSION['success'] = 'Active site updated!';
            header('Location: /admin/cms/sites');
            exit;
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Failed to set active site.';
            header('Location: /admin/cms/sites');
            exit;
        }
    }

    /**
     * List all sites.
     * Loads the sites list view.
     */
    public function index()
    {
        try {
            require_once __DIR__ . '/../models/Site.php';
            $siteModel = new Site($this->config);
            $sites = $siteModel->getAll();
            // Get active site from config table
            $row = $this->db->fetch("SELECT config_value FROM config WHERE config_key = 'active_site_id' LIMIT 1");
            $activeSiteId = $row && isset($row['config_value']) ? (int)$row['config_value'] : null;
            include __DIR__ . '/../views/admin/sites_list.php';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'An error occurred loading the sites list: ' . $e->getMessage();
            echo '<pre style="color:red;">' . htmlspecialchars($e) . '</pre>';
            exit;
        }
    }

    /**
     * Show the create site form.
     */
    public function create()
    {
        try {
            include __DIR__ . '/../views/admin/site_create.php';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'An error occurred loading the create site form.';
            header('Location: /admin/cms/sites');
        }
    }

    /**
     * Handle create site POST request.
     */
    public function store()
    {
        try {
            require_once __DIR__ . '/../models/Site.php';
            $siteModel = new Site($this->config);
            $name = trim($_POST['name'] ?? '');
            $headless = isset($_POST['headless']) && $_POST['headless'] == '1' ? 1 : 0;
            if (!$name) {
                $_SESSION['error'] = 'Site name is required.';
                header('Location: /admin/cms/sites/create');
                exit;
            }
            $apiKey = bin2hex(random_bytes(32));
            $siteModel->create($name, $apiKey, $headless);
            $_SESSION['success'] = 'Site created!';
            header('Location: /admin/cms/sites');
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'An error occurred creating the site.';
            header('Location: /admin/cms/sites/create');
        }
    }

    /**
     * Show the edit site form.
     */
    public function edit($id)
    {
        try {
            require_once __DIR__ . '/../models/Site.php';
            $siteModel = new Site($this->config);
            $site = $siteModel->getById($id);
            if (!$site) {
                $_SESSION['error'] = 'Site not found.';
                header('Location: /admin/cms/sites');
                exit;
            }
            include __DIR__ . '/../views/admin/site_edit.php';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'An error occurred loading the edit site form.';
            header('Location: /admin/cms/sites');
        }
    }

    /**
     * Handle update site POST request.
     */
    public function update($id)
    {
        try {
            require_once __DIR__ . '/../models/Site.php';
            $siteModel = new Site($this->config);
            $name = trim($_POST['name'] ?? '');
            $headless = isset($_POST['headless']) && $_POST['headless'] == '1' ? 1 : 0;
            if (!$name) {
                $_SESSION['error'] = 'Site name is required.';
                header('Location: /admin/strata-cms/sites/edit/' . $id);
                exit;
            }
            $siteModel->updateSite($id, $name, $headless);
            $_SESSION['success'] = 'Site updated!';
            header('Location: /admin/cms/sites');
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'An error occurred updating the site.';
            header('Location: /admin/strata-cms/sites/edit/' . $id);
        }
    }

    /**
     * Regenerate API key for a site.
     */
    public function regenerateKey($id)
    {
        try {
            require_once __DIR__ . '/../models/Site.php';
            $siteModel = new Site($this->config);
            $apiKey = bin2hex(random_bytes(32));
            $siteModel->updateApiKey($id, $apiKey);
            $_SESSION['success'] = 'API key regenerated!';
            header('Location: /admin/cms/sites');
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'An error occurred regenerating the API key.';
            header('Location: /admin/strata-cms/sites');
        }
    }

    /**
     * Delete a site and all its pages.
     */
    public function delete($id)
    {
        try {
            require_once __DIR__ . '/../models/Site.php';
            require_once __DIR__ . '/../models/Page.php';
            $siteModel = new Site($this->config);
            $pageModel = new \App\Modules\StrataCms\Models\Page($this->config);
            $this->db->query("DELETE FROM cms_pages WHERE site_id = ?", [$id]);
            if ($siteModel->delete($id)) {
                $_SESSION['success'] = 'Site and all its pages deleted.';
            } else {
                $_SESSION['error'] = 'Failed to delete site.';
            }
            header('Location: /admin/strata-cms/sites');
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'An error occurred deleting the site.';
            header('Location: /admin/strata-cms/sites');
        }
    }
}
