<?php
namespace App\Modules\StrataCms\Models;

use App\DB;
use App\HtmlSanitizer;

/**
 * Class Page
 *
 * Manages pages and static content for the StrataPHP CMS.
 */
class Page
{
    /**
     * Find a page by ID
     */
    public function findById($id)
    {
        try {
            return $this->db->fetch("SELECT * FROM cms_pages WHERE id = ? AND status = 'published' AND (headless_only IS NULL OR headless_only = 0) LIMIT 1", [$id]);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Search published pages by title (LIKE %title%)
     */
    public function searchByTitle($title, $limit = 10)
    {
        try {
            $sql = "SELECT * FROM cms_pages WHERE status = 'published' AND (headless_only IS NULL OR headless_only = 0) AND title LIKE ? ORDER BY created_at DESC LIMIT " . (int)$limit;
            return $this->db->fetchAll($sql, ["%$title%"]);
        } catch (\Throwable $e) {
            return [];
        }
    }
    /**
     * @var DB Database connection
     */
    private $db;

    /**
     * Page constructor.
     * @param array|null $config
     * @throws \Exception
     */
    public function __construct($config = null)
    {
        try {
            if ($config) {
                $this->db = new DB($config);
            } else {
                $localConfig = include __DIR__ . '/../../../app/config.php';
                $this->db = new DB($localConfig);
            }
        } catch (\Throwable $e) {
            throw new \Exception("Failed to initialize database connection");
        }
    }

    /**
     * Get all pages for a specific site.
     * @param int $siteId
     * @param int|null $limit
     * @return array
     */
    public function getAllBySite($siteId, $limit = null, $forAdmin = false)
    {
        try {
            $params = [$siteId];
            if ($forAdmin) {
                $sql = "SELECT * FROM cms_pages WHERE site_id = ? ORDER BY created_at DESC";
                if ($limit) {
                    $sql .= " LIMIT " . (int)$limit;
                }
                return $this->db->fetchAll($sql, $params);
            }
            // Get the site's headless status
            $siteRow = $this->db->fetch("SELECT headless FROM sites WHERE id = ? LIMIT 1", [$siteId]);
            $isHeadless = $siteRow && isset($siteRow['headless']) ? (int)$siteRow['headless'] : 0;
            if ($isHeadless) {
                $sql = "SELECT * FROM cms_pages WHERE site_id = ? AND status = 'published' ORDER BY created_at DESC";
            } else {
                $sql = "SELECT * FROM cms_pages WHERE site_id = ? AND status = 'published' AND (headless_only IS NULL OR headless_only = 0) ORDER BY created_at DESC";
            }
            if ($limit) {
                $sql .= " LIMIT " . (int)$limit;
            }
            return $this->db->fetchAll($sql, $params);
        } catch (\Throwable $e) {
            // Log error or handle as needed
            return [];
        }
    }

    /**
     * Get the page marked as home (is_home = 1).
     * @return array|null
     */
    public function getHomePage()
    {
        try {
            return $this->db->fetch("SELECT * FROM cms_pages WHERE is_home = 1 AND status = 'published' AND (headless_only IS NULL OR headless_only = 0) LIMIT 1");
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get all published pages, ordered by menu_order and created_at.
     * @return array
     */
    public function getAllPublished()
    {
        $limit = func_num_args() > 0 ? (int)func_get_arg(0) : 10;
        try {
            $sql = "SELECT * FROM cms_pages WHERE status = 'published' AND (headless_only IS NULL OR headless_only = 0) ORDER BY menu_order ASC, created_at DESC LIMIT " . (int)$limit;
            return $this->db->fetchAll($sql);
        } catch (\Throwable $e) {
            return [];
        }
    }
    
    /**
     * Get page by slug
     */
    public function getBySlug($slug)
    {
        return $this->db->fetch("
            SELECT * FROM cms_pages 
            WHERE slug = ? AND status = 'published'
        ", [$slug]);
    }
    
    /**
     * Get page by ID
     */
    public function getById($id)
    {
        return $this->db->fetch("SELECT * FROM cms_pages WHERE id = ?", [$id]);
    }
    
    /**
     * Create new page
     */
    public function create($data)
    {
        try {
            if (empty($data['title'])) {
                throw new \InvalidArgumentException("Page title is required");
            }
            
            // Sanitize input data
            $sanitizedData = $this->sanitizePageData($data);
            
            return $this->db->query("
                INSERT INTO cms_pages (title, slug, content, excerpt, meta_title, meta_description, 
                                      og_image, og_type, twitter_card, canonical_url, noindex,
                                      status, template, menu_order, author_id, parent_id, site_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $sanitizedData['title'],
                $this->processSlug($sanitizedData['slug'] ?? '', $sanitizedData['title']),
                $sanitizedData['content'],
                $sanitizedData['excerpt'],
                $sanitizedData['meta_title'],
                $sanitizedData['meta_description'],
                $sanitizedData['og_image'],
                $sanitizedData['og_type'],
                $sanitizedData['twitter_card'],
                $sanitizedData['canonical_url'],
                $sanitizedData['noindex'],
                $sanitizedData['status'],
                $sanitizedData['template'],
                $sanitizedData['menu_order'],
                $sanitizedData['author_id'],
                $sanitizedData['parent_id'],
                $sanitizedData['site_id']
            ]);
        } catch (\Exception $e) {
            throw new \Exception("Failed to create page: " . $e->getMessage());
        }
    }
    
    /**
     * Update existing page
     */
    public function update($id, $data)
    {
        try {
            if (empty($data['title'])) {
                throw new \InvalidArgumentException("Page title is required");
            }
            
            // Sanitize input data
            $sanitizedData = $this->sanitizePageData($data);
            
            $result = $this->db->query("
                UPDATE cms_pages 
                SET title = ?, slug = ?, content = ?, excerpt = ?, meta_title = ?, 
                    meta_description = ?, og_image = ?, og_type = ?, twitter_card = ?, 
                    canonical_url = ?, noindex = ?, status = ?, template = ?, menu_order = ?, site_id = ?, updated_at = NOW()
                WHERE id = ?
            ", [
                $sanitizedData['title'],
                $this->processSlug($sanitizedData['slug'] ?? '', $sanitizedData['title'], $id),
                $sanitizedData['content'],
                $sanitizedData['excerpt'],
                $sanitizedData['meta_title'],
                $sanitizedData['meta_description'],
                $sanitizedData['og_image'],
                $sanitizedData['og_type'],
                $sanitizedData['twitter_card'],
                $sanitizedData['canonical_url'],
                $sanitizedData['noindex'],
                $sanitizedData['status'],
                $sanitizedData['template'],
                $sanitizedData['menu_order'],
                $sanitizedData['site_id'],
                $id
            ]);
            
            return $result;
        } catch (\Exception $e) {
            throw new \Exception("Failed to update page: " . $e->getMessage());
        }
    }
    
    /**
     * Get all pages with optional limit
     */
    public function getAll($limit = null)
    {
        try {
            $sql = "SELECT * FROM cms_pages ORDER BY created_at DESC";
            if ($limit) {
                $sql .= " LIMIT " . (int)$limit;
            }
            return $this->db->fetchAll($sql);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Delete page
     */
    public function delete($id)
    {
        try {
            return $this->db->query("DELETE FROM cms_pages WHERE id = ?", [$id]);
        } catch (\Exception $e) {
            throw new \Exception("Failed to delete page: " . $e->getMessage());
        }
    }
    
    /**
     * Generate unique slug from title
     */
    private function generateSlug($title, $id = null)
    {
        $slug = $this->sanitizeSlug($title);
        
        // Check if slug exists and make it unique
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug, $id)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Process slug input - either use provided slug or generate from title
     */
    private function processSlug($providedSlug, $title, $id = null)
    {
        // If a custom slug is provided, validate and use it
        if (!empty(trim($providedSlug))) {
            $slug = $this->sanitizeSlug($providedSlug);
            
            // Check for conflicts and make unique if needed
            $originalSlug = $slug;
            $counter = 1;
            
            while ($this->slugExists($slug, $id)) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            return $slug;
        }
        
        // Generate from title if no custom slug provided
        return $this->generateSlug($title, $id);
    }
    
    /**
     * Sanitize slug to alphanumeric and dashes only
     */
    private function sanitizeSlug($input)
    {
        // Convert to lowercase and replace non-alphanumeric with dashes
        $slug = strtolower(trim($input));
        $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug); // Remove multiple dashes
        $slug = trim($slug, '-'); // Remove leading/trailing dashes
        
        // Ensure minimum length
        if (empty($slug)) {
            $slug = 'page-' . time();
        }
        
        return $slug;
    }
    
    /**
     * Check if slug exists
     */
    private function slugExists($slug, $excludeId = null)
    {
        $sql = "SELECT id FROM cms_pages WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result !== false;
    }
    
    /**
     * Public method to check slug availability
     */
    public function isSlugAvailable($slug, $excludeId = null)
    {
        return !$this->slugExists($slug, $excludeId);
    }
    
    /**
     * Find page by slug
     */
    public function findBySlug($slug)
    {
        return $this->db->fetch("
            SELECT * FROM cms_pages 
            WHERE slug = ? AND status = 'published'
            ORDER BY created_at DESC
        ", [$slug]);
    }
    
    /**
     * Get the first published page
     */
    public function getFirstPublished()
    {
        return $this->db->fetch("
            SELECT * FROM cms_pages 
            WHERE status = 'published'
            ORDER BY created_at ASC
            LIMIT 1
        ");
    }
    
    /**
     * Sanitize page data for safe storage
     */
    private function sanitizePageData($data)
    {
        return [
            'title' => HtmlSanitizer::stripAll($data['title'] ?? ''),
            'slug' => $data['slug'] ?? '',  // Slug is processed separately
            'content' => HtmlSanitizer::sanitizeRichContent($data['content'] ?? ''),
            'excerpt' => HtmlSanitizer::stripAll($data['excerpt'] ?? ''),
            'meta_title' => HtmlSanitizer::stripAll($data['meta_title'] ?? $data['title'] ?? ''),
            'meta_description' => HtmlSanitizer::stripAll($data['meta_description'] ?? ''),
            'og_image' => filter_var($data['og_image'] ?? '', FILTER_SANITIZE_URL),
            'og_type' => $this->validateOgType($data['og_type'] ?? 'article'),
            'twitter_card' => $this->validateTwitterCard($data['twitter_card'] ?? 'summary_large_image'),
            'canonical_url' => filter_var($data['canonical_url'] ?? '', FILTER_SANITIZE_URL),
            'noindex' => ($data['noindex'] ?? 0) ? 1 : 0,
            'status' => in_array($data['status'] ?? 'draft', ['draft', 'published', 'private'])
                       ? $data['status'] : 'draft',
            'template' => preg_match('/^[a-zA-Z0-9_-]+$/', $data['template'] ?? 'default')
                         ? $data['template'] : 'default',
            'menu_order' => max(0, (int)($data['menu_order'] ?? 0)),
            'author_id' => (int)($data['author_id'] ?? 1),
            'parent_id' => isset($data['parent_id']) ? (int)$data['parent_id'] : null,
            'site_id' => isset($data['site_id']) ? (int)$data['site_id'] : null
        ];
    }
    
    /**
     * Validate Open Graph type
     */
    private function validateOgType($type)
    {
        $validTypes = ['website', 'article', 'product', 'profile'];
        return in_array($type, $validTypes) ? $type : 'article';
    }
    
    /**
     * Validate Twitter card type
     */
    private function validateTwitterCard($card)
    {
        $validCards = ['summary', 'summary_large_image', 'app', 'player'];
        return in_array($card, $validCards) ? $card : 'summary_large_image';
    }
}
