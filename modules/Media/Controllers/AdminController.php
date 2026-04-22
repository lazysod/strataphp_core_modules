<?php
namespace App\Modules\Media\Controllers;

/**
 * Media Admin Controller
 * Handles rendering of the media dashboard page.
 */
class AdminController
{
    /**
     * Render the media dashboard page
     */
    /**
     * Render the media dashboard page
     * Handles errors gracefully.
     */
    public function dashboard()
    {
        try {
            include __DIR__ . '/../views/dashboard.php';
        } catch (\Exception $e) {
            error_log('Error including dashboard view: ' . $e->getMessage());
            echo '<h2>Error loading dashboard view.</h2>';
        }
    }
}
