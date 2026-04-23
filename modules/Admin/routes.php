<?php

$router->get('/admin/dashboard/profile', [\App\Modules\Admin\Controllers\AdminProfileController::class, 'profile']);
$router->post('/admin/dashboard/profile', [\App\Modules\Admin\Controllers\AdminProfileController::class, 'profile']);

// Manual CORS preflight handler for /oauth/userinfo
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS' && strpos($_SERVER['REQUEST_URI'], '/oauth/userinfo') !== false) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json');
    http_response_code(200);
    exit;
}
// Google Analytics Admin Settings Route (protected by admin middleware)
$router->get('/admin/google-analytics-settings', [\App\Modules\GoogleAnalytics\Controllers\GoogleAnalyticsAdminController::class, 'settings']);
$router->post('/admin/google-analytics-settings/save', [\App\Modules\GoogleAnalytics\Controllers\GoogleAnalyticsAdminController::class, 'saveSettings']);
use App\Controllers\AdminController;
use App\Modules\Admin\Controllers\ModuleInstallerController;
use App\Modules\Admin\Controllers\ModuleDetailsController;
use App\Modules\Admin\Controllers\ModuleManagerController;
use App\Modules\Admin\Controllers\AdminLinksController;

$router->get('/admin', [\App\Modules\Admin\Controllers\AdminController::class, 'index']);
$router->get('/admin/dashboard', [\App\Modules\Admin\Controllers\AdminController::class, 'dashboard']);
// Links Admin Routes
$router->get('/admin/links', [\App\Modules\Admin\Controllers\AdminLinksController::class, 'index']);
$router->post('/admin/links', [\App\Modules\Admin\Controllers\AdminLinksController::class, 'index']);
$router->get('/admin/links/add', [AdminLinksController::class, 'add']);
$router->post('/admin/links/add', [AdminLinksController::class, 'add']);
$router->get('/admin/links/edit/{id}', [AdminLinksController::class, 'edit']);
$router->post('/admin/links/edit/{id}', [AdminLinksController::class, 'edit']);
$router->post('/admin/links/delete/{id}', [AdminLinksController::class, 'delete']);
$router->get('/admin/links/delete/{id}', [AdminLinksController::class, 'delete']);
$router->post('/admin/links/order', [AdminLinksController::class, 'order']);

// Module Installer Routes
$router->get('/admin/module-installer', [ModuleInstallerController::class, 'index']);
$router->post('/admin/module-installer/upload', [ModuleInstallerController::class, 'uploadInstall']);
$router->post('/admin/module-installer/url', [ModuleInstallerController::class, 'urlInstall']);
$router->post('/admin/module-installer/generate', [ModuleInstallerController::class, 'generateModule']);

// Module Details Routes
$router->get('/admin/modules/details/{module}', [ModuleDetailsController::class, 'show']);
$router->post('/admin/modules/validate/{module}', [ModuleDetailsController::class, 'validate']);
$router->get('/admin/modules/validate-all', [ModuleDetailsController::class, 'validateAll']);

// Module Manager Routes
$router->get('/admin/modules', [ModuleManagerController::class, 'index']);
$router->post('/admin/modules', [ModuleManagerController::class, 'index']);
$router->post('/admin/modules/delete/{module}', [ModuleManagerController::class, 'delete']);

// Admin User Management Routes
$router->get('/admin/users', [\App\Modules\Admin\Controllers\UserAdminController::class, 'index']);
$router->get('/admin/users/add', [\App\Modules\Admin\Controllers\UserAdminController::class, 'add']);
$router->post('/admin/users/add', [\App\Modules\Admin\Controllers\UserAdminController::class, 'add']);
$router->get('/admin/users/edit/{id}', [\App\Modules\Admin\Controllers\UserAdminController::class, 'edit']);
$router->post('/admin/users/edit/{id}', [\App\Modules\Admin\Controllers\UserAdminController::class, 'edit']);
$router->get('/admin/users/suspend/{id}', [\App\Modules\Admin\Controllers\UserAdminController::class, 'suspend']);
$router->get('/admin/users/unsuspend/{id}', [\App\Modules\Admin\Controllers\UserAdminController::class, 'unsuspend']);
$router->get('/admin/users/delete/{id}', [\App\Modules\Admin\Controllers\UserAdminController::class, 'delete']);



$router->get('/admin/reset-password', [AdminController::class, 'resetPassword']);
$router->post('/admin/reset-password', [AdminController::class, 'resetPassword']);
$router->get('/admin/reset-request', [AdminController::class, 'resetRequest']);
$router->post('/admin/reset-request', [AdminController::class, 'resetRequest']);

$router->get('/admin/sessions', [\App\Modules\Admin\Controllers\AdminSessionsController::class, 'index']);
$router->post('/admin/sessions/revoke', [\App\Modules\Admin\Controllers\AdminSessionsController::class, 'revoke']);
$router->post('/admin/sessions/update-device', [\App\Modules\Admin\Controllers\AdminSessionsController::class, 'updateDevice']);

// oauth client management
$router->get('/admin/oauth-clients', [\App\Modules\Admin\Controllers\OAuthClientController::class, 'index']);
$router->post('/admin/oauth-clients', [\App\Modules\Admin\Controllers\OAuthClientController::class, 'index']);
$router->get('/admin/oauth-clients/add', [\App\Modules\Admin\Controllers\OAuthClientController::class, 'add']);
$router->post('/admin/oauth-clients/add', [\App\Modules\Admin\Controllers\OAuthClientController::class, 'add']);
$router->get('/admin/oauth-clients/edit/{id}', [\App\Modules\Admin\Controllers\OAuthClientController::class, 'edit']);
$router->post('/admin/oauth-clients/edit/{id}', [\App\Modules\Admin\Controllers\OAuthClientController::class, 'edit']);
$router->get('/admin/oauth-clients/delete/{id}', [\App\Modules\Admin\Controllers\OAuthClientController::class, 'delete']);
$router->post('/admin/oauth-clients/delete/{id}', [\App\Modules\Admin\Controllers\OAuthClientController::class, 'delete']);

// OAuth2 endpoints
$router->get('/oauth/authorize', [\App\Modules\Admin\Controllers\OAuthAuthorizeController::class, 'authorize']);
$router->post('/oauth/authorize', [\App\Modules\Admin\Controllers\OAuthAuthorizeController::class, 'authorize']);

// OAuth2 userinfo endpoint
$router->get('/oauth/userinfo', [\App\Modules\Admin\Controllers\OAuthUserInfoController::class, 'userinfo']);

$router->get('/test', function () {
    echo 'test';
});
