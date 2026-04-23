<?php
/**
 * OAuthClientInterface
 *
 * Defines required methods for OAuth provider clients.
 * Note: Error handling must be implemented in provider classes, not in the interface itself.
 */
namespace App\Modules\OAuthClients;

/**
 * Interface for OAuth provider clients.
 * All methods should handle errors gracefully and return error details in arrays.
 */
interface OAuthClientInterface
{
    /**
     * Get provider authorization URL
     * @return string Empty string on error
     */
    public function getAuthUrl(): string;

    /**
     * Handle callback and exchange code for token
     * @param array $params Callback params
     * @return array Must include 'error' key on failure
     */
    public function handleCallback(array $params): array;

    /**
     * Get user info from provider
     * @param string $accessToken Access token
     * @return array Must include 'error' key on failure
     */
    public function getUserInfo(string $accessToken): array;
}
