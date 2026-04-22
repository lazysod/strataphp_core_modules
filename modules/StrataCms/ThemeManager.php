<?php
namespace App\Modules\StrataCms;
use App\Modules\StrataCms\Helpers\SiteHelper;
use App\HtmlSanitizer;

/**
 * CMS Theme Manager
 *
 * Handles theme loading, template rendering, and asset management
 * for CMS content pages
 */
class ThemeManager
{
    private $config;
    private $currentTheme;
    private $basePath;
    
    public function __construct()
    {
        $this->config = include __DIR__ . '/config/theme.php';
        $this->currentTheme = $this->config['default_theme'];
        $this->basePath = dirname(__DIR__, 2);
    }
    
    /**
     * Set the active theme
     */
    public function setTheme($theme)
    {
        if (isset($this->config['themes'][$theme])) {
            $this->currentTheme = $theme;
            return true;
        }
        return false;
    }
    
    /**
     * Get the current theme
     */
    public function getCurrentTheme()
    {
        return $this->currentTheme;
    }
    
    /**
     * Get theme configuration
     */
    public function getThemeConfig($theme = null)
    {
        $theme = $theme ?: $this->currentTheme;
        return $this->config['themes'][$theme] ?? null;
    }
    
    /**
     * Get available themes
     */
    public function getAvailableThemes()
    {
        return $this->config['themes'];
    }
    
    /**
     * Get available templates for current theme
     */
    public function getAvailableTemplates($theme = null)
    {
        $themeConfig = $this->getThemeConfig($theme);
        if (!$themeConfig) {
            return [];
        }
        
        $templates = [];
        foreach ($themeConfig['templates'] as $templateKey) {
            if (isset($this->config['templates'][$templateKey])) {
                $templates[$templateKey] = $this->config['templates'][$templateKey];
            }
        }
        
        return $templates;
    }
    
    /**
     * Render a page with the current theme
     */
    public function renderPage($page, $template = 'default')
    {
        $themeConfig = $this->getThemeConfig();
        if (!$themeConfig) {
            throw new \Exception("Theme '{$this->currentTheme}' not found");
        }
        
        // Check if template is available for this theme
        if (!in_array($template, $themeConfig['templates'])) {
            $template = 'default';
        }
        
        // Get template file
        $templateConfig = $this->config['templates'][$template] ?? $this->config['templates']['default'];
        $templateFile = $this->getTemplatePath($templateConfig['file']);
        
        if (!file_exists($templateFile)) {
            // Fallback to base template
            $templateFile = $this->getTemplatePath('base.php');
        }
        
        if (!file_exists($templateFile)) {
            throw new \Exception("Template file not found: {$templateFile}");
        }
        
        // Prepare theme variables
        $theme = [
            'config' => $themeConfig,
            'styles' => $themeConfig['styles'],
            'name' => $themeConfig['name'],
            'assets_url' => $this->getAssetsUrl(),
            'css_url' => $this->getCssUrl(),
            'js_url' => $this->getJsUrl()
        ];
        
        // Get navigation pages
        $navigation = $this->getNavigationPages();
        
        // Process content for proper line break and paragraph handling
        if (isset($page['content'])) {
            $page['content'] = $this->processContent($page['content']);
        }
        
        // Include the template
        ob_start();
        include $templateFile;
        return ob_get_clean();
    }
    
    /**
     * Process content to handle line breaks and paragraphs properly
     */
    private function processContent($content)
    {
        if (empty($content)) {
            return '';
        }
        
        // If content contains HTML tags, preserve them; otherwise convert line breaks
        if (strip_tags($content) === $content) {
            // No HTML tags found, process as plain text
            return HtmlSanitizer::plainTextToHtml($content);
        }
        
        // Content already contains HTML, just return it as-is
        return $content;
    }
    
    /**
     * Get theme template path
     */
    private function getTemplatePath($file)
    {
        return $this->basePath . '/' . $this->config['paths']['themes_dir'] . '/' .
               $this->currentTheme . '/' . $this->config['paths']['templates_dir'] . '/' . $file;
    }
    
    /**
     * Get theme assets URL
     */
    private function getAssetsUrl()
    {
        return '/' . $this->config['paths']['themes_dir'] . '/' .
               $this->currentTheme . '/' . $this->config['paths']['assets_dir'];
    }
    
    /**
     * Get CSS URL for current theme
     */
    private function getCssUrl()
    {
        $cssFile = 'style.css';
        if ($this->config['assets']['cache_bust']) {
            $cssPath = $this->basePath . '/' . $this->config['paths']['themes_dir'] . '/' .
                      $this->currentTheme . '/' . $this->config['paths']['assets_dir'] . '/css/' . $cssFile;
            if (file_exists($cssPath)) {
                $cssFile .= '?v=' . filemtime($cssPath);
            }
        }
        
        return $this->getAssetsUrl() . '/css/' . $cssFile;
    }
    
    /**
     * Get JS URL for current theme
     */
    private function getJsUrl()
    {
        $jsFile = 'theme.js';
        if ($this->config['assets']['cache_bust']) {
            $jsPath = $this->basePath . '/' . $this->config['paths']['themes_dir'] . '/' .
                     $this->currentTheme . '/' . $this->config['paths']['assets_dir'] . '/js/' . $jsFile;
            if (file_exists($jsPath)) {
                $jsFile .= '?v=' . filemtime($jsPath);
            }
        }
        
        return $this->getAssetsUrl() . '/js/' . $jsFile;
    }
    
    /**
     * Generate theme CSS from configuration
     */
    public function generateThemeCSS($theme = null)
    {
        $themeConfig = $this->getThemeConfig($theme);
        if (!$themeConfig) {
            return '';
        }
        
        $styles = $themeConfig['styles'];
        
        $css = "
/* Generated CSS for {$themeConfig['name']} theme */
:root {
    --primary-color: {$styles['primary_color']};
    --secondary-color: {$styles['secondary_color']};
    --accent-color: {$styles['accent_color']};
    --font-family: {$styles['font_family']};
}

body {
    font-family: var(--font-family);
    color: var(--secondary-color);
    line-height: 1.6;
}

.theme-primary {
    color: var(--primary-color);
}

.theme-secondary {
    color: var(--secondary-color);
}

.theme-accent {
    color: var(--accent-color);
}

.btn-primary {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.btn-primary:hover {
    background-color: var(--secondary-color);
    border-color: var(--secondary-color);
}

a {
    color: var(--primary-color);
}

a:hover {
    color: var(--secondary-color);
}

h1, h2, h3, h4, h5, h6 {
    color: var(--secondary-color);
}
";
        
        return $css;
    }
    
    /**
     * Get page meta data for SEO
     */
    public function getPageMeta($page)
    {
        $baseUrl = $this->getBaseUrl();
        $slug = isset($page['slug']) ? $page['slug'] : '';
        $pageUrl = $this->getPageUrl($slug);

        return [
            'title' => $page['meta_title'] ?? ($page['title'] ?? ''),
            'description' => $page['meta_description'] ?? ($page['excerpt'] ?? ''),
            'canonical' => $page['canonical_url'] ?? $pageUrl,
            'noindex' => !empty($page['noindex']),

            // Open Graph
            'og_title' => $page['meta_title'] ?? ($page['title'] ?? ''),
            'og_description' => $page['meta_description'] ?? ($page['excerpt'] ?? ''),
            'og_type' => $page['og_type'] ?? 'article',
            'og_url' => $pageUrl,
            'og_image' => !empty($page['og_image']) ? $this->resolveImageUrl($page['og_image']) : null,
            'og_site_name' => $this->config['site_name'] ?? ($_SERVER['HTTP_HOST'] ?? 'Website'),

            // Twitter Cards
            'twitter_card' => $page['twitter_card'] ?? 'summary',
            'twitter_title' => $page['meta_title'] ?? ($page['title'] ?? ''),
            'twitter_description' => $page['meta_description'] ?? ($page['excerpt'] ?? ''),
            'twitter_image' => !empty($page['og_image']) ? $this->resolveImageUrl($page['og_image']) : null,

            // Additional meta
            'author' => $page['author_name'] ?? null,
            'published_time' => $page['created_at'] ?? null,
            'modified_time' => $page['updated_at'] ?? null
        ];
    }
    
    /**
     * Get page URL
     */
    private function getPageUrl($slug)
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . $host . '/' . $slug;
    }
    
    /**
     * Get base URL
     */
    private function getBaseUrl()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . $host;
    }
    
    /**
     * Resolve image URL to absolute URL
     */
    private function resolveImageUrl($imageUrl)
    {
        // If already absolute URL, return as-is
        if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            return $imageUrl;
        }
        
        // If relative URL, make it absolute
        if (strpos($imageUrl, '/') === 0) {
            return $this->getBaseUrl() . $imageUrl;
        }
        
        // If just filename, assume it's in uploads directory
        return $this->getBaseUrl() . '/storage/uploads/' . $imageUrl;
    }
    
    /**
     * Generate HTML meta tags for a page
     */
    public function generateMetaTags($page)
    {
        $meta = $this->getPageMeta($page);
        $html = '';
        
        // Basic meta tags
        $html .= '<meta name="description" content="' . htmlspecialchars($meta['description']) . '">' . "\n";
        
        if ($meta['noindex']) {
            $html .= '<meta name="robots" content="noindex, nofollow">' . "\n";
        }
        
        // Canonical URL
        if ($meta['canonical']) {
            $html .= '<link rel="canonical" href="' . htmlspecialchars($meta['canonical']) . '">' . "\n";
        }
        
        // Open Graph tags
        $html .= '<meta property="og:title" content="' . htmlspecialchars($meta['og_title']) . '">' . "\n";
        $html .= '<meta property="og:description" content="' . htmlspecialchars($meta['og_description']) . '">' . "\n";
        $html .= '<meta property="og:type" content="' . htmlspecialchars($meta['og_type']) . '">' . "\n";
        $html .= '<meta property="og:url" content="' . htmlspecialchars($meta['og_url']) . '">' . "\n";
        
        if ($meta['og_site_name']) {
            $html .= '<meta property="og:site_name" content="' . htmlspecialchars($meta['og_site_name']) . '">' . "\n";
        }
        
        if ($meta['og_image']) {
            $html .= '<meta property="og:image" content="' . htmlspecialchars($meta['og_image']) . '">' . "\n";
            $html .= '<meta property="og:image:alt" content="' . htmlspecialchars($meta['og_title']) . '">' . "\n";
        }
        
        // Twitter Card tags
        $html .= '<meta name="twitter:card" content="' . htmlspecialchars($meta['twitter_card']) . '">' . "\n";
        $html .= '<meta name="twitter:title" content="' . htmlspecialchars($meta['twitter_title']) . '">' . "\n";
        $html .= '<meta name="twitter:description" content="' . htmlspecialchars($meta['twitter_description']) . '">' . "\n";
        
        if ($meta['twitter_image']) {
            $html .= '<meta name="twitter:image" content="' . htmlspecialchars($meta['twitter_image']) . '">' . "\n";
        }
        
        // Additional meta tags
        if ($meta['author']) {
            $html .= '<meta name="author" content="' . htmlspecialchars($meta['author']) . '">' . "\n";
        }
        
        if ($meta['published_time']) {
            $html .= '<meta property="article:published_time" content="' . htmlspecialchars($meta['published_time']) . '">' . "\n";
        }
        
        if ($meta['modified_time']) {
            $html .= '<meta property="article:modified_time" content="' . htmlspecialchars($meta['modified_time']) . '">' . "\n";
        }
        
        return $html;
    }
    
    /**
     * Get navigation pages for menu
     */
    public function getNavigationPages()
    {
        try {
            global $config;
            // Load config and create database connection
            $localConfig = include dirname(__DIR__, 2) . '/app/config.php';
            // error_log('DEBUG: ThemeManager.php DB config: ' . print_r($config['db'], true));
            $db = new \App\DB($config);

            // Get published pages for navigation (with parent_id) for current site
            $siteId = SiteHelper::getCurrentSiteId();
            $pages = $db->fetchAll("
                SELECT id, title, slug, menu_order, is_home, parent_id
                FROM cms_pages
                WHERE status = 'published' AND show_in_nav = 1 AND site_id = ?
                ORDER BY menu_order ASC, title ASC
            ", [$siteId]);

            // Build a map of id => page
            $pageMap = [];
            foreach ($pages as $page) {
                // Skip if this is the home page (by slug or is_home flag)
                if ($page['slug'] === 'home' || (isset($page['is_home']) && (int)$page['is_home'] === 1)) {
                    continue;
                }
                $page['children'] = [];
                $pageMap[$page['id']] = $page;
            }

            // Build the tree
            $tree = [];
            foreach ($pageMap as $id => &$page) {
                if ($page['parent_id'] && isset($pageMap[$page['parent_id']])) {
                    $pageMap[$page['parent_id']]['children'][] = &$page;
                } else {
                    $tree[] = &$page;
                }
            }
            unset($page); // break reference

            $navigation = [];
            // Add home link first
            $navigation[] = [
                'title' => 'Home',
                'url' => '/',
                'slug' => '',
                'is_home' => true,
                'children' => []
            ];

            // Add the tree (top-level pages)
            foreach ($tree as $page) {
                $navigation[] = $this->formatNavPage($page);
            }

            // Add user management links if user module is enabled
            if (!empty($config['modules']['user'])) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $sessionPrefix = $config['session_prefix'] ?? 'app_';
                $isLoggedIn = !empty($_SESSION[$sessionPrefix . 'user_id']);
                if ($isLoggedIn) {
                    $navigation[] = [
                        'title' => 'Profile',
                        'url' => '/user/profile',
                        'slug' => 'profile',
                        'is_home' => false,
                        'children' => []
                    ];
                    $navigation[] = [
                        'title' => 'Logout',
                        'url' => '/logout.php',
                        'slug' => 'logout',
                        'is_home' => false,
                        'children' => []
                    ];
                } else {
                    $navigation[] = [
                        'title' => 'Login',
                        'url' => '/user/login',
                        'slug' => 'login',
                        'is_home' => false,
                        'children' => []
                    ];
                    $navigation[] = [
                        'title' => 'Register',
                        'url' => '/user/register',
                        'slug' => 'register',
                        'is_home' => false,
                        'children' => []
                    ];
                }
            }

            return $navigation;
        } catch (\Exception $e) {
            // Fallback navigation if database fails
            return [
                ['title' => 'Home', 'url' => '/', 'slug' => '', 'is_home' => true, 'children' => []]
            ];
        }
    }

    /**
     * Helper to format a page for navigation output (recursive for children)
     */
    private function formatNavPage($page)
    {
        $item = [
            'title' => $page['title'],
            'url' => '/' . $page['slug'],
            'slug' => $page['slug'],
            'is_home' => false,
            'children' => []
        ];
        if (!empty($page['children'])) {
            foreach ($page['children'] as $child) {
                $item['children'][] = $this->formatNavPage($child);
            }
        }
        return $item;
    }
}
