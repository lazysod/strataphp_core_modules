<?php
namespace App\Modules\GoogleAnalytics\Models;

use App\DB;

class GoogleAnalytics
{
    private $db;
    private $table = 'google_analytics_settings';
    
    public function __construct(DB $db)
    {
        $this->db = $db;
        // Validate table name for security
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $this->table)) {
            throw new \InvalidArgumentException('Invalid table name');
        }
    }

    /**
     * Get the current measurement ID
     */
    public function getMeasurementId()
    {
        $sql = "SELECT measurement_id FROM `" . $this->table . "` ORDER BY updated_at DESC LIMIT 1";
        $row = $this->db->fetch($sql);
        return $row['measurement_id'] ?? '';
    }

    /**
     * Set the measurement ID
     */
    public function setMeasurementId($measurementId)
    {
        // If a row exists, update it; otherwise, insert new
        $sql = "SELECT id FROM `" . $this->table . "` LIMIT 1";
        $row = $this->db->fetch($sql);
        if ($row && isset($row['id'])) {
            $updateSql = "UPDATE `" . $this->table . "` SET measurement_id = :measurement_id, updated_at = NOW() WHERE id = :id";
            return $this->db->query($updateSql, ['measurement_id' => $measurementId, 'id' => $row['id']]);
        } else {
            $insertSql = "INSERT INTO `" . $this->table . "` (measurement_id) VALUES (:measurement_id)";
            return $this->db->query($insertSql, ['measurement_id' => $measurementId]);
        }
    }
    
    /**
     * Get all records
     */
    public function getAll()
    {
        try {
            // Table name is validated in constructor, safe to use here
            $sql = "SELECT * FROM `" . $this->table . "` ORDER BY created_at DESC";
            return $this->db->fetchAll($sql);
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Get a record by ID
     */
    public function getById($id)
    {
        try {
            $sql = "SELECT * FROM `" . $this->table . "` WHERE id = ?";
            return $this->db->fetch($sql, [$id]);
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Create a new record
     */
    public function create($data)
    {
        try {
            $fields = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $sql = "INSERT INTO `" . $this->table . "` ($fields) VALUES ($placeholders)";
            
            return $this->db->query($sql, $data);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Update a record
     */
    public function update($id, $data)
    {
        try {
            $setParts = [];
            foreach (array_keys($data) as $field) {
                $setParts[] = "$field = :$field";
            }
            $setClause = implode(', ', $setParts);
            
            $sql = "UPDATE `" . $this->table . "` SET $setClause WHERE id = :id";
            $data['id'] = $id;
            
            return $this->db->query($sql, $data);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Delete a record
     */
    public function delete($id)
    {
        try {
            $sql = "DELETE FROM `" . $this->table . "` WHERE id = ?";
            return $this->db->query($sql, [$id]);
        } catch (\Exception $e) {
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
