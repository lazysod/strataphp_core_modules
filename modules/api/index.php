<?php
return [
    'name' => 'API Module',
    'slug' => 'api',
    'version' => '1.0.0',
    'description' => 'REST API endpoints for the StrataPHP framework providing JSON-based data access',
    'author' => 'StrataPHP Framework',
    'category' => 'API',
    'license' => 'MIT',
    'framework_version' => '1.0.0',
    'repository' => 'https://github.com/lazysod/strataphp-public',
    'homepage' => 'https://github.com/lazysod/strataphp-public',
    'support_url' => 'https://github.com/lazysod/strataphp-public/issues',
    'update' => true,
    'structure_requirements' => [
        'controllers' => true,
        'views' => true,
        'models' => false  // API module doesn't need models - it's for endpoints only
    ],
    'update_url' => '',
    'enabled' => true
];
