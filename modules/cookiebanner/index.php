<?php
// Module metadata for CookieBanner module
return [
    'name' => 'Cookie Banner',
    'slug' => 'cookiebanner',
    'version' => '1.0.0',
    'framework_version' => '>=1.0.0',
    'description' => 'Displays a cookie consent banner with customizable message and options.',
    'author' => 'StrataPHP Framework',
    'category' => 'Utility',
    'license' => 'MIT',
    'homepage' => 'https://strataphp.org',
    'repository' => 'https://github.com/lazysod/strataphp-public',
    'support_url' => 'https://strataphp.org',
    'enabled' => false,
    'suitable_as_default' => false,
    'dependencies' => [],
    'permissions' => [],
    'requirements' => [
        'php' => '>=7.4'
    ],
    'tags' => ['cookie', 'consent', 'privacy', 'banner'],
    'features' => [
        'Customizable message',
        'Configurable cookie name and duration',
        'Optional privacy/read more link',
        'Easy integration in any theme'
    ]
];
