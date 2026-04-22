<?php
namespace App\Modules\StrataCms\Models;

use App\DB;

/**
 * Class Cms
 *
 * Handles generic CMS table operations for StrataPHP CMS module.
 */
class Cms
{
    /**
     * @var DB Database connection
     */
    private $db;
    /**
     * @var string Table name
     */
    private $table = 'cms';

    /**
     * Cms constructor.
     * @param DB $db
     * @throws \InvalidArgumentException
     */
    public function __construct(DB $db)
    {
        $this->db = $db;
        // Validate table name for security
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $this->table)) {
            throw new \InvalidArgumentException('Invalid table name');
        }
    }

    /**
     * Get all records from the CMS table.
     * @return array
     */
    public function getAll()
    {
        try {
            $sql = "SELECT * FROM `{$this->table}` ORDER BY created_at DESC";
            return $this->db->fetchAll($sql);
        } catch (\Throwable $e) {
            // Log error or handle as needed
            return [];
        }
    }

    /**
     * Get a record by ID.
     * @param int $id
     * @return array|null
     */
    public function getById($id)
    {
        try {
            $sql = "SELECT * FROM `{$this->table}` WHERE id = ?";
            return $this->db->fetch($sql, [$id]);
        } catch (\Throwable $e) {
            // Log error or handle as needed
            return null;
        }
    }

    /**
     * Create a new record in the CMS table.
     * @param array $data
     * @return bool|int Insert ID or false on failure
     */
    public function create($data)
    {
        try {
            $fields = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            $sql = "INSERT INTO `{$this->table}` ($fields) VALUES ($placeholders)";
            return $this->db->query($sql, $data);
        } catch (\Throwable $e) {
            // Log error or handle as needed
            return false;
        }
    }

    /**
     * Update a record by ID.
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        try {
            $set = [];
            foreach ($data as $key => $value) {
                $set[] = "$key = :$key";
            }
            $setStr = implode(', ', $set);
            $data['id'] = $id;
            $sql = "UPDATE `{$this->table}` SET $setStr WHERE id = :id";
            return $this->db->query($sql, $data);
        } catch (\Throwable $e) {
            // Log error or handle as needed
            return false;
        }
    }

    /**
     * Delete a record by ID.
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        try {
            $sql = "DELETE FROM `{$this->table}` WHERE id = ?";
            return $this->db->query($sql, [$id]);
        } catch (\Throwable $e) {
            // Log error or handle as needed
            return false;
        }
    }
    
    /**
     * Search records
     */
    public function search($query)
    {
        try {
            $sql = "SELECT * FROM `" . $this->table . "` 
                    WHERE title LIKE ? OR content LIKE ?
                    ORDER BY created_at DESC";
            
            $searchTerm = '%' . $query . '%';
            return $this->db->fetchAll($sql, [$searchTerm, $searchTerm]);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Get records with pagination
     */
    public function paginate($page = 1, $perPage = 10)
    {
        try {
            // Validate and sanitize input
            $page = max(1, (int)$page);
            $perPage = max(1, min(100, (int)$perPage)); // Limit max per page
            $offset = ($page - 1) * $perPage;
            
            $sql = "SELECT * FROM `" . $this->table . "` 
                    ORDER BY created_at DESC 
                    LIMIT ? OFFSET ?";
            
            return $this->db->fetchAll($sql, [$perPage, $offset]);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Get total count
     */
    public function getCount()
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM `" . $this->table . "`";
            $result = $this->db->fetch($sql);
            return $result ? (int)$result['count'] : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
