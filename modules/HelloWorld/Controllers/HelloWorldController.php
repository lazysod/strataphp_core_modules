<?php
namespace App\Modules\HelloWorld\Controllers;

use App\Modules\HelloWorld\Models\HelloWorld;

/**
 * Hello World Controller
 *
 * Simple demonstration controller for the Hello World module
 */
class HelloWorldController
{
    /**
     * Display the hello world page
     *
     * @return void
     */
    public function index()
    {
        try {
            include __DIR__ . '/../views/hello.php';
        } catch (\Exception $e) {
            echo '<h1>Error loading Hello World page</h1>';
        }
    }
}
