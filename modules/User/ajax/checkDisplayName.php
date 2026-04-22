<?php
/**
 * AJAX endpoint to check display name validity and uniqueness.
 * Handles display name validation, uniqueness check, and bad word filtering.
 * Returns JSON response.
 */

header('Content-Type: application/json');
require_once dirname(__DIR__, 3) . '/bootstrap.php';
global $config;

try {
    if (empty($_GET['display_name'])) {
        echo json_encode(['valid' => false, 'message' => 'Display name is required.']);
        exit;
    }
    $displayName = trim($_GET['display_name']);

    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $displayName)) {
        echo json_encode(['valid' => false, 'message' => 'Only letters, numbers, underscores, and hyphens allowed.']);
        exit;
    }

    $db = new \App\DB($config);

    // Check if display name is taken
    $sql = "SELECT COUNT(*) as cnt FROM users WHERE display_name = ?";
    $row = $db->fetch($sql, [$displayName]);
    if ($row && $row['cnt'] > 0) {
        echo json_encode(['valid' => false, 'message' => 'Display name is already taken.']);
        exit;
    }

    // Check if display name is a bad word (modular, from config)
    if (!empty($config['bad_words']) && is_array($config['bad_words'])) {
        if (in_array(strtolower($displayName), array_map('strtolower', $config['bad_words']))) {
            echo json_encode(['valid' => false, 'message' => 'Display name is not allowed.']);
            exit;
        }
    }

    echo json_encode(['valid' => true, 'message' => 'Display name is available!']);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['valid' => false, 'message' => 'Server error. Please try again later.']);
}
