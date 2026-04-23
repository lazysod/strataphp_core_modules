<?php
/**
 * Google Analytics Module Config
 *
 * Set your Google Analytics Measurement ID here.
 * Error handling: If this file fails, a default config will be used.
 */

try {
    return [
        // Example: 'G-XXXXXXXXXX'
        'measurement_id' => '',
    ];
} catch (\Throwable $e) {
    // Fallback config if error occurs
    return [
        'measurement_id' => '',
        'error' => $e->getMessage(),
    ];
}
