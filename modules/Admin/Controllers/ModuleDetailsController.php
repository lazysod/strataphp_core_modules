<?php
namespace App\Modules\Admin\Controllers;

use App\DB;
use App\Services\ModuleValidator;

class ModuleDetailsController
{
    private $db;
    private $config;
    private $validator;
    
    public function __construct()
    {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_name('PHPSESSID');
            session_set_cookie_params([
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
        $this->config = include dirname(__DIR__, 3) . '/app/config.php';
        $this->db = new DB($this->config);
        $this->validator = new ModuleValidator();
    }
    
    /**
     * Show detailed information about a specific module
     */
    public function show($moduleSlug)
    {
        // Check admin authentication
        if (!$this->isAuthenticated()) {
            header('Location: /admin/admin_login.php');
            exit;
        }
        
        $modulePath = dirname(__DIR__, 3) . '/modules/' . $moduleSlug;
        
        if (!is_dir($modulePath)) {
            header('HTTP/1.0 404 Not Found');
            echo "Module not found";
            exit;
        }
        
        // Get module metadata
        $metadata = $this->getModuleMetadata($modulePath);
        
        // Get README content
        $readme = $this->getReadmeContent($modulePath);
        
        // Get changelog
        $changelog = $this->getChangelogContent($modulePath);
        
        // Validate module
        $validation = $this->validator->validateModule($modulePath);
        
        // Get module statistics
        $stats = $this->getModuleStats($modulePath);
        
        // Prepare data for the view - handle null metadata gracefully
        $metadata = $metadata ?? [];
        $moduleData = array_merge($metadata, [
            'name' => $metadata['name'] ?? ucfirst($moduleSlug),
            'slug' => $moduleSlug,
            'path' => $modulePath,
            'enabled' => $this->isModuleEnabled($moduleSlug),
            'version' => $metadata['version'] ?? 'Unknown',
            'description' => $metadata['description'] ?? 'No description available',
            'author' => $metadata['author'] ?? 'Unknown'
        ]);
        
        $readmeContent = $readme;
        $changelogContent = $changelog;
        $validationResults = $validation;
        $moduleStats = $stats;
        
        $data = [
            'title' => 'Module Details - ' . $moduleData['name'],
            'moduleData' => $moduleData,
            'readmeContent' => $readmeContent,
            'changelogContent' => $changelogContent,
            'validationResults' => $validationResults,
            'moduleStats' => $moduleStats
        ];
        
        // Include the view
        extract($data);
        include dirname(__DIR__) . '/views/module-details.php';
    }
    
    /**
     * Validate a specific module via AJAX
     */
    public function validate($module)
    {
                // Check admin authentication
        if (!$this->isAuthenticated()) {
            header('Location: /admin/admin_login.php');
            exit;
        }
                header('Content-Type: application/json');
        
        try {
            // Decode URL-encoded module name
            $moduleParam = urldecode($module);
            
            // First try to find module by directory name
            $modulePath = dirname(__FILE__, 3) . '/' . $moduleParam;
            
            if (!is_dir($modulePath)) {
                // If not found, search for module by display name
                $modulesDir = dirname(__FILE__, 3);
                $found = false;
                
                if (is_dir($modulesDir)) {
                    $directories = array_filter(glob($modulesDir . '/*'), 'is_dir');
                    
                    foreach ($directories as $dir) {
                        $indexFile = $dir . '/index.php';
                        if (file_exists($indexFile)) {
                            $metadata = include $indexFile;
                            if (is_array($metadata) && isset($metadata['name']) && $metadata['name'] === $moduleParam) {
                                $modulePath = $dir;
                                $found = true;
                                break;
                            }
                        }
                    }
                }
                
                if (!$found) {
                    echo json_encode(['success' => false, 'message' => 'Module not found: ' . $moduleParam]);
                    exit;
                }
            }
            
            $results = $this->validator->validateModule($modulePath);
            
            echo json_encode([
                'success' => true,
                'valid' => $results['valid'],
                'results' => $results
            ]);
            exit;
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
    
    /**
     * Show validation report for all modules
     */
    public function validateAll()
    {
        // Check admin authentication
        if (!$this->isAuthenticated()) {
            header('Location: /admin/admin_login.php');
            exit;
        }
        
        // Set up data that the view expects
        $config = $this->config;
        $modules = $config['modules'] ?? [];
        $baseDir = dirname(__DIR__, 3); // Points to public_html/
        
        // Load the validation report view (controller version)
        include dirname(__DIR__) . '/views/module-validation-report-controller.php';
    }
    
    /**
     * Get module metadata from index.php
     */
    private function getModuleMetadata($modulePath)
    {
        $indexFile = $modulePath . '/index.php';
        
        if (!file_exists($indexFile)) {
            return null;
        }
        
        try {
            $metadata = include $indexFile;
            if (!is_array($metadata)) {
                // If not an array, return null or handle as error
                return null;
            }
            // Add computed fields
            $metadata['path'] = $modulePath;
            $metadata['last_modified'] = date('Y-m-d H:i:s', filemtime($indexFile));
            $metadata['file_count'] = $this->countFiles($modulePath);
            $metadata['size'] = $this->getDirectorySize($modulePath);
            return $metadata;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Get README content
     */
    /**
     * Get README content with security validation
     */
    private function getReadmeContent($modulePath)
    {
        $readmeFiles = ['README.md', 'readme.md', 'README.txt', 'readme.txt'];
        
        foreach ($readmeFiles as $file) {
            $filePath = $modulePath . '/' . $file;
            if (file_exists($filePath) && $this->isSecureFilePath($filePath, $modulePath)) {
                $content = $this->secureFileRead($filePath);
                if ($content !== false) {
                    // Convert markdown to HTML if it's a .md file
                    if (str_ends_with($file, '.md')) {
                        return $this->parseMarkdown($content);
                    }
                    
                    return nl2br(htmlspecialchars($content));
                }
            }
        }
        
        return null;
    }
    
    /**
     * Get changelog content
     */
    private function getChangelogContent($modulePath)
    {
        $changelogFiles = ['CHANGELOG.md', 'changelog.md', 'CHANGELOG.txt'];
        
        foreach ($changelogFiles as $file) {
            $filePath = $modulePath . '/' . $file;
            if (file_exists($filePath) && $this->isSecureFilePath($filePath, $modulePath)) {
                $content = $this->secureFileRead($filePath);
                
                if (str_ends_with($file, '.md')) {
                    return $this->parseMarkdown($content);
                }
                
                return nl2br(htmlspecialchars($content));
            }
        }
        
        return null;
    }
    
    /**
     * Get module statistics
     */
    private function getModuleStats($modulePath)
    {
        $stats = [
            'total_files' => 0,
            'php_files' => 0,
            'js_files' => 0,
            'css_files' => 0,
            'image_files' => 0,
            'lines_of_code' => 0,
            'size_bytes' => 0,
            'controllers' => 0,
            'models' => 0,
            'views' => 0,
            'routes' => 0
        ];
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($modulePath)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $stats['total_files']++;
                $stats['size_bytes'] += $file->getSize();
                
                $extension = strtolower($file->getExtension());
                
                switch ($extension) {
                    case 'php':
                        $stats['php_files']++;
                        $stats['lines_of_code'] += $this->countLinesOfCode($file->getPathname());
                        
                        // Count controllers, models, views
                        if (strpos($file->getPathname(), '/controllers/') !== false) {
                            $stats['controllers']++;
                        } elseif (strpos($file->getPathname(), '/models/') !== false) {
                            $stats['models']++;
                        } elseif (strpos($file->getPathname(), '/views/') !== false) {
                            $stats['views']++;
                        }
                        break;
                        
                    case 'js':
                        $stats['js_files']++;
                        break;
                        
                    case 'css':
                        $stats['css_files']++;
                        break;
                        
                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                    case 'gif':
                    case 'svg':
                        $stats['image_files']++;
                        break;
                }
            }
        }
        
        // Count routes if routes.php exists
        $routesFile = $modulePath . '/routes.php';
        if (file_exists($routesFile) && $this->isSecureFilePath($routesFile, $modulePath)) {
            $routesContent = $this->secureFileRead($routesFile);
            // Count router method calls (get, post, put, delete, etc.)
            $stats['routes'] = preg_match_all('/\$router->(get|post|put|delete|patch|options|any)\s*\(/', $routesContent);
        }
        
        // Add fields expected by the view
        $stats['total_size'] = $stats['size_bytes'];
        $stats['last_modified'] = filemtime($modulePath);
        
        return $stats;
    }
    
    /**
     * Count lines of code in a PHP file
     */
    private function countLinesOfCode($filePath)
    {
        $content = $this->secureFileRead($filePath);
        if ($content === false) {
            return 0;
        }
        $lines = explode("\n", $content);
        $codeLines = 0;
        
        foreach ($lines as $line) {
            $line = trim($line);
            // Skip empty lines and comments
            if (!empty($line) && !str_starts_with($line, '//') && !str_starts_with($line, '*') && !str_starts_with($line, '/*')) {
                $codeLines++;
            }
        }
        
        return $codeLines;
    }
    
    /**
     * Count total files in directory
     */
    private function countFiles($directory)
    {
        $count = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Get directory size in bytes
     */
    private function getDirectorySize($directory)
    {
        $size = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        
        return $size;
    }
    
    /**
     * Simple markdown parser
     */
    private function parseMarkdown($content)
    {
        // Basic markdown parsing
        $content = htmlspecialchars($content);
        
        // Headers
        $content = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $content);
        $content = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $content);
        $content = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $content);
        
        // Bold and italic
        $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
        $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);
        
        // Code blocks
        $content = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $content);
        $content = preg_replace('/`(.*?)`/', '<code>$1</code>', $content);
        
        // Links
        $content = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank">$1</a>', $content);
        
        // Lists
        $content = preg_replace('/^- (.*$)/m', '<li>$1</li>', $content);
        $content = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $content);
        
        // Line breaks
        $content = nl2br($content);
        
        return $content;
    }
    
    /**
     * Format bytes to human readable format
     */
    public function formatBytes($bytes)
    {
        if ($bytes == 0) {
            return '0 Bytes';
        }
        $unit = array('Bytes', 'KB', 'MB', 'GB', 'TB');
        $i = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 2) . ' ' . $unit[$i];
    }
    
    /**
     * Check if admin is authenticated
     */
    private function isAuthenticated()
    {
        $sessionPrefix = $this->config['session_prefix'] ?? ($this->config['prefix'] ?? 'framework');
        return isset($_SESSION[$sessionPrefix . 'admin']) && $_SESSION[$sessionPrefix . 'admin'] >= 1;
    }
    
    /**
     * Check if a module is enabled
     */
    private function isModuleEnabled($moduleSlug)
    {
        return !empty($this->config['modules'][$moduleSlug]['enabled']);
    }
    
    /**
     * Return JSON response
     */
    /**
     * Return JSON response
     */
    private function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Security: Validate file path is within allowed directory
     */
    private function isSecureFilePath($filePath, $allowedBasePath)
    {
        $realPath = realpath($filePath);
        $realBasePath = realpath($allowedBasePath);
        
        if ($realPath === false || $realBasePath === false) {
            return false;
        }
        
        // Ensure the file is within the allowed base path
        return strpos($realPath, $realBasePath) === 0;
    }
    
    /**
     * Security: Safe file reading with size limits
     */
    private function secureFileRead($filePath, $maxSize = 1048576) // 1MB limit
    {
        if (!is_readable($filePath)) {
            return false;
        }
        
        $fileSize = filesize($filePath);
        if ($fileSize === false || $fileSize > $maxSize) {
            return false;
        }
        
        return file_get_contents($filePath);
    }
}
