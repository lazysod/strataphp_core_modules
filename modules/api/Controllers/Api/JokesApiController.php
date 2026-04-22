<?php
namespace App\Modules\Api\Controllers;

/**
 * Jokes API Controller
 *
 * Example API controller providing joke endpoints for demonstration
 * Extends base ApiController for common functionality
 */
require_once __DIR__ . '/ApiController.php';
require_once __DIR__ . '/ApiHelper.php';
class JokesApiController extends ApiController
{
    /**
     * Index endpoint - returns error as no default action
     *
     * @return void
     */
    public function index()
    {
        try {
            $this->error('Bad Request', 400);
        } catch (\Exception $e) {
            $this->error('Internal server error', 500);
        }
    }

    /**
     * Get random joke endpoint
     *
     * @return void
     */
    public function random()
    {
        try {
            $jokes = [
                ["id" => 1, "joke" => "Why did the chicken cross the road? To get to the other side!"],
                ["id" => 2, "joke" => "I told my computer I needed a break, and it said 'No problem, I'll go to sleep.'"],
                ["id" => 3, "joke" => "Why do programmers prefer dark mode? Because light attracts bugs!"],
            ];
            $random = $jokes[array_rand($jokes)];
            $this->json($random);
        } catch (\Exception $e) {
            $this->error('Error retrieving joke', 500);
        }
    }

    /**
     * Get joke by ID endpoint
     *
     * @param string $id Joke ID
     * @return void
     */
    public function get($id)
    {
        try {
            // Validate parameter (example)
            $missing = ApiHelper::requireParams(['id' => $id], ['id']);
            if ($missing) {
                $this->json(ApiHelper::error('Missing parameter: ' . implode(', ', $missing), 400), 400);
                return;
            }

            $jokes = [
                1 => "Why did the chicken cross the road? To get to the other side!",
                2 => "I told my computer I needed a break, and it said 'No problem, I'll go to sleep.'",
                3 => "Why do programmers prefer dark mode? Because light attracts bugs!",
            ];

            if (isset($jokes[$id])) {
                $this->json(ApiHelper::success(['id' => $id, 'joke' => $jokes[$id]]));
            } else {
                $this->json(ApiHelper::error('Joke not found', 404), 404);
            }
        } catch (\Exception $e) {
            $this->error('Error retrieving joke', 500);
        }
    }

    /**
     * Add new joke endpoint
     *
     * @return void
     */
    public function add()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->error('Method Not Allowed', 405);
                return;
            }
            // dummy get data from a DB or other source
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            if (!is_array($data) || empty($data['joke'])) {
                $this->error('Missing joke text', 400);
                return;
            }

            // In a real app, you'd save to DB. Here, just echo back.
            $newJoke = [
                'id' => rand(100, 999),
                'joke' => $data['joke']
            ];
            $this->json(ApiHelper::success($newJoke), 201);
        } catch (\Exception $e) {
            $this->error('Error adding joke', 500);
        }
    }
}
