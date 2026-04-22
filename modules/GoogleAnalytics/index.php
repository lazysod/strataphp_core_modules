<?php
// Module metadata for GoogleAnalytics module
return [
    'name' => 'Google Analytics',
    'slug' => 'google-analytics',
    'version' => '1.0.0',
    'description' => 'A comprehensive Google Analytics management module with CRUD operations, search, and pagination.',
    'author' => 'StrataPHP Framework',
    'category' => 'Analytics',
    'license' => 'MIT',
    'homepage' => 'https://github.com/strataphp/google_analytics-module',
    'repository' => 'https://github.com/strataphp/google_analytics-module.git',
    'support_url' => 'https://github.com/strataphp/google_analytics-module/issues',
    'update' => true,
    'update_url' => '', // Optional: URL to check for updates
    'enabled' => true,
    'suitable_as_default' => false,
    'dependencies' => [], // Other modules this depends on
    'permissions' => ['google-analytics.create', 'google-analytics.read', 'google-analytics.update', 'google-analytics.delete'], // Required permissions
    'requirements' => [
        'php' => '>=7.4',
        'mysql' => '>=5.7'
    ],
    'tags' => ['google-analytics', 'content', 'cms', 'crud'],
    // 'screenshots' => [],
];
