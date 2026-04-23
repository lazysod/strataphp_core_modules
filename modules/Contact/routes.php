<?php
use App\App;

// Ensure Composer autoloader is loaded for App class
$composerAutoload = __DIR__ . '/../../../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}
// ...existing code...
// Contact module routes
global $router;
if (!empty(App::config('modules')['contact'])) {
    // Register / as root if contact is the default module
    if (!empty(App::config('default_module')) && App::config('default_module') === 'contact') {
        $router->get('/', [\App\Modules\Contact\Controllers\ContactFormController::class, 'index']);
    }
    $router->get('/contact', [\App\Modules\Contact\Controllers\ContactFormController::class, 'index']);
    $router->post('/contact', [\App\Modules\Contact\Controllers\ContactFormController::class, 'submit']);
}
    // Additional context lines can be added here if necessary
