<?php
// Admin User Management Routes
$router->get('/admin/users', [\App\Modules\Admin\Controllers\UserAdminController::class, 'index']);
$router->get('/admin/users/add', [\App\Modules\Admin\Controllers\UserAdminController::class, 'add']);
$router->post('/admin/users/add', [\App\Modules\Admin\Controllers\UserAdminController::class, 'add']);
$router->get('/admin/users/edit/{id}', [\App\Modules\Admin\Controllers\UserAdminController::class, 'edit']);
$router->post('/admin/users/edit/{id}', [\App\Modules\Admin\Controllers\UserAdminController::class, 'edit']);
$router->get('/admin/users/suspend/{id}', [\App\Modules\Admin\Controllers\UserAdminController::class, 'suspend']);
$router->get('/admin/users/delete/{id}', [\App\Modules\Admin\Controllers\UserAdminController::class, 'delete']);
