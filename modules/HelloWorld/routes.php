<?php
// Hello World Module Routes

$router->get('/helloworld', function () {
    $controller = new \App\Modules\HelloWorld\Controllers\HelloWorldController();
    $controller->index();
});

$router->get('/hello', function () {
    $controller = new \App\Modules\HelloWorld\Controllers\HelloWorldController();
    $controller->index();
});
