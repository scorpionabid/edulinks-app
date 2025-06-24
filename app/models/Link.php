<?php
/**
 * EduLinks Link Model
 * 
 * Handles link and file operations
 */

namespace App\Models;

use App\Core\Model;

class Link extends Model
{
    protected $table = 'links';
    protected $fillable = [
        'title',
        'description',
        'url',
        'page_id',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'is_active',
        'is_featured',
        'sort_order',
        'created_by'
    ];
    
    /**
     * Get links for a specific page
     */
    public function getPageLinks(int $pageId, bool $activeOnly = true): array
    {
        $conditions = ['page_id' => $pageId];
        if ($activeOnly) {
            $conditions['is_active'] = true;
        }
        
        return $this->where($conditions, 'sort_order ASC, created_at DESC');
    }
    
    /**
     * Get featured links
     */
    public function getFeaturedLinks(int $limit = 10): array
    {
        return $this->where(['is_featured' => true, 'is_active' => true], 'sort_order ASC', $limit);
    }
    
    /**
     * Get links with pagination
     */
    public function getPaginated(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Build WHERE clause
        $whereConditions = [];
        $params = [];
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(l.title ILIKE ? OR l.description ILIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (isset($filters['page_id']) && $filters['page_id'] !== '') {
            $whereConditions[] = "l.page_id = ?";
            $params[] = $filters['page_id'];
        }
        
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $whereConditions[] = "l.is_active = ?";
            $params[] = (bool)$filters['is_active'];
        }
        
        if (isset($filters['is_featured']) && $filters['is_featured'] !== '') {
            $whereConditions[] = "l.is_featured = ?";
            $params[] = (bool)$filters['is_featured'];
        }
        
        if (!empty($filters['type'])) {
            if ($filters['type'] === 'file') {
                $whereConditions[] = "l.file_path IS NOT NULL";
            } elseif ($filters['type'] === 'url') {
                $whereConditions[] = "l.url IS NOT NULL";
            }
        }
        
        $whereClause = empty($whereConditions) ? '' : 'WHERE ' . implode(' AND ', $whereConditions);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as count FROM links l {$whereClause}";
        $totalCount = $this->db->fetchOne($countSql, $params)['count'];
        
        // Get links with page and creator information
        $sql = "SELECT l.*, 
                       p.title as page_title, p.slug as page_slug, p.color as page_color,
                       u.first_name || ' ' || u.last_name as created_by_name
                FROM links l
                LEFT JOIN pages p ON l.page_id = p.id
                LEFT JOIN users u ON l.created_by = u.id 
                {$whereClause} 
                ORDER BY l.is_featured DESC, l.sort_order ASC, l.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";
        
        $links = $this->db->fetchAll($sql, $params);
        
        return [
            'data' => $links,
            'total' => (int)$totalCount,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($totalCount / $perPage)
        ];
    }
    
    /**
     * Create link with auto-generated sort order
     */
    public function createLink(array $data): int
    {
        // Set sort order if not provided
        if (!isset($data['sort_order'])) {
            $maxOrder = $this->db->fetchOne(
                "SELECT MAX(sort_order) as max_order FROM links WHERE page_id = ?",
                [$data['page_id']]
            )['max_order'];
            $data['sort_order'] = ($maxOrder ?? 0) + 1;
        }
        
        return $this->create($data);
    }
    
    /**
     * Increment click count
     */
    public function incrementClicks(int $linkId): bool
    {
        $sql = "UPDATE links SET click_count = click_count + 1 WHERE id = ? AND is_active = true";
        $stmt = $this->db->execute($sql, [$linkId]);
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Get popular links
     */
    public function getPopularLinks(int $limit = 10, int $pageId = null): array
    {
        $whereClause = "WHERE l.is_active = true";
        $params = [];
        
        if ($pageId) {
            $whereClause .= " AND l.page_id = ?";
            $params[] = $pageId;
        }
        
        $sql = "SELECT l.*, p.title as page_title, p.slug as page_slug
                FROM links l
                LEFT JOIN pages p ON l.page_id = p.id
                {$whereClause}
                ORDER BY l.click_count DESC, l.created_at DESC
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Search links
     */
    public function searchLinks(string $query, int $limit = 20): array
    {
        $searchTerm = '%' . $query . '%';
        
        $sql = "SELECT l.*, p.title as page_title, p.slug as page_slug,
                       ts_rank(to_tsvector('simple', l.title || ' ' || COALESCE(l.description, '')), plainto_tsquery('simple', ?)) as rank
                FROM links l
                LEFT JOIN pages p ON l.page_id = p.id
                WHERE l.is_active = true 
                  AND p.is_active = true
                  AND (l.title ILIKE ? OR l.description ILIKE ?)
                ORDER BY rank DESC, l.click_count DESC, l.created_at DESC
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql, [$query, $searchTerm, $searchTerm]);
    }
    
    /**
     * Get link statistics
     */
    public function getStatistics(): array
    {
        $totalLinks = $this->count();
        $activeLinks = $this->count(['is_active' => true]);
        $featuredLinks = $this->count(['is_featured' => true, 'is_active' => true]);
        
        $fileLinks = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM links WHERE file_path IS NOT NULL AND is_active = true"
        )['count'];
        
        $urlLinks = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM links WHERE url IS NOT NULL AND is_active = true"
        )['count'];
        
        $totalClicks = $this->db->fetchOne(
            "SELECT SUM(click_count) as total FROM links WHERE is_active = true"
        )['total'];
        
        $mostPopular = $this->db->fetchOne(
            "SELECT title, click_count 
             FROM links 
             WHERE is_active = true 
             ORDER BY click_count DESC 
             LIMIT 1"
        );
        
        return [
            'total' => (int)$totalLinks,
            'active' => (int)$activeLinks,
            'inactive' => (int)$totalLinks - (int)$activeLinks,
            'featured' => (int)$featuredLinks,
            'file_links' => (int)$fileLinks,
            'url_links' => (int)$urlLinks,
            'total_clicks' => (int)$totalClicks,
            'most_popular' => $mostPopular ? [
                'title' => $mostPopular['title'],
                'clicks' => (int)$mostPopular['click_count']
            ] : null
        ];
    }
    
    /**
     * Update sort orders
     */
    public function updateSortOrders(array $linkOrders): bool
    {
        $this->db->beginTransaction();
        
        try {
            foreach ($linkOrders as $linkId => $sortOrder) {
                $this->update($linkId, ['sort_order' => $sortOrder]);
            }
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Get link with full details
     */
    public function getLinkWithDetails(int $linkId): ?array
    {
        $sql = "SELECT l.*, 
                       p.title as page_title, p.slug as page_slug, p.color as page_color,
                       u.first_name || ' ' || u.last_name as created_by_name
                FROM links l
                LEFT JOIN pages p ON l.page_id = p.id
                LEFT JOIN users u ON l.created_by = u.id 
                WHERE l.id = ?";
        
        return $this->db->fetchOne($sql, [$linkId]);
    }
    
    /**
     * Check if link can be deleted
     */
    public function canDelete(int $linkId): array
    {
        // For now, links can always be deleted
        // In future, might check for dependencies
        return [
            'can_delete' => true,
            'reasons' => []
        ];
    }
    
    /**
     * Delete link and its file
     */
    public function deleteLink(int $linkId): bool
    {
        $link = $this->find($linkId);
        if (!$link) {
            return false;
        }
        
        // Delete file if exists
        if (!empty($link['file_path']) && file_exists($link['file_path'])) {
            unlink($link['file_path']);
        }
        
        return $this->delete($linkId);
    }
    
    /**
     * Get recent links
     */
    public function getRecentLinks(int $limit = 5): array
    {
        $sql = "SELECT l.*, p.title as page_title, p.slug as page_slug
                FROM links l
                LEFT JOIN pages p ON l.page_id = p.id
                WHERE l.is_active = true AND p.is_active = true
                ORDER BY l.created_at DESC
                LIMIT {$limit}";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Format link data for display
     */
    public function formatForDisplay(array $link): array
    {
        $link['status_label'] = $link['is_active'] ? 'Aktiv' : 'Deaktiv';
        $link['status_class'] = $link['is_active'] ? 'success' : 'secondary';
        
        $link['type'] = !empty($link['file_path']) ? 'file' : 'url';
        $link['type_label'] = $link['type'] === 'file' ? 'Fayl' : 'Link';
        $link['type_icon'] = $link['type'] === 'file' ? 'file' : 'external-link-alt';
        
        if ($link['type'] === 'file' && !empty($link['file_size'])) {
            $link['file_size_formatted'] = $this->formatFileSize($link['file_size']);
        } else {
            $link['file_size_formatted'] = null;
        }
        
        if (!empty($link['file_type'])) {
            $link['file_extension'] = $this->getFileExtensionFromMime($link['file_type']);
        } else {
            $link['file_extension'] = null;
        }
        
        return $link;
    }
    
    /**
     * Format file size
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        
        return sprintf("%.1f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }
    
    /**
     * Get file extension from MIME type
     */
    private function getFileExtensionFromMime(string $mimeType): string
    {
        $mimeToExt = [
            'application/pdf' => 'PDF',
            'application/msword' => 'DOC',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'DOCX',
            'application/vnd.ms-excel' => 'XLS',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'XLSX',
            'application/vnd.ms-powerpoint' => 'PPT',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'PPTX',
            'image/jpeg' => 'JPG',
            'image/png' => 'PNG',
            'image/gif' => 'GIF'
        ];
        
        return $mimeToExt[$mimeType] ?? 'FILE';
    }
}