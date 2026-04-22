<?php
/**
 * Admin Module Configuration
 *
 * Defines the admin module metadata and configuration for the StrataPHP framework.
 * This module provides comprehensive administration functionality including user management,
 * module management, and system controls.
 *
 * @package App\Modules\Admin
 * @author  StrataPHP Framework
 * @version 1.0.0
 * @license MIT
 */

return [
    'name' => 'Admin Panel',
    'slug' => 'admin',
    'version' => '1.0.0',
    'description' => 'Comprehensive administration panel with user management, module management, and system controls',
    'author' => 'StrataPHP Framework',
    'category' => 'Admin',
    'license' => 'MIT',
    'framework_version' => '1.0.0',
    'repository' => 'https://github.com/lazysod/strataphp-public',
    'homepage' => 'https://github.com/lazysod/strataphp-public',
    'support_url' => 'https://github.com/lazysod/strataphp-public/issues',
    'structure_requirements' => [
        'controllers' => true,  // Needs controllers for admin operations
        'views' => true,        // Needs views for admin interface
        'models' => true        // Needs models for data management
    ],
    'enabled' => true,
    'dependencies' => [
        'php' => '>=8.0',
        'strataphp' => '>=1.0.0'
    ],
    'routes' => [
        '/admin' => 'AdminController@index',
        '/admin/dashboard' => 'AdminController@dashboard',
        '/admin/modules' => 'ModuleManagerController@index',
        '/admin/users' => 'UserAdminController@index',
        '/admin/links' => 'AdminLinksController@index'
    ],
    'permissions' => [
        'admin.access',
        'admin.modules.manage',
        'admin.users.manage',
        'admin.links.manage'
    ],
    'assets' => [
        'css' => ['admin.css'],
        'js' => ['admin.js']
    ]
];
