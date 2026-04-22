<?php
use App\App;
// Ensure Composer autoloader is loaded for App class
$composerAutoload = __DIR__ . '/../../../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}
// ...existing code...
use App\Controllers\AdminController;

// Home module routes (core route for /)
global $router;

// Fallback homepage route when CMS is disabled or not available
$modules = App::config('modules') ?? [];

$cmsEnabled = !empty($modules['StrataCms']['enabled']) || !empty($modules['cms']['enabled']);

if (!$cmsEnabled || (!empty(App::config('default_module')) && App::config('default_module') === 'home')) {
    $router->get(
        '/',
        function () {
            if (class_exists('App\\App')) {
                // App::log('DEBUG: / route dispatched (fallback)');
            }
            if (class_exists('App\\Controllers\\HomeController')) {
                // App::log('DEBUG: HomeController found');
                $controller = new \App\Controllers\HomeController();
                if (method_exists($controller, 'index')) {
                    // App::log('DEBUG: HomeController::index() exists');
                    return $controller->index();
                } else {
                    // App::log('DEBUG: HomeController::index() missing');
                }
            } else {
                // App::log('DEBUG: HomeController missing');
            }
            echo 'Home route error: controller or method missing.';
        }
    );
}
    // Additional context lines can be added here if necessary
$router->get('/about', [\App\Controllers\AboutController::class, 'index']);
$router->get('/admin', [AdminController::class, 'index']);
$router->post('/admin', [AdminController::class, 'index']);
$router->get('/admin/dashboard', [AdminController::class, 'dashboard']);
