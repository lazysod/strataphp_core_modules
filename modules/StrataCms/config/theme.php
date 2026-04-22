<?php
/**
 * CMS Theme Configuration
 *
 * Manages themes specifically for CMS content pages
 * Separate from main StrataPHP theme system
 */

return [
    // Default CMS theme
    'default_theme' => 'modern',
    
    // Available CMS themes
    'themes' => [
        'modern' => [
            'name' => 'Strata PHP: CMS',
            'site_name' => 'Strata PHP: CMS',
            'description' => 'Clean, modern design with responsive layout',
            'author' => 'StrataPHP',
            'version' => '1.0.0',
            'templates' => ['default', 'full-width', 'sidebar'],
            'styles' => [
                'primary_color' => '#3498db',
                'secondary_color' => '#2c3e50',
                'accent_color' => '#e74c3c',
                'font_family' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif'
            ]
        ],
        
        'minimal' => [
            'name' => 'Minimal',
            'description' => 'Minimalist design focused on content readability',
            'author' => 'StrataPHP',
            'version' => '1.0.0',
            'templates' => ['default', 'full-width'],
            'styles' => [
                'primary_color' => '#2c3e50',
                'secondary_color' => '#95a5a6',
                'accent_color' => '#3498db',
                'font_family' => 'Georgia, "Times New Roman", serif'
            ]
        ],
        
        'blog' => [
            'name' => 'Blog',
            'description' => 'Blog-focused theme with article styling',
            'author' => 'StrataPHP',
            'version' => '1.0.0',
            'templates' => ['default', 'sidebar', 'archive'],
            'styles' => [
                'primary_color' => '#2ecc71',
                'secondary_color' => '#34495e',
                'accent_color' => '#f39c12',
                'font_family' => '"Helvetica Neue", Helvetica, Arial, sans-serif'
            ]
        ]
    ],
    
    // Theme directory structure
    'paths' => [
        'themes_dir' => 'modules/StrataCms/themes',
        'templates_dir' => 'templates',
        'assets_dir' => 'assets',
        'cache_dir' => 'storage/cache/themes'
    ],
    
    // Template settings
    'templates' => [
        'default' => [
            'name' => 'Default',
            'description' => 'Standard page layout with header and footer',
            'file' => 'default.php'
        ],
        'full-width' => [
            'name' => 'Full Width',
            'description' => 'Full-width layout without sidebar',
            'file' => 'full-width.php'
        ],
        'sidebar' => [
            'name' => 'With Sidebar',
            'description' => 'Two-column layout with sidebar',
            'file' => 'sidebar.php'
        ],
        'archive' => [
            'name' => 'Archive',
            'description' => 'List layout for multiple pages',
            'file' => 'archive.php'
        ]
    ],
    
    // Asset management
    'assets' => [
        'css_minify' => true,
        'js_minify' => true,
        'cache_bust' => true,
        'cdn_enabled' => false
    ]
];
