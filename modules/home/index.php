<?php
// Module metadata for Home module
return [
    'name' => 'Home Page',
    'slug' => 'home',
    'version' => '1.0.0',
    'description' => 'Minimal homepage module for serving as the main landing page',
    'author' => 'StrataPHP Framework',
    'category' => 'Content',
    'license' => 'MIT',
    'framework_version' => '1.0.0',
    'repository' => 'https://github.com/lazysod/strataphp-public',
    'homepage' => 'https://github.com/lazysod/strataphp-public',
    'support_url' => 'https://github.com/lazysod/strataphp-public/issues',
    'update' => true,
    'structure_requirements' => [
        'controllers' => false, // Simple module, no complex controllers needed
        'views' => false,       // Uses framework default views
        'models' => false       // No data persistence needed
    ],
    'update_url' => '', // Optional: URL to check for updates
    'enabled' => true,
    'suitable_as_default' => true,
    
];
