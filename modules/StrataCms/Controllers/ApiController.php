<?php
namespace App\Modules\StrataCms\Controllers;

use App\Modules\StrataCms\Models\Page;
use App\Modules\StrataCms\Models\Site;

class ApiController
{
    private $config;
    public function __construct()
    {
        $this->config = include dirname(__DIR__, 3) . '/app/config.php';
    }

    /**
     * GET /api/pages?api_key=XYZ[&slug=...&limit=...]
     * Returns published, headless-allowed pages for the site matching the API key.
     */
    public function pages()
    {
        header('Content-Type: application/json');
        $apiKey = $_GET['api_key'] ?? '';
        if (!$apiKey) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Missing API key']);
            return;
        }
        $siteModel = new Site($this->config);
        $site = $siteModel->getByApiKey($apiKey);
        if (!$site) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid API key']);
            return;
        }
        $siteId = $site['id'];
        $slug = $_GET['slug'] ?? null;
        $pageId = isset($_GET['page_id']) ? (int)$_GET['page_id'] : null;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $pageModel = new Page($this->config);
        if ($pageId) {
            $page = $pageModel->getById($pageId);
            if ($page && (int)$page['site_id'] === (int)$siteId && $page['status'] === 'published') {
                echo json_encode(['success' => true, 'pages' => [$page]]);
            } else {
                echo json_encode(['success' => true, 'pages' => []]);
            }
            return;
        }
        if ($slug) {
            $page = $pageModel->getBySlug($slug);
            if ($page && (int)$page['site_id'] === (int)$siteId && $page['status'] === 'published') {
                echo json_encode(['success' => true, 'pages' => [$page]]);
            } else {
                echo json_encode(['success' => true, 'pages' => []]);
            }
            return;
        }
        // getAllBySite already respects the site's headless status
        $pages = $pageModel->getAllBySite($siteId, $limit);
        echo json_encode(['success' => true, 'pages' => array_values($pages)]);
    }
}
