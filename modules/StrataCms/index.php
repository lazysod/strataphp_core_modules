<?php
// Module metadata for StrataPHP CMS module
return [
    'name' => 'StrataPHP CMS',
    'slug' => 'strata-cms',
    'version' => '1.0.0',
    'description' => 'Professional Content Management System with pages, posts, menus, and dynamic routing. The flagship CMS module for StrataPHP.',
    'author' => 'StrataPHP Framework',
    'category' => 'Content',
    'license' => 'MIT',
    'homepage' => 'https://github.com/strataphp/cms-module',
    'repository' => 'https://github.com/strataphp/cms-module.git',
    'support_url' => 'https://github.com/strataphp/cms-module/issues',
    'documentation' => 'https://docs.strataphp.com/modules/cms',
    'update_url' => '', // Optional: URL to check for updates
    'enabled' => false,
    'suitable_as_default' => false,
    'dependencies' => [
        'user' => 'User >=1.0.0',  // Requires user management for authors
        'media' => 'Media >=1.0.0'  // Requires media management for media library
    ],
    'permissions' => [
        'cms.pages.create', 'cms.pages.read', 'cms.pages.update', 'cms.pages.delete',
        'cms.posts.create', 'cms.posts.read', 'cms.posts.update', 'cms.posts.delete',
        'cms.menus.create', 'cms.menus.read', 'cms.menus.update', 'cms.menus.delete',
        'cms.admin', 'cms.settings'
    ],
    'requirements' => [
        'php' => '>=7.4',
        'mysql' => '>=5.7'
    ],
    'features' => [
        'Page Management',
        'Blog Posts',
        'Menu Builder',
        'Dynamic Routing',
        'SEO Optimization',
        'Template System',
        'Media Library',
        'Revision History'
    ],
    'tags' => ['cms', 'content', 'pages', 'blog', 'seo', 'management'],
    'screenshots' => [
        '/modules/cms/assets/screenshots/dashboard.png',
        '/modules/cms/assets/screenshots/page-editor.png',
        '/modules/cms/assets/screenshots/menu-builder.png'
    ],
    'database_tables' => [
        'cms_pages',
        'cms_posts',
        'cms_menus',
        'cms_content_revisions'
    ]
];
