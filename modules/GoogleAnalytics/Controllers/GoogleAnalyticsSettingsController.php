<?php
namespace App\Modules\GoogleAnalytics\Controllers;

/**
 * Google Analytics Settings Controller
 * Handles secure retrieval of the Measurement ID for frontend use
 * Includes error handling and documentation comments.
 */
class GoogleAnalyticsSettingsController
{
    private $settingsPath;

    public function __construct()
    {
        $this->settingsPath = dirname(__DIR__, 3) . '/storage/settings/google_analytics.json';
    }

    /**
     * Get the Measurement ID from settings file
     * @return string
     */
    public function getMeasurementId()
    {
        try {
            if (file_exists($this->settingsPath)) {
                $json = @file_get_contents($this->settingsPath);
                if ($json !== false) {
                    $data = json_decode($json, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return $data['measurement_id'] ?? '';
                    }
                }
            }
        } catch (\Throwable $e) {
            // Optionally log error
        }
        return '';
    }
}
