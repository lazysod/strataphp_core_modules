<?php
/**
 * CMS 404 Page Template
 *
 * Styled 404 page using the CMS theme system
 */

// Use CMS theme system for 404 pages
use App\Modules\Cms\ThemeManager;

// Create fake page data for 404
$page = [
    'title' => 'Page Not Found',
    'content' => '
        <div style="text-align: center; padding: 2rem 0;">
            <h2>Oops! Page Not Found</h2>
            <p style="font-size: 1.2rem; color: #6c757d; margin: 2rem 0;">The page you\'re looking for doesn\'t exist or has been moved.</p>
            <div style="margin: 2rem 0;">
                <a href="/" style="display: inline-block; background: var(--primary-color); color: white; padding: 1rem 2rem; text-decoration: none; border-radius: 8px; margin: 0.5rem;">
                    🏠 Go Home
                </a>
                <a href="/user/login" style="display: inline-block; background: var(--secondary-color); color: white; padding: 1rem 2rem; text-decoration: none; border-radius: 8px; margin: 0.5rem;">
                    👤 Login
                </a>
            </div>
            <div style="margin-top: 3rem; padding: 2rem; background: rgba(52, 152, 219, 0.05); border-radius: 12px; border-left: 4px solid var(--primary-color);">
                <h3>What can you do?</h3>
                <ul style="text-align: left; display: inline-block; margin: 1rem 0;">
                    <li>Check the URL for typos</li>
                    <li>Go back to the <a href="/">homepage</a></li>
                    <li>Use the navigation menu above</li>
                    <li>Search for what you need</li>
                </ul>
            </div>
        </div>
    ',
    'meta_description' => 'Page not found - 404 error',
    'meta_title' => 'Page Not Found | StrataPHP CMS',
    'slug' => '404',
    'status' => 'published',
    'template' => 'default',
    'og_image' => '',
    'og_type' => 'website',
    'twitter_card' => 'summary',
    'canonical_url' => '',
    'noindex' => true,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];

// Get theme meta
$themeManager = new ThemeManager();
$meta = $themeManager->getPageMeta($page);

// Get navigation
$navigation = $themeManager->getNavigationPages();

// Include the theme template
include dirname(__DIR__, 3) . '/themes/cms/modern/templates/base.php';
