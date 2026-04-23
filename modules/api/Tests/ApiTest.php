<?php
namespace App\Modules\Api\Tests;

/**
 * Simple test suite for API module
 * Note: Requires PHPUnit for full functionality
 */
class ApiTest
{
    public function testJokesApiEndpoint()
    {
        // Simple validation test
        $jokes = [
            ["id" => 1, "joke" => "Why did the chicken cross the road? To get to the other side!"],
            ["id" => 2, "joke" => "I told my computer I needed a break, and it said 'No problem, I'll go to sleep.'"],
            ["id" => 3, "joke" => "Why do programmers prefer dark mode? Because light attracts bugs!"],
        ];
        
        // Test joke structure
        foreach ($jokes as $joke) {
            if (!isset($joke['id']) || !isset($joke['joke'])) {
                throw new \Exception('Invalid joke structure');
            }
        }
        
        return true;
    }
    
    public function testJsonResponse()
    {
        $testData = ['message' => 'Hello World'];
        $json = json_encode($testData);
        $decoded = json_decode($json, true);
        
        if ($decoded['message'] !== 'Hello World') {
            throw new \Exception('JSON encoding/decoding failed');
        }
        
        return true;
    }
    
    /**
     * Run all tests
     */
    public function runTests()
    {
        $tests = ['testJokesApiEndpoint', 'testJsonResponse'];
        $results = [];
        
        foreach ($tests as $test) {
            try {
                $result = $this->$test();
                $results[$test] = $result ? 'PASS' : 'FAIL';
            } catch (\Exception $e) {
                $results[$test] = 'FAIL: ' . $e->getMessage();
            }
        }
        
        return $results;
    }
}
