<?php
namespace App\Modules\api\Controllers;

/**
 * Base API Controller
 *
 * Provides common functionality for API endpoints including
 * JSON response formatting and error handling
 */
class ApiController
{
    /**
     * Send JSON response
     *
     * @param mixed $data Data to encode as JSON
     * @param int $status HTTP status code
     * @return void
     */
    protected function json($data, $status = 200)
    {
        try {
            http_response_code($status);
            header('Content-Type: application/json');
            echo json_encode($data);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Internal server error']);
        }
    }

    /**
     * Send error response
     *
     * @param string $message Error message
     * @param int $status HTTP status code
     * @return void
     */
    protected function error($message, $status = 400)
    {
        try {
            $this->json([
                'error' => true,
                'code' => $status,
                'message' => $message
            ], $status);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => 'Internal server error']);
        }
    }
}
