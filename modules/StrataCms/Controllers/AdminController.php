<?php

namespace App\Modules\StrataCms\Controllers;

use App\DB;
use App\Modules\StrataCms\Models\Page;
use App\Modules\StrataCms\Models\Site;
use App\Modules\StrataCms\Helpers\SiteHelper;
use App\SessionManager;

/**
 * Class AdminController
 *
 * Handles admin operations for the StrataPHP CMS module.
 */
class AdminController
{
    private $db;
    private $config;
    /**
     * Set a page as the root (home) page
     */
    public function setHomePage($id)
    {
        try {
            // Unset all other home pages
            $this->db->query('UPDATE cms_pages SET is_home = 0');
            // Set this page as home
            $this->db->query('UPDATE cms_pages SET is_home = 1 WHERE id = ?', [$id]);
            $_SESSION['success'] = 'Home page updated!';
            header('Location: /admin/strata-cms/pages');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to set home page.';
            header('Location: /admin/strata-cms/pages');
            exit;
        }
    }

    public function __construct()
    {
        // Define the constant to allow access to CMS view files
        if (!defined('STRPHP_ROOT')) {
            define('STRPHP_ROOT', true);
        }

        $this->config = include dirname(__DIR__, 3) . '/app/config.php';
        $this->db = new DB($this->config);

        // Ensure user is authenticated and has admin access
        $this->requireAuth();
    }

    /**
     * Require authentication for admin access
     */
    private function requireAuth()
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Get session prefix from config
        $sessionPrefix = $this->config['session_prefix'] ?? 'app_';

        // Check if user is logged in as admin using StrataPHP's session structure
        if (!isset($_SESSION[$sessionPrefix . 'admin']) || $_SESSION[$sessionPrefix . 'admin'] < 1) {
            header('Location: /admin/admin_login.php');
            exit;
        }
    }

    /**
     * CMS Dashboard
     */
    public function dashboard()
    {
        try {
            $pageModel = new Page($this->config);

            // Get statistics

            $siteId = SiteHelper::getCurrentSiteId();
            $stats = [
                'total_pages' => $this->getPageCount(),
                'published_pages' => $this->getPageCount('published'),
                'draft_pages' => $this->getPageCount('draft'),
                'recent_pages' => $pageModel->getAllBySite($siteId, 5)
            ];

            $data = [
                'title' => 'CMS Dashboard',
                'stats' => $stats
            ];

            $this->renderAdminView('dashboard', $data);
        } catch (\Exception $e) {
            $this->showError('Unable to load the CMS dashboard.');
        }
    }

    /**
     * List all pages
     */
    public function pages()
    {
        try {
            $pageModel = new Page($this->config);
            $siteModel = new Site($this->config);
            $sites = $siteModel->getAll();
            $selectedSiteId = isset($_GET['site_id']) && is_numeric($_GET['site_id']) ? (int)$_GET['site_id'] : null;
            if ($selectedSiteId) {
                $pages = $pageModel->getAllBySite($selectedSiteId, null, true);
            } else {
                $pages = $pageModel->getAll(); // Show all pages from all sites
            }
            $data = [
                'title' => 'Manage Pages',
                'pages' => $pages,
                'sites' => $sites,
                'selectedSiteId' => $selectedSiteId
            ];
            $this->renderAdminView('pages', $data);
        } catch (\Exception $e) {
            $this->showError('Unable to load pages.');
        }
    }

    /**
     * Show create page form
     */
    public function createPage()
    {
        $pageModel = new Page($this->config);
        $siteId = SiteHelper::getCurrentSiteId();
        $allPages = $pageModel->getAllBySite($siteId);
        // Fetch all sites for the site selector
        $siteModel = new Site($this->config);
        $sites = $siteModel->getAll();
        $data = [
            'title' => 'Create New Page',
            'page' => null,
            'allPages' => $allPages,
            'sites' => $sites
        ];
        $this->renderAdminView('page_form', $data);
    }

    /**
     * Store new page
     */
    public function storePage()
    {
        try {
            $pageModel = new Page($this->config);

            $data = [
                'title' => $_POST['title'] ?? '',
                'slug' => $_POST['slug'] ?? '',
                'content' => $_POST['content'] ?? '',
                'excerpt' => $_POST['excerpt'] ?? '',
                'meta_title' => $_POST['meta_title'] ?? '',
                'meta_description' => $_POST['meta_description'] ?? '',
                'og_image' => $_POST['og_image'] ?? '',
                'og_type' => $_POST['og_type'] ?? 'article',
                'twitter_card' => $_POST['twitter_card'] ?? 'summary_large_image',
                'canonical_url' => $_POST['canonical_url'] ?? '',
                'noindex' => isset($_POST['noindex']) ? 1 : 0,
                'status' => $_POST['status'] ?? 'draft',
                'template' => $_POST['template'] ?? 'default',
                'menu_order' => $_POST['menu_order'] ?? 0,
                'author_id' => $_SESSION[($this->config['session_prefix'] ?? 'app_') . 'user_id'] ?? 1,
                'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
                'site_id' => !empty($_POST['site_id']) ? (int)$_POST['site_id'] : null
            ];

            // Validate required fields
            if (empty($data['title'])) {
                throw new \Exception('Page title is required.');
            }

            $pageId = $pageModel->create($data);

            if ($pageId) {
                $_SESSION['success'] = 'Page created successfully.';
                header('Location: /admin/strata-cms/pages');
            } else {
                throw new \Exception('Failed to create page.');
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /admin/strata-cms/pages/create');
        }
        exit;
    }

    /**
     * Show edit page form
     */
    public function editPage($id)
    {
        try {
            $pageModel = new Page($this->config);
            $page = $pageModel->getById($id);

            if (!$page) {
                $_SESSION['error'] = 'Page not found.';
                header('Location: /admin/strata-cms/pages');
                exit;
            }

            $siteId = SiteHelper::getCurrentSiteId();
            $allPages = $pageModel->getAllBySite($siteId);
            $siteModel = new Site($this->config);
            $sites = $siteModel->getAll();
            $data = [
                'title' => 'Edit Page',
                'page' => $page,
                'allPages' => $allPages,
                'sites' => $sites
            ];
            $this->renderAdminView('page_form', $data);
        } catch (\Exception $e) {
            $this->showError('Unable to load page for editing.');
        }
    }
    /**
     * Update existing page
     */
    public function updatePage($id)
    {
        try {
            $pageModel = new Page($this->config);

            $data = [
                'title' => $_POST['title'] ?? '',
                'slug' => $_POST['slug'] ?? '',
                'content' => $_POST['content'] ?? '',
                'excerpt' => $_POST['excerpt'] ?? '',
                'meta_title' => $_POST['meta_title'] ?? '',
                'meta_description' => $_POST['meta_description'] ?? '',
                'og_image' => $_POST['og_image'] ?? '',
                'og_type' => $_POST['og_type'] ?? 'article',
                'twitter_card' => $_POST['twitter_card'] ?? 'summary_large_image',
                'canonical_url' => $_POST['canonical_url'] ?? '',
                'noindex' => isset($_POST['noindex']) ? 1 : 0,
                'status' => $_POST['status'] ?? 'draft',
                'template' => $_POST['template'] ?? 'default',
                'menu_order' => $_POST['menu_order'] ?? 0,
                'author_id' => $_SESSION[($this->config['session_prefix'] ?? 'app_') . 'user_id'] ?? 1,
                'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
                'site_id' => !empty($_POST['site_id']) ? (int)$_POST['site_id'] : null
            ];

            // Validate required fields
            if (empty($data['title'])) {
                throw new \Exception('Page title is required.');
            }

            $success = $pageModel->update($id, $data);

            if ($success) {
                $_SESSION['success'] = 'Page updated successfully.';
                header('Location: /admin/strata-cms/pages');
            } else {
                throw new \Exception('Failed to update page.');
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /admin/strata-cms/pages/' . $id . '/edit');
        }
        exit;
    }

    /**
     * Delete page
     */
    public function deletePage($id)
    {
        try {
            $pageModel = new Page($this->config);
            $success = $pageModel->delete($id);

            if ($success) {
                $_SESSION['success'] = 'Page deleted successfully.';
            } else {
                $_SESSION['error'] = 'Failed to delete page.';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'An error occurred while deleting the page.';
        }

        header('Location: /admin/strata-cms/pages');
        exit;
    }

    /**
     * Get page count by status
     */
    private function getPageCount($status = null)
    {
        if ($status) {
            $result = $this->db->fetch("SELECT COUNT(*) as count FROM cms_pages WHERE status = ?", [$status]);
        } else {
            $result = $this->db->fetch("SELECT COUNT(*) as count FROM cms_pages");
        }

        return $result ? $result['count'] : 0;
    }

    /**
     * Render admin view template
     */
    private function renderAdminView($template, $data = [])
    {
        $templatePath = dirname(__DIR__) . '/views/admin/' . $template . '.php';

        if (file_exists($templatePath)) {
            // Extract data for template
            extract($data);
            include $templatePath;
        } else {
            // Fallback to simple output
            echo $this->renderSimpleAdminPage($data);
        }
    }

    /**
     * Render simple admin page as fallback
     */
    private function renderSimpleAdminPage($data)
    {
        $title = htmlspecialchars($data['title'] ?? 'CMS Admin');

        $content = '<h1>' . $title . '</h1>';

        if (isset($data['pages'])) {
            $content .= '<div class="pages-list">';
            $content .= '<a href="/admin/strata-cms/pages/create" class="btn btn-primary">Create New Page</a>';
            $content .= '<table class="table">';
            $content .= '<thead><tr><th>Title</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>';
            $content .= '<tbody>';

            foreach ($data['pages'] as $page) {
                $content .= '<tr>';
                $content .= '<td>' . htmlspecialchars($page['title']) . '</td>';
                $content .= '<td>' . htmlspecialchars($page['status']) . '</td>';
                $content .= '<td>' . htmlspecialchars($page['created_at']) . '</td>';
                $content .= '<td>';
                $content .= '<a href="/admin/strata-cms/pages/' . $page['id'] . '/edit">Edit</a> | ';
                $content .= '<form method="POST" action="/admin/strata-cms/pages/' . $page['id'] . '/delete" style="display:inline;">';
                $content .= '<button type="submit" onclick="return confirm(\'Are you sure?\')">Delete</button>';
                $content .= '</form>';
                $content .= '</td>';
                $content .= '</tr>';
            }

            $content .= '</tbody></table>';
            $content .= '</div>';
        }

        return "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>{$title}</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; line-height: 1.6; }
        .btn { padding: 8px 16px; background: #007cba; color: white; text-decoration: none; border-radius: 4px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        .table th { background: #f5f5f5; }
    </style>
</head>
<body>
    {$content}
</body>
</html>";
    }

    /**
     * Show error page
     */
    private function showError($message)
    {
        http_response_code(500);

        $data = [
            'title' => 'Admin Error',
            'content' => '<h1>Error</h1><p>' . htmlspecialchars($message) . '</p>'
        ];

        echo $this->renderSimpleAdminPage($data);
    }

    /**
     * Check if slug is available (AJAX endpoint)
     */
    public function checkSlug()
    {
        header('Content-Type: application/json');

        try {
            $slug = $_GET['slug'] ?? '';
            $excludeId = $_GET['exclude_id'] ?? null;

            if (empty($slug)) {
                echo json_encode(['available' => false, 'message' => 'Slug cannot be empty']);
                return;
            }

            $pageModel = new Page($this->config);

            // Check for route conflicts with existing static routes
            $conflictRoutes = [
                'admin',
                'user',
                'api',
                'about',
                'contact',
                'login',
                'logout',
                'register',
                'dashboard',
                'modules',
                'links',
                'forum'
            ];

            if (in_array($slug, $conflictRoutes)) {
                echo json_encode([
                    'available' => false,
                    'message' => 'This slug conflicts with system routes'
                ]);
                return;
            }

            $available = $pageModel->isSlugAvailable($slug, $excludeId);

            echo json_encode([
                'available' => $available,
                'message' => $available ? 'Slug is available' : 'Slug already exists'
            ]);
        } catch (\Exception $e) {
            echo json_encode(['available' => false, 'message' => 'Error checking slug']);
        }
    }

    /**
     * Media Library - manage uploaded images
     */
    public function mediaLibrary()
    {
        $this->requireAuth();

        try {
            // Use config value or BASE_PATH for upload directory
            $uploadDir = ($this->config['cms_upload_dir'] ?? (defined('BASE_PATH') ? BASE_PATH . '/storage/uploads/cms/' : __DIR__ . '/../../../../storage/uploads/cms/'));
            $thumbDir = $uploadDir . 'thumbs/';
            $images = [];
            $perPage = 20;
            $page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
            $total = 0;
            if (is_dir($uploadDir)) {
                $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($uploadDir, \FilesystemIterator::SKIP_DOTS));
                foreach ($rii as $fileInfo) {
                    if ($fileInfo->isFile()) {
                        $filePath = $fileInfo->getPathname();
                        $file = $fileInfo->getFilename();
                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'])) {
                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            // Get relative path for URL (always relative to /storage/uploads/cms/)
                            $relativeFile = ltrim(str_replace($uploadDir, '', $filePath), '/\\');
                            $url = '/storage/uploads/cms/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativeFile);
                            $images[] = [
                                'filename' => $file,
                                'url' => $url,
                                'thumbnail' => $isImage ? $url : '',
                                'size' => filesize($filePath),
                                'uploaded' => date('Y-m-d H:i:s', filemtime($filePath))
                            ];
                        }
                    }
                }
                // Sort by upload date (newest first)
                usort($images, function ($a, $b) {
                    return strtotime($b['uploaded']) - strtotime($a['uploaded']);
                });
                $total = count($images);
                $images = array_slice($images, ($page - 1) * $perPage, $perPage);
            }
            $totalPages = max(1, ceil($total / $perPage));
            $this->renderAdminView('media_library', [
                'images' => $images,
                'page' => $page,
                'totalPages' => $totalPages
            ]);
        } catch (\Exception $e) {
            $this->renderAdminView('error', ['message' => 'Failed to load media library']);
        }
    }
}
