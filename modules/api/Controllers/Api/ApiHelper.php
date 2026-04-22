<?php
namespace App\Modules\api\Controllers;

/**
 * API Helper Utilities
 *
 * Provides utility functions for API parameter validation
 * and response formatting
 */
class ApiHelper
{
    /**
     * Validate required parameters in an array
     *
     * @param array $params Parameters to validate
     * @param array $required Required parameter keys
     * @return array Array of missing parameter names
     */
    public static function requireParams($params, $required)
    {
        try {
            $missing = [];
            foreach ($required as $key) {
                if (!isset($params[$key]) || $params[$key] === '') {
                    $missing[] = $key;
                }
            }
            return $missing;
        } catch (\Exception $e) {
            return $required; // Return all as missing on error
        }
    }

    /**
     * Format a standard success response
     *
     * @param array $data Response data
     * @param string $message Success message
     * @return array Formatted response array
     */
    public static function success($data = [], $message = 'OK')
    {
        try {
            return [
                'success' => true,
                'message' => $message,
                'data' => $data
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error formatting response'];
        }
    }

    /**
     * Format a standard error response
     *
     * @param string $message Error message
     * @param int $code Error code
     * @return array Formatted error response array
     */
    public static function error($message = 'Error', $code = 400)
    {
        try {
            return [
                'success' => false,
                'code' => $code,
                'message' => $message
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Internal error'];
        }
    }
}
