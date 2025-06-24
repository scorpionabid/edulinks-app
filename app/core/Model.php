<?php
/**
 * EduLinks Base Model Class
 * 
 * Abstract base class for all models
 */

namespace App\Core;

use PDO;

abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];
    protected $timestamps = true;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Find record by ID
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $result = $this->db->fetchOne($sql, [$id]);
        
        return $result ? $this->hideFields($result) : null;
    }
    
    /**
     * Find record by criteria
     */
    public function findWhere(array $criteria): ?array
    {
        $conditions = [];
        $params = [];
        
        foreach ($criteria as $column => $value) {
            $conditions[] = "{$column} = ?";
            $params[] = $value;
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $conditions);
        $result = $this->db->fetchOne($sql, $params);
        
        return $result ? $this->hideFields($result) : null;
    }
    
    /**
     * Get all records
     */
    public function all(string $orderBy = null, int $limit = null, int $offset = null): array
    {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        if ($offset) {
            $sql .= " OFFSET {$offset}";
        }
        
        $results = $this->db->fetchAll($sql);
        
        return array_map([$this, 'hideFields'], $results);
    }
    
    /**
     * Get records with conditions
     */
    public function where(array $criteria, string $orderBy = null, int $limit = null): array
    {
        $conditions = [];
        $params = [];
        
        foreach ($criteria as $column => $value) {
            $conditions[] = "{$column} = ?";
            $params[] = $value;
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $conditions);
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        $results = $this->db->fetchAll($sql, $params);
        
        return array_map([$this, 'hideFields'], $results);
    }
    
    /**
     * Create new record
     */
    public function create(array $data): int
    {
        $filteredData = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $filteredData['created_at'] = date('Y-m-d H:i:s');
            $filteredData['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $columns = implode(',', array_keys($filteredData));
        $placeholders = ':' . implode(', :', array_keys($filteredData));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders}) RETURNING {$this->primaryKey}";
        
        $stmt = $this->db->execute($sql, $filteredData);
        $result = $stmt->fetch();
        
        return (int)$result[$this->primaryKey];
    }
    
    /**
     * Update record
     */
    public function update(int $id, array $data): bool
    {
        $filteredData = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $filteredData['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $setClause = implode(', ', array_map(
            fn($key) => "{$key} = :{$key}", 
            array_keys($filteredData)
        ));
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id";
        $filteredData['id'] = $id;
        
        $stmt = $this->db->execute($sql, $filteredData);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Delete record
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->execute($sql, [$id]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Count records
     */
    public function count(array $criteria = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];
        
        if (!empty($criteria)) {
            $conditions = [];
            foreach ($criteria as $column => $value) {
                $conditions[] = "{$column} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return (int)$result['count'];
    }
    
    /**
     * Execute custom query
     */
    public function query(string $sql, array $params = []): array
    {
        $results = $this->db->fetchAll($sql, $params);
        return array_map([$this, 'hideFields'], $results);
    }
    
    /**
     * Filter data based on fillable fields
     */
    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * Hide sensitive fields
     */
    protected function hideFields(array $data): array
    {
        if (empty($this->hidden)) {
            return $data;
        }
        
        return array_diff_key($data, array_flip($this->hidden));
    }
}