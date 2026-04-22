<?php
// Controller to handle setting the default module from the admin UI

namespace App\Modules\Admin\Controllers;

require_once __DIR__ . '/../../../app/updateDefaultModule.php';

class ModuleDefaultController
{
    /**
     * Set the default module from the admin UI.
     * Handles POST request and updates the default module.
     * @throws \Exception
     */
    public function setDefault()
    {
        error_log('ModuleDefaultController::setDefault called');
        error_log('POST: ' . print_r($_POST, true));
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['default_module'])) {
            $newDefault = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['default_module']); // sanitize
            try {
                updateDefaultModule($newDefault);
                error_log('Default module updated to: ' . $newDefault);
                $_SESSION['success'] = 'Default module updated to ' . htmlspecialchars($newDefault);
            } catch (\Exception $e) {
                error_log('Failed to update default module: ' . $e->getMessage());
                $_SESSION['error'] = 'Failed to update default module: ' . $e->getMessage();
            }
        } else {
            error_log('POST missing or default_module not set');
        }
        header('Location: /admin/modules');
        exit;
    }
}
// Usage: Route POST /admin/modules/set-default to ModuleDefaultController::setDefault
