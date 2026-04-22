<?php
namespace App\Modules\User\Helpers;

use App\App;

/**
 * CMS Fallback Helper
 *
 * Provides utilities for graceful fallback when CMS module is disabled
 * Helps maintain compatibility between CMS-enhanced and basic StrataPHP modes
 */
class CmsHelper
{
    /**
     * Check if CMS module is enabled and available
     *
     * @return bool
     */
    public static function isCmsEnabled(): bool
    {
        try {
            $modules = App::config('modules');
            $enabled = !empty($modules['cms']['enabled']);
            return $enabled;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Check if CMS theme view exists and CMS is enabled
     *
     * @param string $viewPath Relative path to CMS view
     * @return bool
     */
    public static function isCmsViewAvailable(string $viewPath): bool
    {
        if (!self::isCmsEnabled()) {
            return false;
        }
        $fullPath = dirname(__DIR__, 2) . '/cms/views/' . $viewPath;
        $exists = file_exists($fullPath);
        return $exists;
    }
    
    /**
     * Get appropriate view path with CMS fallback
     *
     * @param string $cmsViewPath Path to CMS view (e.g., 'user/login.php')
     * @param string $defaultViewPath Path to default view (e.g., '../views/login.php')
     * @return string Full path to the view to use
     */
    public static function getViewPath(string $cmsViewPath, string $defaultViewPath): string
    {
        if (self::isCmsViewAvailable($cmsViewPath)) {
            return dirname(__DIR__, 2) . '/cms/views/' . $cmsViewPath;
        }
        // Return path relative to the calling controller
        return $defaultViewPath;
    }
    
    /**
     * Get appropriate redirect URL based on user role and CMS availability
     *
     * @param bool $isAdmin Whether user is admin
     * @param string $fallbackUrl Default fallback URL
     * @return string Redirect URL
     */
    public static function getPostLoginRedirect(bool $isAdmin, string $fallbackUrl = '/'): string
    {
        if ($isAdmin) {
            // Admin users: prefer CMS admin if available, otherwise basic admin
            return self::isCmsEnabled() ? '/admin/cms' : '/admin';
        }
        
        // Regular users: prefer profile if available, otherwise fallback
        return '/user/profile';
    }
    
    /**
     * Get appropriate admin redirect for already-logged-in users
     *
     * @param bool $isAdmin Whether user is admin
     * @return string Redirect URL
     */
    public static function getLoggedInRedirect(bool $isAdmin): string
    {
        if ($isAdmin) {
            return self::isCmsEnabled() ? '/admin/cms' : '/admin';
        }
        
        return '/user/profile';
    }
}
