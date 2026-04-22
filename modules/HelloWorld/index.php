<?php
return [
    'name' => 'Hello World',
    'slug' => 'helloworld',
    'version' => '1.0.0',
    'description' => 'Simple "Hello World" demonstration module for learning StrataPHP framework basics',
    'author' => 'StrataPHP Framework',
    'category' => 'Development',
    'license' => 'MIT',
    'framework_version' => '1.0.0',
    'repository' => 'https://github.com/lazysod/strataphp-public',
    'homepage' => 'https://github.com/lazysod/strataphp-public',
    'support_url' => 'https://github.com/lazysod/strataphp-public/issues',
    'structure_requirements' => [
        'controllers' => false,  // Simple demo module doesn't need complex structure
        'views' => true,
        'models' => false
    ],
    'update_url' => '',
    'enabled' => true
];
