<?php
// API v1 routes for jokes

use App\Modules\Api\Controllers\JokesApiController;

global $router;

// Error out if no endpoint given
$router->get('/api/v1/jokes/', [JokesApiController::class, 'index'], 'api.jokes.index');

// GET /api/v1/jokes/random
$router->get('/api/v1/jokes/random', [JokesApiController::class, 'random'], 'api.jokes.random');

// GET /api/v1/jokes/{id}
$router->get('/api/v1/jokes/{id}', [JokesApiController::class, 'get'], 'api.jokes.get');
