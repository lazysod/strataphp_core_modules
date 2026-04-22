<?php
namespace App\Modules\GoogleAnalytics\Controllers;
/**
 * Provides a simple admin interface for updating the Measurement ID
 * Includes error handling and documentation comments.
 */
class GoogleAnalyticsAdminController
{
    /**
     * Show the settings form for Google Analytics Measurement ID
     */
    public function settings()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        global $config;
        $sessionPrefix = $config['session_prefix'] ?? ($config['prefix'] ?? 'framework');
        if (!isset($_SESSION[$sessionPrefix . 'admin']) || $_SESSION[$sessionPrefix . 'admin'] < 1) {
            header('Location: /admin/admin_login.php');
            exit;
        }
        require_once $_SERVER['DOCUMENT_ROOT'] . '/app/DB.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/modules/GoogleAnalytics/Models/GoogleAnalytics.php';
        $db = new \App\DB($config);
        $gaModel = new \App\Modules\GoogleAnalytics\Models\GoogleAnalytics($db);
        $measurementId = $gaModel->getMeasurementId();
        include dirname(__DIR__) . '/views/settings.php';
    }

    /**
     * Save the Measurement ID from the settings form
     */
    public function saveSettings()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        global $config;
        $sessionPrefix = $config['session_prefix'] ?? ($config['prefix'] ?? 'framework');
        if (!isset($_SESSION[$sessionPrefix . 'admin']) || $_SESSION[$sessionPrefix . 'admin'] < 1) {
            header('Location: /admin/admin_login.php');
            exit;
        }
        require_once $_SERVER['DOCUMENT_ROOT'] . '/app/DB.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/modules/GoogleAnalytics/Models/GoogleAnalytics.php';
        $db = new \App\DB($config);
        $gaModel = new \App\Modules\GoogleAnalytics\Models\GoogleAnalytics($db);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $measurementId = trim($_POST['measurement_id'] ?? '');
            if ($measurementId !== '') {
                // Validate format: G-XXXXXXXXXX (10 uppercase letters/numbers)
                if (!preg_match('/^G-[A-Z0-9]{10}$/', $measurementId)) {
                    $_SESSION['ga_settings_error'] = 'Invalid Measurement ID format. Must be G-XXXXXXXXXX.';
                    header('Location: /admin/google-analytics-settings');
                    exit;
                }
                try {
                    $gaModel->setMeasurementId($measurementId);
                    $_SESSION['ga_settings_success'] = 'Measurement ID saved.';
                } catch (\Throwable $e) {
                    $_SESSION['ga_settings_error'] = 'Error saving settings: ' . htmlspecialchars($e->getMessage());
                }
            } else {
                $_SESSION['ga_settings_error'] = 'Measurement ID cannot be empty.';
            }
            header('Location: /admin/google-analytics-settings');
            exit;
        }
    }
}
