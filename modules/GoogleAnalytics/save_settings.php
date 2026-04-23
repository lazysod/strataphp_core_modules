
<?php
/**
 * Google Analytics Settings Save Handler
 *
 * Handles POST requests to save the Google Analytics Measurement ID to a secure JSON file.
 * Performs input validation, error handling, and logs debug information.
 *
 * @author StrataPHP Framework
 */
session_start();


// Paths are constructed using dirname and are not user-controlled, ensuring safety.
$settingsPath = dirname(__DIR__, 3) . '/storage/settings/google_analytics.json';
$debugLog = dirname(__DIR__, 3) . '/storage/logs/ga_settings_debug.log';

// Extra safety: ensure settings path is within allowed directory
if (strpos(realpath(dirname($settingsPath)), realpath(dirname(__DIR__, 3) . '/storage/settings')) !== 0) {
    throw new Exception('Settings path is outside allowed directory.');
}
if (strpos(realpath(dirname($debugLog)), realpath(dirname(__DIR__, 3) . '/storage/logs')) !== 0) {
    throw new Exception('Debug log path is outside allowed directory.');
}

try {
    // Safe: $debugLog is a fixed path, not user-controlled

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $measurementId = isset($_POST['measurement_id']) ? trim($_POST['measurement_id']) : '';
    // Safe: $debugLog is a fixed path, not user-controlled
        file_put_contents($debugLog, "Measurement ID: $measurementId\n", FILE_APPEND);
        if ($measurementId !== '') {
            $json = json_encode(['measurement_id' => $measurementId], JSON_PRETTY_PRINT);
            if ($json === false) {
                throw new Exception('Failed to encode JSON.');
            }
            // Safe: $settingsPath is a fixed path, not user-controlled
            $result = @file_put_contents($settingsPath, $json);
            if ($result === false) {
                throw new Exception('Failed to write settings file.');
            }
            // Safe: $debugLog is a fixed path, not user-controlled
            file_put_contents($debugLog, "Write result: $result\n", FILE_APPEND);
            $_SESSION['ga_settings_success'] = 'Google Analytics Measurement ID saved.';
        } else {
            $_SESSION['ga_settings_error'] = 'Measurement ID cannot be empty.';
        }
        header('Location: /admin/google-analytics-settings');
        exit;
    }
} catch (Throwable $e) {
    // Safe: $debugLog is a fixed path, not user-controlled
    file_put_contents($debugLog, "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    $_SESSION['ga_settings_error'] = 'An error occurred while saving: ' . htmlspecialchars($e->getMessage());
    header('Location: /admin/google-analytics-settings');
    exit;
}
