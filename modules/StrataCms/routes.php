<?php
// StrataPHP CMS Module Routes

use App\App;
use App\Modules\StrataCms\Controllers\CmsController;
use App\Modules\StrataCms\Controllers\PageController;
use App\Modules\StrataCms\Controllers\AdminController;
use App\Modules\StrataCms\Controllers\SiteController;
use App\Modules\StrataCms\Controllers\ApiController;

// Ensure Composer autoloader is loaded for App class
$composerAutoload = __DIR__ . '/../../../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

global $router;

if (!empty(App::config('modules')['StrataCms']['enabled'])) {
    
    // Register / as root if StrataCms is the default module
    if (!empty(App::config('default_module')) && App::config('default_module') === 'StrataCms') {
        $router->get('/', [PageController::class, 'home']);
    }
    $router->post('/admin/strata-cms/sites/delete/{id}', [\App\Modules\StrataCms\Controllers\SiteController::class, 'delete']);
    // Sites management (multi-tenant API)
    $router->get('/admin/strata-cms/sites', [\App\Modules\StrataCms\Controllers\SiteController::class, 'index']);
    $router->post('/admin/strata-cms/sites/set-active', [\App\Modules\StrataCms\Controllers\SiteController::class, 'setActive']);
    $router->get('/admin/strata-cms/sites/create', [\App\Modules\StrataCms\Controllers\SiteController::class, 'create']);
    $router->post('/admin/strata-cms/sites/store', [\App\Modules\StrataCms\Controllers\SiteController::class, 'store']);
    $router->get('/admin/strata-cms/sites/edit/{id}', [\App\Modules\StrataCms\Controllers\SiteController::class, 'edit']);
    $router->post('/admin/strata-cms/sites/update/{id}', [\App\Modules\StrataCms\Controllers\SiteController::class, 'update']);
    $router->get('/admin/strata-cms/sites/regenerate/{id}', [\App\Modules\StrataCms\Controllers\SiteController::class, 'regenerateKey']);
    
    // Public page routes - Dynamic routing for CMS pages
    $router->get('/', [PageController::class, 'home']);
    $router->get('/page/{slug}', [PageController::class, 'show']);
    
    // Admin CMS management routes
    $router->get('/admin/strata-cms', [AdminController::class, 'dashboard']);
    $router->get('/admin/strata-cms/dashboard', [AdminController::class, 'dashboard']);
    $router->get('/admin/strata-cms/pages', [AdminController::class, 'pages']);
    $router->get('/admin/strata-cms/pages/create', [AdminController::class, 'createPage']);
    $router->post('/admin/strata-cms/pages/create', [AdminController::class, 'storePage']);
    $router->get('/admin/strata-cms/pages/{id}/edit', [AdminController::class, 'editPage']);
    $router->post('/admin/strata-cms/pages/{id}/edit', [AdminController::class, 'updatePage']);
    $router->post('/admin/strata-cms/pages/{id}/delete', [AdminController::class, 'deletePage']);
    $router->post('/admin/strata-cms/pages/{id}/set-home', [AdminController::class, 'setHomePage']);
    
    // Media library route
    $router->get('/admin/strata-cms/media-library', [\App\Modules\Media\Controllers\ImageController::class, 'mediaLibrary']);
    
    // API routes for headless usage (new, API key required)
    $router->get('/api/pages', [\App\Modules\StrataCms\Controllers\ApiController::class, 'pages']);
    // Deprecated: $router->get('/api/cms/pages', [CmsController::class, 'apiPages']);
    // Deprecated: $router->get('/api/cms/pages/{slug}', [CmsController::class, 'apiPage']);
    
    // Fallback route for dynamic pages (must be last)
    $router->get('/{slug}', [PageController::class, 'dynamicPage']);

    // $router->post('/admin/modules/set-default', [ModuleDefaultController::class, 'setDefault']);
    $router->post('/admin/modules/set-default', [\App\Modules\Admin\Controllers\ModuleDefaultController::class, 'setDefault']);
}
