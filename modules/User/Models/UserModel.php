<?php
namespace App\Modules\User\Models;

use App\DB;

/**
 * User Model for handling user data operations
 *
 * This model provides methods for user management including:
 * - User profile operations
 * - Password management
 * - Session handling
 * - Authentication helpers
 */
class UserModel
{
    private $db;
    private $config;
    
    /**
     * Constructor
     *
     * @param DB $db Database connection instance
     * @param array $config Configuration array
     */
    public function __construct(DB $db, array $config)
    {
        $this->db = $db;
        $this->config = $config;
    }
    
    /**
     * Get user by ID with SQL injection protection
     *
     * @param int $userId User ID
     * @return array|null User data or null if not found
     */
    public function getUserById(int $userId): ?array
    {
        try {
            return $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Get user by email with SQL injection protection
     *
     * @param string $email User email
     * @return array|null User data or null if not found
     */
    public function getUserByEmail(string $email): ?array
    {
        try {
            return $this->db->fetch("SELECT * FROM users WHERE email = ?", [$email]);
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Update user profile with SQL injection protection
     *
     * @param int $userId User ID
     * @param array $data User data to update
     * @return bool Success status
     */
    public function updateUserProfile(int $userId, array $data): bool
    {
        try {
            $allowedFields = ['first_name', 'verified', 'second_name', 'email', 'display_name'];
            $updateFields = [];
            $values = [];
            
            foreach ($data as $field => $value) {
                if (in_array($field, $allowedFields) && $value !== null) {
                    $updateFields[] = "`{$field}` = ?";
                    $values[] = $value;
                }
            }
            
            if (empty($updateFields)) {
                return false;
            }
            
            $values[] = $userId;
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            
            return $this->db->query($sql, $values) !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Update user password with SQL injection protection
     *
     * @param int $userId User ID
     * @param string $newPassword New password (will be hashed)
     * @return bool Success status
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            return $this->db->query("UPDATE users SET password = ? WHERE id = ?", [$hashedPassword, $userId]) !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Create new user with SQL injection protection
     *
     * @param array $userData User registration data
     * @return array Result with status and message
     */
    public function createUser(array $userData): array
    {
        try {
            // Validate required fields
            $required = ['email', 'password', 'first_name', 'second_name'];
            foreach ($required as $field) {
                if (empty($userData[$field])) {
                    return ['status' => 'error', 'message' => "Field {$field} is required"];
                }
            }
            
            // Check if email already exists
            if ($this->getUserByEmail($userData['email'])) {
                return ['status' => 'error', 'message' => 'Email already registered'];
            }
            
            // Hash password
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            // Insert user
            $sql = "INSERT INTO users (email, password, first_name, second_name, display_name, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
            $params = [
                $userData['email'],
                $hashedPassword,
                $userData['first_name'],
                $userData['second_name'],
                $userData['display_name'] ?? null
            ];
            
            $result = $this->db->query($sql, $params);
            
            if ($result !== false) {
                return ['status' => 'success', 'message' => 'User created successfully'];
            } else {
                return ['status' => 'error', 'message' => 'Failed to create user'];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Database error occurred'];
        }
    }
    
    /**
     * Authenticate user with SQL injection protection
     *
     * @param string $email User email
     * @param string $password User password
     * @return array Authentication result
     */
    public function authenticate(string $email, string $password): array
    {
        try {
            $user = $this->getUserByEmail($email);
            
            if (!$user) {
                return ['status' => 'error', 'message' => 'Invalid credentials'];
            }
            
            if (!password_verify($password, $user['password'])) {
                return ['status' => 'error', 'message' => 'Invalid credentials'];
            }
            
            return ['status' => 'success', 'message' => 'Authentication successful', 'user' => $user];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Authentication error occurred'];
        }
    }
    
    /**
     * Get user statistics
     *
     * @param int $userId User ID
     * @return array User statistics
     */
    public function getUserStats(int $userId): array
    {
        try {
            // Get registration date
            $user = $this->getUserById($userId);
            if (!$user) {
                return [];
            }
            
            // Get session count
            $sessionCount = $this->db->fetch("SELECT COUNT(*) as count FROM user_sessions WHERE user_id = ?", [$userId]);
            
            return [
                'member_since' => $user['created_at'] ?? 'Unknown',
                'total_sessions' => $sessionCount['count'] ?? 0,
                'last_login' => $user['last_login'] ?? 'Never'
            ];
        } catch (\Exception $e) {
            return [];
        }
    }
}
