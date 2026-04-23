<?php
/**
 * OAuth Clients Config
 *
 * Contains provider keys, secrets, and callback URIs.
 * Add other providers as needed.
 */
try {
    return [
        'google_client_id' => 'YOUR_GOOGLE_CLIENT_ID',
        'google_client_secret' => 'YOUR_GOOGLE_CLIENT_SECRET',
        'google_redirect_uri' => 'http://localhost:8888/oauth_clients/google/callback',
        // Add other providers here
    ];
} catch (Exception $e) {
    error_log('OAuth config error: ' . $e->getMessage());
    return [];
}
