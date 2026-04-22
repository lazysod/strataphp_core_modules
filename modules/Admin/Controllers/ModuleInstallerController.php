<?php
namespace App\Modules\Admin\Controllers;

use App\DB;
use App\View;
use App\App;

class ModuleInstallerController
{
    private $db;
    private $config;
    private $view;
    
    public function __construct()
    {
        $this->config = include dirname(__DIR__, 3) . '/app/config.php';
        $this->db = new DB($this->config);
        $this->view = new View($this->config);
    }
    
    /**
     * Show the module installer interface
     */
    public function index()
    {
        // Check admin authentication
        if (!$this->isAuthenticated()) {
            header('Location: /admin/admin_login.php');
            exit;
        }
        
        // Ensure CSRF token is set in session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $data = [
            'title' => 'Module Installer',
            'installedModules' => $this->getInstalledModules(),
            'maxFileSize' => $this->getMaxUploadSize(),
            'tempDir' => sys_get_temp_dir(),
            'tempDirInfo' => $this->getTempDirectoryInfo(),
            'controller' => $this
        ];

        // Include the view directly since it's in the module's views directory
        extract($data);
        include dirname(__DIR__) . '/views/module-installer.php';
    }
    
    /**
     * Handle ZIP file upload and installation
     */
    public function uploadInstall()
    {
        // error_log('UPLOAD: uploadInstall() called');
        if (!$this->isAuthenticated()) {
            // error_log('UPLOAD: Not authenticated');
            return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized']);
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // error_log('UPLOAD: Invalid request method');
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
        }
        
        // Validate CSRF token
        if (!$this->validateCsrfToken()) {
            // error_log('UPLOAD: Invalid CSRF token');
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid CSRF token']);
        }
        
        try {
            // error_log('UPLOAD: Passed CSRF and method checks');
            // Debug: Log $_FILES information
            // error_log('UPLOAD: $_FILES=' . print_r($_FILES, true));
            // Check if file was uploaded
            if (!isset($_FILES['module_zip'])) {
                // error_log('UPLOAD: No file uploaded');
                throw new \Exception('No file uploaded: module_zip field not found in request');
            }
            
            $uploadError = $_FILES['module_zip']['error'];
            if ($uploadError !== UPLOAD_ERR_OK) {
                $errorMessages = [
                    UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
                    UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
                    UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'File upload stopped by PHP extension'
                ];
                $errorMsg = isset($errorMessages[$uploadError])
                    ? $errorMessages[$uploadError]
                    : "Unknown upload error code: $uploadError";
                // error_log('UPLOAD: Upload error: ' . $errorMsg);
                throw new \Exception("Upload error: $errorMsg");
            }
            
            $uploadedFile = $_FILES['module_zip'];
            
            // Validate file type
            if (!$this->isValidZipFile($uploadedFile)) {
                throw new \Exception('Invalid file type. Only ZIP files are allowed.');
            }
            // Create temporary directory for extraction
            $tempDir = $this->createTempDirectory();
            $zipPath = $tempDir . '/' . basename($uploadedFile['name']);
            // Move uploaded file to temp directory
            if (!move_uploaded_file($uploadedFile['tmp_name'], $zipPath)) {
                throw new \Exception('Failed to move uploaded file');
            }
            
            // Extract ZIP file
            $extractDir = $tempDir . '/extracted';
            if (!$this->extractZipFile($zipPath, $extractDir)) {
                throw new \Exception('Failed to extract ZIP file');
            }
            // Find module directory in extracted files
            $modulePath = $this->findModuleDirectory($extractDir);

            if (!$modulePath) {
                // Provide more detailed error information
                $allFiles = $this->listExtractedContents($extractDir);
                $errorMsg = 'Invalid module structure. Could not find a valid module directory.\n\n';
                $errorMsg .= 'Found contents: ' . implode(', ', array_slice($allFiles, 0, 10));
                if (count($allFiles) > 10) {
                    $errorMsg .= '... (and ' . (count($allFiles) - 10) . ' more)';
                }
                $errorMsg .= '\n\nTo fix this:\n';
                $errorMsg .= '1. Ensure your module has an index.php file with valid metadata\n';
                $errorMsg .= '2. For repositories with multiple modules, add a .strataphp-modules file\n';
                $errorMsg .= '3. See documentation for proper module structure';
                throw new \Exception($errorMsg);
            }
            
            // Install the module using our existing installer
            $result = $this->installModuleFromPath($modulePath);
            // Clean up temporary files (including uploaded ZIP)
            $this->cleanupTempDirectory($tempDir);
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Module installed successfully!',
                'module' => $result
            ]);
        } catch (\Exception $e) {
            // Clean up on error (including uploaded ZIP)
            if (isset($tempDir)) {
                $this->cleanupTempDirectory($tempDir);
            }
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Installation failed: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Install module from URL (GitHub, ZIP URL, etc.)
     */
    public function urlInstall()
    {
        if (!$this->isAuthenticated()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized']);
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
        }
        
        // Validate CSRF token
        if (!$this->validateCsrfToken()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid CSRF token']);
        }
        
        try {
            $sourceUrl = $_POST['source_url'] ?? '';
            
            if (empty($sourceUrl)) {
                throw new \Exception('Source URL is required');
            }
            
            // Validate URL format
            if (!filter_var($sourceUrl, FILTER_VALIDATE_URL)) {
                throw new \Exception('Invalid URL format');
            }
            
            // Use the CLI installer script
            $installerPath = dirname(__DIR__, 4) . '/bin/install-module.php';
            $phpPath = $this->findPhpExecutable();
            $command = $phpPath . " " . escapeshellarg($installerPath) . " " . escapeshellarg($sourceUrl) . " 2>&1";

            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                throw new \Exception('Installation failed: ' . implode("\n", $output));
            }
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Module installed successfully!',
                'output' => implode("\n", $output)
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Installation failed: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Generate a new module using the CLI generator
     */
    public function generateModule()
    {
        if (!$this->isAuthenticated()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Unauthorized']);
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
        }
        
        // Validate CSRF token
        if (!$this->validateCsrfToken()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid CSRF token']);
        }
        
        try {
            $moduleName = $_POST['module_name'] ?? '';
            
            if (empty($moduleName)) {
                throw new \Exception('Module name is required');
            }
            
            // Validate module name
            if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/', $moduleName)) {
                throw new \Exception('Invalid module name. Use only letters, numbers, underscores, and hyphens.');
            }
            
            // Use the CLI generator script
            $generatorPath = dirname(__DIR__, 4) . '/bin/create-module.php';
            
            // Try to find PHP executable - works for both MAMP and cPanel
            $phpPath = $this->findPhpExecutable();
            $command = $phpPath . " " . escapeshellarg($generatorPath) . " " . escapeshellarg($moduleName) . " 2>&1";
            
            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                throw new \Exception('Generation failed: ' . implode("\n", $output));
            }
            
            // Add the module to the config file
            $this->addModuleToConfig($moduleName);
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Module generated successfully!',
                'output' => implode("\n", $output)
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Generation failed: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Add a newly generated module to the config file
     */
    private function addModuleToConfig($moduleName)
    {
        $configPath = dirname(__DIR__, 3) . '/app/config.php';
        
        if (!file_exists($configPath)) {
            throw new \Exception('Config file not found');
        }
        
        // Load current config
        $currentConfig = include $configPath;
        
        // Check if module already exists in config
        if (isset($currentConfig['modules'][$moduleName])) {
            return; // Already exists, no need to add
        }
        
        // Get module metadata to determine default settings
        $moduleIndexPath = dirname(__DIR__, 2) . "/{$moduleName}/index.php";
        $moduleData = [];
        
        if (file_exists($moduleIndexPath)) {
            $moduleData = include $moduleIndexPath;
        }
        
        // Add module to config with sensible defaults
        $currentConfig['modules'][$moduleName] = [
            'enabled' => $moduleData['enabled'] ?? false,
            'suitable_as_default' => $moduleData['suitable_as_default'] ?? false,
        ];
        
        // Write config back to file
        $configContent = "<?php\nreturn " . var_export($currentConfig, true) . ";\n";
        
        if (!file_put_contents($configPath, $configContent)) {
            throw new \Exception('Failed to update config file');
        }
    }
    
    /**
     * Get list of installed modules
     */
    private function getInstalledModules()
    {
        $modules = [];
        $modulesDir = dirname(__DIR__, 3) . '/modules';
        
        if (is_dir($modulesDir)) {
            $dirs = scandir($modulesDir);
            // Build a lowercase map of config keys for case-insensitive lookup
            $configModules = $this->config['modules'] ?? [];
            $configKeyMap = [];
            foreach ($configModules as $key => $val) {
                $configKeyMap[strtolower($key)] = $key;
            }
            foreach ($dirs as $dir) {
                if ($dir === '.' || $dir === '..') {
                    continue;
                }
                $moduleDir = $modulesDir . '/' . $dir;
                $indexFile = $moduleDir . '/index.php';
                if (is_dir($moduleDir) && file_exists($indexFile)) {
                    $moduleData = include $indexFile;
                    if (!is_array($moduleData)) {
                        continue;
                    }
                    $moduleData['directory'] = $dir;
                    // Case-insensitive config lookup
                    $configKey = $configKeyMap[strtolower($dir)] ?? null;
                    if ($configKey && isset($configModules[$configKey])) {
                        // Use config value if set, otherwise fallback to metadata
                        $moduleData['enabled'] = isset($configModules[$configKey]['enabled']) && $configModules[$configKey]['enabled'] !== ''
                            ? $configModules[$configKey]['enabled']
                            : ($moduleData['enabled'] ?? false);
                        $moduleData['suitable_as_default'] = isset($configModules[$configKey]['suitable_as_default']) && $configModules[$configKey]['suitable_as_default'] !== ''
                            ? $configModules[$configKey]['suitable_as_default']
                            : ($moduleData['suitable_as_default'] ?? false);
                    } else {
                        $moduleData['enabled'] = $moduleData['enabled'] ?? false;
                        $moduleData['suitable_as_default'] = $moduleData['suitable_as_default'] ?? false;
                    }
                    $modules[] = $moduleData;
                }
            }
        }
        return $modules;
    }
    
    /**
     * Check if user is authenticated as admin
     */
    private function isAuthenticated()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $sessionPrefix = $this->config['session_prefix'] ?? ($this->config['prefix'] ?? 'app_');
        return isset($_SESSION[$sessionPrefix . 'admin']) && $_SESSION[$sessionPrefix . 'admin'] > 0;
    }
    
    /**
     * Validate CSRF token
     */
    private function validateCsrfToken()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $token = $_POST['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        
        return !empty($token) && !empty($sessionToken) && hash_equals($sessionToken, $token);
    }
    
    /**
     * Generate CSRF token
     */
    public function getCsrfToken()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate ZIP file
     */
    private function isValidZipFile($file)
    {
        // error_log('UPLOAD: isValidZipFile() called');
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        // error_log('UPLOAD: ZIP extension check: ' . $extension);
        if ($extension !== 'zip') {
            // error_log('UPLOAD: isValidZipFile() failed extension');
            return false;
        }
        // Check MIME type
        $allowedMimes = ['application/zip', 'application/x-zip-compressed'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        // error_log('UPLOAD: ZIP mime check: ' . $mimeType);
        $result = in_array($mimeType, $allowedMimes);
        // error_log('UPLOAD: isValidZipFile() result: ' . ($result ? 'true' : 'false'));
        return $result;
    }
    
    /**
     * Create temporary directory
     */
    private function createTempDirectory()
    {
        // Clean up any old temp directories first (older than 1 hour)
        $this->cleanupOldTempDirectories();
        
        $tempDir = sys_get_temp_dir() . '/strataphp_module_' . uniqid();
        if (!mkdir($tempDir, 0755, true)) {
            throw new \Exception('Failed to create temporary directory');
        }
        return $tempDir;
    }
    
    /**
     * Clean up old temporary directories (older than 1 hour)
     */
    private function cleanupOldTempDirectories()
    {
        $tempBasePath = sys_get_temp_dir();
        $pattern = $tempBasePath . '/strataphp_module_*';
        $oldDirs = glob($pattern);
        
        if (!$oldDirs) {
            return;
        }
        
        $oneHourAgo = time() - 3600;
        $cleanedCount = 0;
        
        foreach ($oldDirs as $dir) {
            if (is_dir($dir) && filemtime($dir) < $oneHourAgo) {
                $this->cleanupTempDirectory($dir);
                $cleanedCount++;
            }
        }
        
        if ($cleanedCount > 0) {
        }
    }
    
    /**
     * Get information about temporary directories
     */
    private function getTempDirectoryInfo()
    {
        $tempBasePath = sys_get_temp_dir();
        $pattern = $tempBasePath . '/strataphp_module_*';
        $tempDirs = glob($pattern);
        
        return [
            'basePath' => $tempBasePath,
            'activeCount' => $tempDirs ? count($tempDirs) : 0,
            'freeSpace' => disk_free_space($tempBasePath),
            'totalSpace' => disk_total_space($tempBasePath)
        ];
    }
    
    /**
     * Extract ZIP file
     */
    private function extractZipFile($zipPath, $extractDir)
    {
        $zip = new \ZipArchive();
        $result = $zip->open($zipPath);
        
        if ($result !== true) {
            return false;
        }
        
        if (!mkdir($extractDir, 0755, true)) {
            $zip->close();
            return false;
        }
        
        $success = $zip->extractTo($extractDir);
        $zip->close();
        
        return $success;
    }
    
    /**
     * Find module directory in extracted files
     * Supports multiple detection strategies for different repository structures
     */
    private function findModuleDirectory($extractDir)
    {
        // error_log('UPLOAD: findModuleDirectory() called with extractDir=' . $extractDir);
        // First, check for a .strataphp-modules file that specifies module locations
        $moduleSpecFile = $extractDir . '/.strataphp-modules';
        if (file_exists($moduleSpecFile)) {
            // error_log('UPLOAD: .strataphp-modules file found: ' . $moduleSpecFile);
            $specifiedPath = $this->findModuleFromSpec($extractDir, $moduleSpecFile);
            if ($specifiedPath) {
                // error_log('UPLOAD: findModuleDirectory() returning from spec: ' . $specifiedPath);
                return $specifiedPath;
            }
        }
        $possibleModules = $this->scanForModules($extractDir);
        // error_log('UPLOAD: scanForModules found ' . count($possibleModules) . ' possible modules');
        // If exactly one module found, return it
        if (count($possibleModules) === 1) {
            // error_log('UPLOAD: findModuleDirectory() returning single found: ' . $possibleModules[0]);
            return $possibleModules[0];
        }
        // If multiple modules found, prefer the one with the most complete structure
        if (count($possibleModules) > 1) {
            $best = $this->selectBestModule($possibleModules);
            // error_log('UPLOAD: findModuleDirectory() returning best of multiple: ' . $best);
            return $best;
        }
        // error_log('UPLOAD: findModuleDirectory() returning null, no modules found');
        return null;
    }
    
    /**
     * Find module based on .strataphp-modules specification file
     */
    private function findModuleFromSpec($extractDir, $specFile)
    {
        try {
            $content = '';
            if (is_readable($specFile)) {
                $content = file_get_contents($specFile);
            } else {
                // error_log('Spec file not readable: ' . $specFile);
                throw new \Exception('Spec file not readable');
            }
            $lines = array_filter(array_map('trim', explode("\n", $content)));
            
            foreach ($lines as $line) {
                // Skip comments
                if (strpos($line, '#') === 0) {
                    continue;
                }
                
                $modulePath = $extractDir . '/' . ltrim($line, '/');
                if ($this->isValidModuleDirectory($modulePath)) {
                    return $modulePath;
                }
            }
        } catch (\Exception $e) {
        }
        
        return null;
    }
    
    /**
     * Scan directory tree for potential modules
     */
    private function scanForModules($baseDir, $maxDepth = 3, $currentDepth = 0)
    {
        $modules = [];
        
        if ($currentDepth > $maxDepth) {
            return $modules;
        }
        
        // Check if current directory is a module
        if ($this->isValidModuleDirectory($baseDir)) {
            $modules[] = $baseDir;
        }
        
        // Scan subdirectories
        if (is_dir($baseDir)) {
            $dirs = scandir($baseDir);
            foreach ($dirs as $dir) {
                if ($dir === '.' || $dir === '..' || !is_dir("$baseDir/$dir")) {
                    continue;
                }
                
                // Skip common non-module directories
                if (in_array($dir, ['.git', '.github', 'node_modules', 'vendor', 'tests', 'docs', 'documentation'])) {
                    continue;
                }
                
                $subModules = $this->scanForModules("$baseDir/$dir", $maxDepth, $currentDepth + 1);
                $modules = array_merge($modules, $subModules);
            }
        }
        
        return $modules;
    }
    
    /**
     * Check if a directory contains a valid module
     */
    private function isValidModuleDirectory($dir)
    {
        // Must have index.php
        if (!file_exists("$dir/index.php")) {
            // error_log('UPLOAD: isValidModuleDirectory() failed: index.php missing in ' . $dir);
            return false;
        }
        // Validate module metadata
        try {
            $metadata = include "$dir/index.php";
            if (!is_array($metadata)) {
                // error_log('UPLOAD: isValidModuleDirectory() failed: index.php did not return array in ' . $dir);
                return false;
            }
            $requiredFields = ['name', 'slug', 'version', 'description'];
            foreach ($requiredFields as $field) {
                if (!isset($metadata[$field]) || empty($metadata[$field])) {
                    // error_log('UPLOAD: isValidModuleDirectory() failed: missing or empty ' . $field . ' in ' . $dir);
                    return false;
                }
            }
            // error_log('UPLOAD: isValidModuleDirectory() success: ' . $dir);
            return true;
        } catch (\Exception $e) {
            // error_log('UPLOAD: isValidModuleDirectory() exception in ' . $dir . ': ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Select the best module from multiple candidates
     */
    private function selectBestModule($modules)
    {
        $scored = [];
        
        foreach ($modules as $modulePath) {
            $score = $this->scoreModule($modulePath);
            $scored[$modulePath] = $score;
        }
        
        // Return the highest scored module
        arsort($scored);
        return array_key_first($scored);
    }
    
    /**
     * Score a module based on completeness and structure
     */
    private function scoreModule($modulePath)
    {
        $score = 0;
        
        // Basic structure components
        $components = [
            'controllers' => 3,
            'models' => 3,
            'views' => 3,
            'assets' => 2,
            'routes.php' => 2,
            'README.md' => 1,
            'CHANGELOG.md' => 1
        ];
        
        foreach ($components as $component => $points) {
            if (file_exists("$modulePath/$component")) {
                $score += $points;
            }
        }
        
        // Bonus for being in a standard module directory structure
        if (basename($modulePath) !== basename(dirname($modulePath))) {
            $score += 2; // Likely in a dedicated module folder
        }
        
        return $score;
    }
    
    /**
     * List contents of extracted directory for debugging
     */
    private function listExtractedContents($dir, $prefix = '', $maxItems = 20)
    {
        $contents = [];
        $count = 0;
        
        if (!is_dir($dir)) {
            return ['[not a directory]'];
        }
        
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..' || $count >= $maxItems) {
                continue;
            }
            
            $path = $prefix . $item;
            if (is_dir("$dir/$item")) {
                $contents[] = $path . '/';
                
                // Don't recurse too deep
                if (strlen($prefix) < 20) {
                    $subContents = $this->listExtractedContents("$dir/$item", "$path/", 5);
                    $contents = array_merge($contents, array_slice($subContents, 0, 5));
                }
            } else {
                $contents[] = $path;
            }
            $count++;
        }
        
        return $contents;
    }
    
    /**
     * Install module from local path
     */
    private function installModuleFromPath($modulePath)
    {
        // Load module metadata
        $indexFile = $modulePath . '/index.php';
        if (!file_exists($indexFile)) {
            // error_log('UPLOAD: installModuleFromPath() failed: index.php not found in ' . $modulePath);
            throw new \Exception('Module index.php not found');
        }
        $moduleData = include $indexFile;
        // error_log('UPLOAD: installModuleFromPath() loaded metadata: ' . print_r($moduleData, true));
        $moduleName = $moduleData['slug'] ?? basename($modulePath);
        // Check if module already exists
        $targetDir = dirname(__DIR__, 2) . '/modules/' . $moduleName;
        // error_log('UPLOAD: installModuleFromPath() targetDir=' . $targetDir);
        if (is_dir($targetDir)) {
            // error_log('UPLOAD: installModuleFromPath() failed: module already exists at ' . $targetDir);
            throw new \Exception("Module '{$moduleName}' already exists");
        }
        // Copy module files
        // error_log('UPLOAD: installModuleFromPath() about to copy module files to ' . $targetDir);
        if (!$this->copyDirectory($modulePath, $targetDir)) {
            // error_log('UPLOAD: installModuleFromPath() failed: could not copy module files');
            throw new \Exception('Failed to copy module files');
        }
        // error_log('UPLOAD: installModuleFromPath() module files copied');
        // Update composer autoload
        // error_log('UPLOAD: installModuleFromPath() updating composer autoload');
        $this->updateComposerAutoload($moduleName);
        // Add to config
        // error_log('UPLOAD: installModuleFromPath() adding module to config');
        $this->addModuleToConfig($moduleName);
        // error_log('UPLOAD: installModuleFromPath() completed successfully');
        return $moduleData;
    }
    
    /**
     * Copy directory recursively
     */
    private function copyDirectory($source, $destination)
    {
        // error_log('UPLOAD: copyDirectory() called: source=' . $source . ' destination=' . $destination);
        if (!is_dir($source)) {
            // error_log('UPLOAD: copyDirectory() failed: source is not a directory');
            return false;
        }
        if (!mkdir($destination, 0755, true)) {
            // error_log('UPLOAD: copyDirectory() failed: could not create destination directory ' . $destination);
            return false;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            $relativePath = str_replace($source . DIRECTORY_SEPARATOR, '', $item->getPathname());
            $targetPath = $destination . DIRECTORY_SEPARATOR . $relativePath;
            if ($item->isDir()) {
                if (!mkdir($targetPath, 0755, true)) {
                    // error_log('UPLOAD: copyDirectory() failed: could not create subdirectory ' . $targetPath);
                    return false;
                }
                // error_log('UPLOAD: copyDirectory() created directory ' . $targetPath);
            } else {
                if (!copy($item, $targetPath)) {
                    // error_log('UPLOAD: copyDirectory() failed: could not copy file ' . $item->getPathname() . ' to ' . $targetPath);
                    return false;
                }
                // error_log('UPLOAD: copyDirectory() copied file ' . $item->getPathname() . ' to ' . $targetPath);
            }
        }
        // error_log('UPLOAD: copyDirectory() completed successfully');
        return true;
    }
    
    /**
     * Update composer autoload
     */
    private function updateComposerAutoload($moduleName)
    {
        $composerPath = dirname(__DIR__, 4) . '/composer.json';
        exec("cd " . dirname($composerPath) . " && composer dump-autoload --optimize");
    }
    
    /**
     * Clean up temporary directory and all files (including ZIP uploads)
     */
    private function cleanupTempDirectory($tempDir)
    {
        if (!is_dir($tempDir)) {
            return;
        }
        
        $fileCount = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tempDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item);
            } else {
                unlink($item);
                $fileCount++;
            }
        }
        
        rmdir($tempDir);
    }
    
    /**
     * Get maximum upload file size
     */
    private function getMaxUploadSize()
    {
        $maxUpload = $this->parseSize(ini_get('upload_max_filesize'));
        $maxPost = $this->parseSize(ini_get('post_max_size'));
        $memoryLimit = $this->parseSize(ini_get('memory_limit'));
        
        return min($maxUpload, $maxPost, $memoryLimit);
    }
    
    /**
     * Parse size string to bytes
     */
    private function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);
        
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }
        
        return round($size);
    }
    
    /**
     * Format bytes to human readable format
     */
    public function formatBytes($size)
    {
        if ($size == 0) {
            return '0 Bytes';
        }
        $unit = array('Bytes', 'KB', 'MB', 'GB', 'TB');
        $i = floor(log($size, 1024));
        return round($size / pow(1024, $i), 2) . ' ' . $unit[$i];
    }
    
    /**
     * Find the PHP executable path - works for MAMP, cPanel, and standard installations
     */
    private function findPhpExecutable()
    {
        // Use php_path from config if set, fallback to 'php'
        if (!empty($this->config['php_path'])) {
            return $this->config['php_path'];
        }
        return 'php';
    }
    
    /**
     * Return JSON response
     */
    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
