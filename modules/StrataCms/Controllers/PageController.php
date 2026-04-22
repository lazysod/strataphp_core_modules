<?php
namespace App\Modules\StrataCms\Controllers;

use App\DB;
use App\Modules\StrataCms\Models\Page;
use App\Modules\StrataCms\ThemeManager;
use App\View;

/**
 * Class PageController
 *
 * Handles public and admin page operations for the StrataPHP CMS module.
 */
use App\Modules\StrataCms\Helpers\SiteHelper;

class PageController
{
    private $db;
    private $config;
    
    public function __construct()
    {
        $this->config = include dirname(__DIR__, 3) . '/app/config.php';
        $this->db = new DB($this->config);
    }
    
    /**
     * Display the home page
     */
    public function home()
    {
        try {
            $pageModel = new Page($this->config);
            $siteId = SiteHelper::getCurrentSiteId();
            // Try to find the home page for this site
            $pages = $pageModel->getAllBySite($siteId);
            $homePage = null;
            foreach ($pages as $p) {
                if (!empty($p['is_home'])) {
                    $homePage = $p;
                    break;
                }
            }
            // Fallback: page with slug 'home' for this site
            if (!$homePage) {
                foreach ($pages as $p) {
                    if ($p['slug'] === 'home') {
                        $homePage = $p;
                        break;
                    }
                }
            }
            // Fallback: first published page for this site
            if (!$homePage && !empty($pages)) {
                $homePage = $pages[0];
            }
            if (!$homePage) {
                // If no pages exist, show a default welcome message
                $data = [
                    'title' => 'Welcome to StrataPHP',
                    'content' => '<h1>Welcome to StrataPHP</h1><p>Your CMS is ready! Create your first page in the admin panel.</p>',
                    'meta_description' => 'Welcome to StrataPHP CMS'
                ];
            } else {
                $data = [
                    'title' => $homePage['title'],
                    'meta_title' => $homePage['meta_title'] ?? $homePage['title'],
                    'content' => $homePage['content'],
                    'excerpt' => $homePage['excerpt'] ?? '',
                    'meta_description' => $homePage['meta_description'] ?? $homePage['excerpt'] ?? '',
                    'og_image' => $homePage['og_image'] ?? '',
                    'og_type' => $homePage['og_type'] ?? 'article',
                    'twitter_card' => $homePage['twitter_card'] ?? 'summary_large_image',
                    'canonical_url' => $homePage['canonical_url'] ?? '',
                    'noindex' => $homePage['noindex'] ?? false,
                    'site_name' => $this->config['site_name'] ?? 'StrataPHP CMS',
                    'page' => $homePage
                ];
            }
            $this->renderPage($data);
        } catch (\Exception $e) {
            $this->showError('Unable to load the home page.');
        }
    }
    
    /**
     * Display a specific page by slug
     */
    public function show($slug)
    {
        try {
            $pageModel = new Page($this->config);
            $page = $pageModel->findBySlug($slug);
            
            if (!$page || $page['status'] !== 'published') {
                $this->show404();
                return;
            }
            
            $data = [
                'title' => $page['title'],
                'meta_title' => $page['meta_title'] ?? $page['title'],
                'content' => $page['content'],
                'excerpt' => $page['excerpt'] ?? '',
                'meta_description' => $page['meta_description'] ?? $page['excerpt'] ?? '',
                'og_image' => $page['og_image'] ?? '',
                'og_type' => $page['og_type'] ?? 'article',
                'twitter_card' => $page['twitter_card'] ?? 'summary_large_image',
                'canonical_url' => $page['canonical_url'] ?? '',
                'noindex' => $page['noindex'] ?? false,
                'site_name' => $this->config['site_name'] ?? 'StrataPHP CMS',
                'page' => $page
            ];
            
            $this->renderPage($data);
        } catch (\Exception $e) {
            $this->showError('Unable to load the requested page.');
        }
    }
    
    /**
     * Handle dynamic page routing (fallback route)
     */
    public function dynamicPage($slug)
    {
        // This is the same as show() but used for the fallback route
        $this->show($slug);
    }
    
    /**
     * Render a page using the theme system
     */
    private function renderPage($data)
    {
        // Always use the new theme system for rendering
        // Define the constant to allow access to CMS view files
        if (!defined('STRPHP_ROOT')) {
            define('STRPHP_ROOT', dirname(__DIR__, 3));
        }
        // Use the new theme manager
        $themeManager = new ThemeManager();
        // Get page data and template
        $page = $data['page'] ?? $data;
    // Force all pages to use the 'default' template for consistent rendering
        echo $themeManager->renderPage($page, 'default');
    }
    
    /**
     * Render a simple HTML page as fallback
     */
    private function renderSimplePage($data)
    {
        $title = htmlspecialchars($data['title'] ?? 'Page');
        $content = $data['content'] ?? '';
        $metaDescription = htmlspecialchars($data['meta_description'] ?? '');
        
        return "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <meta name=\"description\" content=\"{$metaDescription}\">
    <title>{$title}</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; line-height: 1.6; }
        h1 { color: #333; }
    </style>
</head>
<body>
    {$content}
</body>
</html>";
    }
    
    /**
     * Show 404 error page
     */
    private function show404()
    {
        http_response_code(404);
        
        // Use CMS-styled 404 page
        $cms404Template = dirname(__DIR__) . '/views/404.php';
        if (file_exists($cms404Template)) {
            include $cms404Template;
            return;
        }
        
        // Fallback to basic 404 if CMS template doesn't exist
        $data = [
            'title' => 'Page Not Found',
            'content' => '<h1>Page Not Found</h1><p>The requested page could not be found.</p>',
            'meta_description' => 'Page not found'
        ];
        
        $this->renderPage($data);
    }
    
    /**
     * Show error page
     */
    private function showError($message)
    {
        http_response_code(500);
        
        $data = [
            'title' => 'Error',
            'content' => '<h1>Error</h1><p>' . htmlspecialchars($message) . '</p>',
            'meta_description' => 'An error occurred'
        ];
        
        $this->renderPage($data);
    }
}
