<?php
namespace App\Modules\Contact\Models;

use App\DB;

/**
 * Class Contact
 * Handles contact retrieval and addition.
 */
class Contact
{
    /**
     * Get all contacts from the users table.
     * @return array|null
     */
    public function getAllContacts()
    {
        try {
            $config = isset($config) ? $config : (file_exists(__DIR__ . '/../../../app/config.php') ? include __DIR__ . '/../../../app/config.php' : []);
            $db = new DB($config);
            return $db->fetchAll('SELECT * FROM users');
        } catch (\Throwable $e) {
            error_log('Contact::getAllContacts error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Add a new contact to the users table.
     * @param string $name
     * @param string $email
     * @param string $avatar
     * @return bool|int|null
     */
    public function addContact($name, $email, $avatar = '')
    {
        try {
            $config = isset($config) ? $config : (file_exists(__DIR__ . '/../../../app/config.php') ? include __DIR__ . '/../../../app/config.php' : []);
            $db = new DB($config);
            $sql = "INSERT INTO users (name, email, avatar) VALUES (?, ?, ?)";
            return $db->query($sql, [$name, $email, $avatar]);
        } catch (\Throwable $e) {
            error_log('Contact::addContact error: ' . $e->getMessage());
            return null;
        }
    }
}
