<?php
namespace App\Modules\HelloWorld\Models;

/**
 * Hello World Model
 *
 * Simple demonstration model for the Hello World module
 */
class HelloWorld
{
    private $messages = [
        "Hello, world!",
        "Welcome to StrataPHP!",
        "Greetings from your first module!",
        "Hello there, developer!",
        "StrataPHP says hello!"
    ];

    /**
     * Get a hello world message
     *
     * @param bool $random Whether to return a random message
     * @return string The hello world message
     */
    public function getMessage($random = false)
    {
        try {
            if ($random) {
                return $this->messages[array_rand($this->messages)];
            }
            return $this->messages[0];
        } catch (\Exception $e) {
            return "Error loading message";
        }
    }

    /**
     * Get all available messages
     *
     * @return array All messages
     */
    public function getAllMessages()
    {
        return $this->messages;
    }
}
