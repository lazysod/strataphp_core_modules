<?php
/**
 * CMS User Template Wrapper
 *
 * Provides CMS theming for user module pages
 */

namespace App\Modules\StrataCms;

class UserThemeWrapper
{
    /**
     * Render a user page with CMS theme
     */
    public static function renderUserPage($title, $content, $additionalMeta = [])
    {
        try {
            // Create page data for CMS theme
            $page = [
                'title' => $title,
                'content' => $content,
                'meta_description' => $additionalMeta['description'] ?? "User management - {$title}",
                'meta_title' => $additionalMeta['title'] ?? "{$title} | StrataPHP CMS",
                'slug' => $additionalMeta['slug'] ?? strtolower(str_replace(' ', '-', $title)),
                'status' => 'published',
                'template' => 'default',
                'og_image' => $additionalMeta['og_image'] ?? '',
                'og_type' => 'website',
                'twitter_card' => 'summary',
                'canonical_url' => $additionalMeta['canonical'] ?? '',
                'noindex' => $additionalMeta['noindex'] ?? false,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Use CMS theme manager
            $themeManager = new ThemeManager();
            $meta = $themeManager->getPageMeta($page);
            $navigation = $themeManager->getNavigationPages();
            $themeConfig = $themeManager->getThemeConfig();
            // Force site_name to be consistent for all CMS pages
            $siteName = $themeConfig['site_name'] ?? $themeConfig['name'] ?? 'StrataPHP CMS';
            $themeConfig['site_name'] = $siteName;
            $theme = [
                'config' => $themeConfig,
                'styles' => $themeConfig['styles'],
                'name' => $themeConfig['name'],
                'assets_url' => '/' . 'modules/cms/themes/' . $themeManager->getCurrentTheme() . '/assets',
                'css_url' => '/' . 'modules/cms/themes/' . $themeManager->getCurrentTheme() . '/assets/css/style.css',
                'js_url' => '/' . 'modules/cms/themes/' . $themeManager->getCurrentTheme() . '/assets/js/main.js',
            ];

            // Make variables available in template scope
            extract([
                'theme' => $theme,
                'navigation' => $navigation,
                'meta' => $meta,
                'page' => $page
            ]);

            // Include the correct CMS theme template
            include __DIR__ . '/themes/modern/templates/default.php';
        } catch (\Throwable $e) {
            echo '<div class="alert alert-danger">An error occurred rendering the user page.</div>';
        }
    }
    
    /**
     * Generate user form content with CMS styling
     */
    public static function generateFormContent($formTitle, $formContent, $error = '', $success = '')
    {
        $alertHtml = '';
        
        if (!empty($success)) {
            $alertHtml .= '<div style="padding: 1rem; margin-bottom: 1.5rem; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 8px; border-left: 4px solid #28a745;">
                <strong>✓ Success!</strong> ' . htmlspecialchars($success, ENT_QUOTES, 'UTF-8') . '
            </div>';
        }
        
        if (!empty($error)) {
            $alertHtml .= '<div style="padding: 1rem; margin-bottom: 1.5rem; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 8px; border-left: 4px solid #dc3545;">
                <strong>✗ Error!</strong> ' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '
            </div>';
        }

        return "
        <div style='max-width: 500px; margin: 0 auto;'>
            <div style='text-align: center; margin-bottom: 2rem;'>
                <h2 style='color: var(--secondary-color); margin-bottom: 0.5rem;'>{$formTitle}</h2>
                <p style='color: var(--text-light);'>Please fill out the form below</p>
            </div>
            
            {$alertHtml}
            
            <div style='background: white; padding: 2rem; border-radius: 12px; box-shadow: var(--shadow-medium); border: 1px solid var(--border-color);'>
                {$formContent}
            </div>
            
            <div style='text-align: center; margin-top: 2rem; font-size: 0.9rem; color: var(--text-light);'>
                <p>Need help? <a href='/'>Return to homepage</a> or <a href='/user/login'>login here</a></p>
            </div>
        </div>";
    }
}
