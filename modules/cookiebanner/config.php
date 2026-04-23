<?php
/**
 * Cookie Banner Module Configuration
 *
 * @package    modules/cookiebanner
 * @author     StrataPHP Team
 * @copyright  2025
 * @license    MIT
 *
 * This file returns an array of configuration options for the Cookie Banner module.
 * Error handling: If this file is included and fails, a default config will be used.
 */

try {
    return [
        'cookie_name' => 'cookie_consent',
        'cookie_length' => 365, // days
        'message' => 'We use 🍪 cookies to ensure you get the best experience on our website.',
        'read_more_url' => '/privacy', // or any CMS page slug
        'button_text' => 'Accept',
        'banner_style' => 'position:fixed;bottom:0;left:0;width:100%;background:#222;color:#fff;padding:18px 10px;z-index:9999;text-align:center;box-shadow:0 -2px 8px rgba(0,0,0,0.15);',
        'button_style' => 'margin-left:20px;padding:8px 18px;background:#ffd700;color:#222;border:none;border-radius:4px;cursor:pointer;font-weight:bold;',
    ];
} catch (\Throwable $e) {
    // Fallback config if error occurs
    return [
        'cookie_name' => 'cookie_consent',
        'cookie_length' => 365,
        'message' => 'This website uses cookies to ensure you get the best experience.',
        'read_more_url' => '/privacy',
        'button_text' => 'Accept',
        'banner_style' => '',
        'button_style' => '',
        'error' => $e->getMessage(),
    ];
}
