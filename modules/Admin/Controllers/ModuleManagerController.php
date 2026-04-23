<?php
namespace App\Modules\Admin\Controllers;

/**
 * Module Manager Controller
 *
 * Handles module management interface including enabling/disabling modules
 * and setting the default module
 */
class ModuleManagerController
{
    
    /**
     * Display module management interface
     *
     * @return void
     */
    public function index()
    {
        try {
            global $siteConfig;
            
                    require_once dirname(__DIR__, 3) . '/app/bootstrap.php';
                    global $siteConfig;
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handleFormSubmission();
                return;
            }
            
            // Scan filesystem for all modules and merge with config
            $modules = $this->scanModules($siteConfig);
            
            // Include the view directly with variables available
            include __DIR__ . '/../views/module_manager.php';
        } catch (\Exception $e) {
            http_response_code(500);
            echo '<h1>Error loading module manager</h1>';
        }
    }
    
    /**
     * Handle form submission directly in the index method
     */
    private function handleFormSubmission()
    {
        try {
            // (Debug logging removed)

            // Load current modules.php
            $modulesPath = __DIR__ . '/../../../app/modules.php';
            $modulesConfig = include $modulesPath;
            $enabled = $_POST['enabled'] ?? [];
            $enabled = array_unique($enabled);
            $enabledPresent = $_POST['enabled_present'] ?? [];

            // Update enabled state for all modules using enabled_present[]
            foreach ($modulesConfig['modules'] as $modName => &$modInfo) {
                $modNameLower = strtolower($modName);
                if ($modNameLower === 'admin' || $modNameLower === 'home') {
                    $modInfo['enabled'] = true;
                } else {
                    // If module is present in enabled_present, set enabled based on enabled[]
                    $modInfo['enabled'] = in_array($modNameLower, array_map('strtolower', $enabled));
                }
                if (!isset($modInfo['suitable_as_default'])) {
                    $modInfo['suitable_as_default'] = false;
                }
            }
            unset($modInfo);

            // Save default module selection
            if (isset($_POST['default_module']) && is_string($_POST['default_module']) && $_POST['default_module'] !== '') {
                $modulesConfig['default_module'] = $_POST['default_module'];
            }

            // Write back to modules.php only
            $modulesExport = var_export($modulesConfig, true);
            $modulesContent = "<?php\nreturn $modulesExport;\n";
            file_put_contents($modulesPath, $modulesContent, LOCK_EX);

            // (Debug logging removed)

            $_SESSION['success'] = 'Module configuration updated successfully.';
            header('Location: /admin/modules');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Error updating module configuration: ' . $e->getMessage();
            header('Location: /admin/modules');
            exit;
        }
    }
    
    /**
     * Scan filesystem for all modules and merge with config data
     */
    private function scanModules($siteConfig)
    {
        $modules = [];
        $modulesPath = $_SERVER['DOCUMENT_ROOT'] . '/modules';
        if (!is_dir($modulesPath)) {
            return isset($siteConfig['modules']) ? $siteConfig['modules'] : [];
        }
        // Scan filesystem for module directories
        $moduleDirectories = array_filter(glob($modulesPath . '/*'), 'is_dir');
        $existingModuleNames = [];
        foreach ($moduleDirectories as $moduleDir) {
            $moduleName = strtolower(basename($moduleDir));
            $existingModuleNames[] = $moduleName;
            $moduleIndexPath = $moduleDir . '/index.php';
            // Skip if no index.php exists (required for StrataPHP modules)
            if (!file_exists($moduleIndexPath)) {
                continue;
            }
            // Start with config data if exists
            $moduleData = isset($siteConfig['modules'][$moduleName]) ? $siteConfig['modules'][$moduleName] : [];
            // Set defaults for modules not in config
            if (!isset($moduleData['enabled'])) {
                $moduleData['enabled'] = false;
            }
            if (!isset($moduleData['suitable_as_default'])) {
                $moduleData['suitable_as_default'] = false;
            }
            $modules[$moduleName] = $moduleData;
        }
        // Remove config entries for modules that do not exist on disk
        if (isset($siteConfig['modules'])) {
            foreach ($siteConfig['modules'] as $modName => $modInfo) {
                $modNameLower = strtolower($modName);
                if (!in_array($modNameLower, $existingModuleNames)) {
                    unset($modules[$modNameLower]);
                }
            }
        }
        return $modules;
    }

    /**
     * Security: Validate configuration file path
     */
    private function isSecureConfigPath($configPath)
    {
        $realPath = realpath(dirname($configPath));
        $expectedPath = realpath(__DIR__ . '/../../../app');
        
        if ($realPath === false || $expectedPath === false) {
            return false;
        }
        
        // Ensure we're only writing to the app directory
        return $realPath === $expectedPath && basename($configPath) === 'config.php';
    }
    
    /**
     * Security: Safe configuration file writing with backup
     */
    private function secureConfigWrite($configPath, $content)
    {
        // Validate content is PHP
        if (!str_starts_with($content, '<?php')) {
            return false;
        }
        
        // Write new config (no backup)
        $result = file_put_contents($configPath, $content, LOCK_EX);
        return $result !== false;
    }
    
    /**
     * Clean up old configuration backups
     */
    private function cleanupConfigBackups($configDir)
    {
        $backups = glob($configDir . '/config.php.backup.*');
        if (count($backups) > 5) {
            rsort($backups); // Sort by filename (timestamp)
            $toDelete = array_slice($backups, 5);
            foreach ($toDelete as $backup) {
                unlink($backup);
            }
        }
    }
    
    /**
     * Delete a module with safety checks
     */
    public function delete($moduleName = null)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid request method']);
                return;
            }
            
            // Get module name from route parameter or POST data (for backward compatibility)
            if (empty($moduleName)) {
                $moduleName = $_POST['module_name'] ?? '';
            }
            
            // Validate module name
            if (empty($moduleName)) {
                $this->jsonResponse(['success' => false, 'message' => 'Module name is required']);
                return;
            }
            
            // For safety, we still want user confirmation, but we'll get it from the frontend
            // The frontend should have already validated the user typed the correct name
            
            // Check if module can be deleted
            $protectionCheck = $this->checkModuleProtection($moduleName);
            if (!$protectionCheck['can_delete']) {
                $this->jsonResponse(['success' => false, 'message' => $protectionCheck['reason']]);
                return;
            }
            
            // Create backup before deletion
            $backupPath = $this->createModuleBackup($moduleName);
            
                        // Perform the deletion
            $result = $this->performModuleDeletion($moduleName, false); // Default to not keeping data
            
            if ($result['success']) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Module deleted successfully',
                    'backup_path' => $backupPath
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => $result['message']]);
            }
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'An error occurred during deletion']);
        }
    }
    
    /**
     * Check if a module can be safely deleted
     */
    private function checkModuleProtection($moduleName)
    {
        global $siteConfig;
        
        // System modules that cannot be deleted
        $systemModules = ['admin', 'user'];
        if (in_array($moduleName, $systemModules)) {
            return [
                'can_delete' => false,
                'reason' => 'Cannot delete system module: ' . $moduleName
            ];
        }
        
        // Check if it's the default module
        $defaultModule = $siteConfig['default_module'] ?? '';
        if ($moduleName === $defaultModule) {
            return [
                'can_delete' => false,
                'reason' => 'Cannot delete the default module. Change default module first.'
            ];
        }
        
        // Check if other modules depend on this one
        $dependencies = $this->checkModuleDependencies($moduleName);
        if (!empty($dependencies)) {
            return [
                'can_delete' => false,
                'reason' => 'Cannot delete module. Required by: ' . implode(', ', $dependencies)
            ];
        }
        
        // Check if module exists
        $moduleDir = dirname(__DIR__, 2) . '/' . $moduleName;
        if (!is_dir($moduleDir)) {
            return [
                'can_delete' => false,
                'reason' => 'Module directory does not exist'
            ];
        }
        
        return ['can_delete' => true, 'reason' => ''];
    }
    
    /**
     * Check which modules depend on the given module
     */
    private function checkModuleDependencies($moduleName)
    {
        $dependentModules = [];
        $modulesDir = dirname(__DIR__, 2);
        
        if (!is_dir($modulesDir)) {
            return $dependentModules;
        }
        
        $dirs = scandir($modulesDir);
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..' || $dir === $moduleName) {
                continue;
            }
            
            $moduleIndexPath = $modulesDir . '/' . $dir . '/index.php';
            if (file_exists($moduleIndexPath)) {
                $moduleData = include $moduleIndexPath;
                $dependencies = $moduleData['dependencies'] ?? [];
                
                if (in_array($moduleName, $dependencies)) {
                    $dependentModules[] = $dir;
                }
            }
        }
        
        return $dependentModules;
    }
    
    /**
     * Create a backup of the module before deletion
     */
    private function createModuleBackup($moduleName)
    {
        $moduleDir = dirname(__DIR__, 2) . '/' . $moduleName;
        $backupDir = dirname(__DIR__, 4) . '/storage/backups/modules';
        
        // Create backup directory if it doesn't exist
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d_H-i-s');
        $backupPath = $backupDir . '/' . $moduleName . '_' . $timestamp . '.zip';
        
        // Create ZIP backup
        $zip = new \ZipArchive();
        if ($zip->open($backupPath, \ZipArchive::CREATE) === true) {
            $this->addDirectoryToZip($zip, $moduleDir, $moduleName);
            $zip->close();
        }
        
        return $backupPath;
    }
    
    /**
     * Recursively add directory to ZIP archive
     */
    private function addDirectoryToZip($zip, $dir, $base = '')
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            $relativePath = str_replace($dir . '/', '', $file->getRealPath());
            if ($file->isDir()) {
                $zip->addEmptyDir($base . '/' . $relativePath);
            } else {
                $zip->addFile($file->getRealPath(), $base . '/' . $relativePath);
            }
        }
    }
    
    /**
     * Perform the actual module deletion
     */
    private function performModuleDeletion($moduleName, $keepData = false)
    {
        try {
            global $siteConfig;
            
            // 1. Remove from config
            if (isset($siteConfig['modules'][$moduleName])) {
                unset($siteConfig['modules'][$moduleName]);
                
                // Save updated config
                $configPath = dirname(__DIR__, 3) . '/app/config.php';
                $configContent = "<?php\nreturn " . var_export($siteConfig, true) . ";\n";
                file_put_contents($configPath, $configContent);
            }
            
            // 2. Remove from composer.json
            $this->removeFromComposer($moduleName);
            
            // 3. Remove module directory
            $moduleDir = dirname(__DIR__, 2) . '/' . $moduleName;
            if (is_dir($moduleDir)) {
                $this->removeDirectory($moduleDir);
            }
            
            // 4. Optionally drop database tables
            if (!$keepData) {
                $this->dropModuleTables($moduleName);
            }
            
            return ['success' => true, 'message' => 'Module deleted successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Deletion failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Remove module from composer.json
     */
    private function removeFromComposer($moduleName)
    {
        $composerPath = dirname(__DIR__, 4) . '/composer.json';
        if (!file_exists($composerPath)) {
            return;
        }
        
        $composer = [];
        try {
            if (is_readable($composerPath)) {
                $composerContent = file_get_contents($composerPath);
                $composer = json_decode($composerContent, true);
            } else {
                error_log('Composer file not readable: ' . $composerPath);
            }
        } catch (\Exception $e) {
            error_log('Error reading composer file: ' . $e->getMessage());
        }
        $moduleClass = ucfirst($moduleName);
        $namespace = "App\\Modules\\{$moduleClass}\\";
        
        // Remove PSR-4 autoloading entries
        if (isset($composer['autoload']['psr-4'][$namespace . "Controllers\\"])) {
            unset($composer['autoload']['psr-4'][$namespace . "Controllers\\"]);
        }
        if (isset($composer['autoload']['psr-4'][$namespace . "Models\\"])) {
            unset($composer['autoload']['psr-4'][$namespace . "Models\\"]);
        }
        
        file_put_contents($composerPath, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
    
    /**
     * Recursively remove directory
     */
    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        
        rmdir($dir);
    }
    
    /**
     * Drop module-specific database tables
     */
    private function dropModuleTables($moduleName)
    {
        // This is optional and dangerous - only drop if explicitly requested
        // Implementation would depend on your database structure
        // For now, just log the intention
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
