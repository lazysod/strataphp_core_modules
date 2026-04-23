<?php
/**
 * Links Module Configuration
 *
 * Defines the links module metadata and configuration for the StrataPHP framework.
 * This module provides Linktree-style link management functionality.
 *
 * @package App\Modules\Links
 * @author  StrataPHP Framework
 * @version 1.0.0
 * @license MIT
 */

return [
    'name' => 'Links',
    'slug' => 'links',
    'version' => '1.0.0',
    'description' => 'Linktree-style landing page for displaying organized collections of links',
    'author' => 'StrataPHP Framework',
    'category' => 'Social',
    'license' => 'MIT',
    'framework_version' => '1.0.0',
    'repository' => 'https://github.com/lazysod/strataphp-public',
    'homepage' => 'https://github.com/lazysod/strataphp-public',
    'support_url' => 'https://github.com/lazysod/strataphp-public/issues',
    'update' => true,
    'structure_requirements' => [
        'controllers' => true,  // Needs controllers for link display
        'views' => true,        // Needs views for link pages
        'models' => false       // Uses admin models for data
    ],
    'update_url' => '',
    'enabled' => true,
    'routes' => [
        '/links' => 'LinksController@index',
        '/links/about' => 'LinksController@about'
    ],
    'suitable_as_default' => true,
    "admin_url" => "/admin/links",
];
