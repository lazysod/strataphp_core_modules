<?php
// StrataPHP Media Module Routes

use App\App;
use App\Modules\Media\Controllers\ImageController;
use App\Modules\Media\Controllers\AdminController; // If needed for dashboard

$composerAutoload = __DIR__ . '/../../../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

global $router;

if (!empty(App::config('modules')['media']['enabled'])) {
    // Media upload and management routes
    $router->post('/admin/media/upload/image', [ImageController::class, 'upload']);
    $router->post('/admin/media/media/delete', [ImageController::class, 'deleteMedia']);
    $router->get('/admin/media/media-library', [ImageController::class, 'mediaLibrary']);
    $router->get('/admin/media/manager', [ImageController::class, 'mediaLibrary']);
    $router->get('/admin/media/dashboard', [AdminController::class, 'dashboard']);
    // Add dashboard or other admin routes as needed
}
