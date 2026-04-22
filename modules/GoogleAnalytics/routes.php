<?php
use App\App;
use App\Modules\GoogleAnalytics\Controllers\GoogleAnalyticsAdminController;
use App\Modules\GoogleAnalytics\Controllers\GoogleAnalyticsController;

// Ensure Composer autoloader is loaded for App class
$composerAutoload = __DIR__ . '/../../../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// GoogleAnalytics module routes
global $router;

if (!empty(App::config('modules')['google-analytics']['enabled'])) {
    // Main routes
    $router->get('/google-analytics', [GoogleAnalyticsController::class, 'index']);
    $router->get('/google-analytics/create', [GoogleAnalyticsController::class, 'create']);
    $router->post('/google-analytics/create', [GoogleAnalyticsController::class, 'store']);
    $router->get('/google-analytics/{{id}}', [GoogleAnalyticsController::class, 'show']);
    $router->get('/google-analytics/{{id}}/edit', [GoogleAnalyticsController::class, 'edit']);
    $router->post('/google-analytics/{{id}}/edit', [GoogleAnalyticsController::class, 'update']);
    $router->post('/google-analytics/{{id}}/delete', [GoogleAnalyticsController::class, 'delete']);
    
    // API routes (optional)

    // Admin settings route (PSR-4 compliant)
    $router->get('/admin/google-analytics-settings', [GoogleAnalyticsAdminController::class, 'settings']);

    $router->get('/api/google-analytics', [GoogleAnalyticsController::class, 'apiIndex']);
    
    // Register as root if this is the default module
    if (!empty(App::config('default_module')) && App::config('default_module') === 'google-analytics') {
        $router->get('/', [GoogleAnalyticsController::class, 'index']);
    }
}
