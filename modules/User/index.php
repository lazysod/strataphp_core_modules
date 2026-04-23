<?php
// Module metadata for User Management module
return [
    'name' => 'User Management',
    'slug' => 'user',
    'version' => '1.0.0',
    'description' => 'Comprehensive user authentication and management system with registration, login, profiles, and password reset',
    'author' => 'StrataPHP Framework',
    'category' => 'Security',
    'license' => 'MIT',
    'framework_version' => '1.0.0',
    'repository' => 'https://github.com/lazysod/strataphp-public',
    'homepage' => 'https://github.com/lazysod/strataphp-public',
    'support_url' => 'https://github.com/lazysod/strataphp-public/issues',
    'structure_requirements' => [
        'controllers' => true,  // Needs controllers for auth logic
        'views' => true,        // Needs views for login/register forms
        'models' => true        // Needs models for user data
    ],
    'update_url' => '', // Optional: URL to check for updates
    'enabled' => true
];
