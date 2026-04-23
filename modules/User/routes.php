<?php
use App\Modules\User\Controllers\UserLoginController;
use App\Modules\User\Controllers\UserProfileController;
use App\App;

// Ensure Composer autoloader is loaded for App class
$composerAutoload = __DIR__ . '/../../../vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}
// modules/User/routes.php
// Register user module routes using the router and modules['user'] config

global $router;
if (!empty(App::config('modules')['User'])) {
    // Register / as root if user is the default module
    if (!empty(App::config('default_module')) && App::config('default_module') === 'user') {
        $router->get('/', [UserLoginController::class, 'index']);
    }
    $router->get('/user/login', [UserLoginController::class, 'index']);
    // $router->get('/user/dashboard', [\App\Modules\User\Controllers\UserDashboardController::class, 'index']); // Will show new dashboard menu
    $router->post('/user/login', [UserLoginController::class, 'index']);
    $router->get('/user/register', [\App\Modules\User\Controllers\UserRegisterController::class, 'index']);
    $router->post('/user/register', [\App\Modules\User\Controllers\UserRegisterController::class, 'index']);
    $router->get('/user/profile', [UserProfileController::class, 'index']);
    $router->post('/user/profile', [UserProfileController::class, 'index']);
    $router->get('/user/reset-request', [\App\Modules\User\Controllers\UserResetRequestController::class, 'index']);
    $router->post('/user/reset-request', [\App\Modules\User\Controllers\UserResetRequestController::class, 'index']);
    $router->get('/user/reset', [\App\Modules\User\Controllers\UserResetController::class, 'index']);
    $router->post('/user/reset', [\App\Modules\User\Controllers\UserResetController::class, 'index']);
    $router->get('/user/activate', [\App\Modules\User\Controllers\UserActivateController::class, 'index']);
    $router->get('/user/sessions', [\App\Modules\User\Controllers\UserSessionsController::class, 'index']);
    $router->post('/user/sessions/revoke', [\App\Modules\User\Controllers\UserSessionsController::class, 'revoke']);
    $router->post('/user/sessions/update-device', [\App\Modules\User\Controllers\UserSessionsController::class, 'updateDevice']);
    // Add more user routes as needed
    
    $router->get('/user/sso', [\App\Modules\User\Controllers\SSOController::class, 'index']);
    $router->post('/user/sso/revoke', [\App\Modules\User\Controllers\SSOController::class, 'revoke']);
    $router->get('/admin/user/sessions', [\App\Modules\User\Controllers\UserSessionsController::class, 'adminSessions']);
    $router->post('/admin/user/sessions/revoke', [\App\Modules\User\Controllers\UserSessionsController::class, 'adminRevoke']);
}
    // Additional context lines can be added here if necessary
