<?php
/**
 * Google Analytics settings view (simple form)
 *
 * Shows and saves the Measurement ID for Google Analytics.
 * Includes error handling and safe file operations.
 */
session_start();
$success = $_SESSION['ga_settings_success'] ?? '';
$error = $_SESSION['ga_settings_error'] ?? '';
unset($_SESSION['ga_settings_success'], $_SESSION['ga_settings_error']);

$settingsPath = dirname(__DIR__, 3) . '/storage/settings/google_analytics.json';
$measurementId = '';
try {
    if (file_exists($settingsPath)) {
        $json = @file_get_contents($settingsPath);
        if ($json === false) {
            $error = 'Could not read settings file.';
        } else {
            $data = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $measurementId = $data['measurement_id'] ?? '';
            } else {
                $error = 'Settings file is corrupted.';
            }
        }
    }
} catch (Throwable $e) {
    $error = 'Error loading settings: ' . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Google Analytics Settings</title>
</head>
<body>
    <h1>Google Analytics Settings</h1>
    <?php if ($success) : ?>
        <div style="color: green;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error) : ?>
        <div style="color: red;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post" action="/modules/Google_Analytics/save_settings.php">
        <label for="measurement_id">Measurement ID:</label>
        <input type="text" id="measurement_id" name="measurement_id" value="<?= htmlspecialchars($measurementId) ?>" required>
        <button type="submit">Save</button>
    </form>
</body>
</html>
