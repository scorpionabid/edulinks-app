<?php
/**
 * EduLinks File Controller
 * 
 * Handles file downloads and uploads
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\FileUpload;
use App\Core\Logger;
use App\Models\Link;

class FileController extends Controller
{
    private Link $linkModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->linkModel = new Link();
    }
    
    /**
     * Handle file download
     */
    public function download(string $id): void
    {
        $linkId = (int)$id;
        $link = $this->linkModel->getLinkWithDetails($linkId);
        
        if (!$link || !$link['is_active']) {
            http_response_code(404);
            $this->view('errors/404', [
                'title' => 'Fayl Tapılmadı',
                'message' => 'Axtardığınız fayl mövcud deyil və ya silinib.'
            ]);
            return;
        }
        
        // Check if user has permission to access this page
        if (!Auth::check()) {
            http_response_code(401);
            $this->view('errors/401', [
                'title' => 'Giriş Tələb Olunur',
                'message' => 'Bu fayla giriş üçün sistemə daxil olmalısınız.'
            ]);
            return;
        }
        
        // Check page permission (unless admin)
        if (!Auth::isAdmin() && !Auth::hasPagePermission($link['page_id'])) {
            http_response_code(403);
            $this->view('errors/403', [
                'title' => 'Giriş Qadağandır',
                'message' => 'Bu fayla giriş icazəniz yoxdur.'
            ]);
            return;
        }
        
        // Check if it's a file link
        if (empty($link['file_path'])) {
            http_response_code(400);
            $this->view('errors/400', [
                'title' => 'Yanlış Sorğu',
                'message' => 'Bu link fayl deyil.'
            ]);
            return;
        }
        
        // Check if file exists
        if (!file_exists($link['file_path'])) {
            http_response_code(404);
            $this->view('errors/404', [
                'title' => 'Fayl Tapılmadı',
                'message' => 'Fayl serverdə mövcud deyil.'
            ]);
            return;
        }
        
        // Increment click count
        $this->linkModel->incrementClicks($linkId);
        
        // Log download
        Logger::logFile('download', $link['file_name'], Auth::id());
        
        // Serve file
        $this->serveFile($link);
    }
    
    /**
     * Serve file for download
     */
    private function serveFile(array $link): void
    {
        $filePath = $link['file_path'];
        $fileName = $link['file_name'];
        $fileSize = $link['file_size'];
        $mimeType = $link['file_type'] ?? 'application/octet-stream';
        
        // Clear output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . $fileSize);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Expires: 0');
        
        // Handle range requests for large files
        $this->handleRangeRequest($filePath, $fileSize);
    }
    
    /**
     * Handle range requests for large files
     */
    private function handleRangeRequest(string $filePath, int $fileSize): void
    {
        $start = 0;
        $end = $fileSize - 1;
        
        if (isset($_SERVER['HTTP_RANGE'])) {
            $range = $_SERVER['HTTP_RANGE'];
            if (preg_match('/bytes=(\d+)-(\d+)?/', $range, $matches)) {
                $start = (int)$matches[1];
                if (isset($matches[2])) {
                    $end = (int)$matches[2];
                }
                
                http_response_code(206); // Partial Content
                header('Accept-Ranges: bytes');
                header("Content-Range: bytes $start-$end/$fileSize");
                header('Content-Length: ' . ($end - $start + 1));
            }
        }
        
        // Output file content
        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            http_response_code(500);
            exit('Server error');
        }
        
        fseek($handle, $start);
        $bytesToRead = $end - $start + 1;
        $bufferSize = 8192; // 8KB chunks
        
        while ($bytesToRead > 0 && !feof($handle)) {
            $chunkSize = min($bufferSize, $bytesToRead);
            $chunk = fread($handle, $chunkSize);
            
            if ($chunk === false) {
                break;
            }
            
            echo $chunk;
            $bytesToRead -= strlen($chunk);
            
            // Flush output to prevent memory issues
            if (ob_get_level()) {
                ob_flush();
            }
            flush();
        }
        
        fclose($handle);
    }
    
    /**
     * Handle AJAX file upload
     */
    public function upload(): void
    {
        $this->requireAuth();
        
        if ($this->getMethod() !== 'POST') {
            $this->jsonError('Yalnız POST metoduna icazə verilir', 405);
            return;
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['file'])) {
            $this->jsonError('Heç bir fayl seçilmədi', 400);
            return;
        }
        
        $file = $_FILES['file'];
        
        // Upload file
        $fileUpload = new FileUpload();
        $result = $fileUpload->upload($file);
        
        if (!$result['success']) {
            $this->jsonError('Fayl yüklənə bilmədi', 400, $result['errors']);
            return;
        }
        
        // Return success response
        $this->jsonSuccess('Fayl uğurla yükləndi', [
            'file_path' => $result['file_path'],
            'file_name' => $result['file_name'],
            'file_size' => $result['file_size'],
            'file_type' => $result['file_type'],
            'file_url' => $result['file_url']
        ]);
    }
    
    /**
     * Preview file (for images and PDFs)
     */
    public function preview(string $id): void
    {
        $linkId = (int)$id;
        $link = $this->linkModel->getLinkWithDetails($linkId);
        
        if (!$link || !$link['is_active']) {
            http_response_code(404);
            exit('File not found');
        }
        
        // Check authentication and permissions
        if (!Auth::check() || (!Auth::isAdmin() && !Auth::hasPagePermission($link['page_id']))) {
            http_response_code(403);
            exit('Access denied');
        }
        
        // Check if it's a file link
        if (empty($link['file_path']) || !file_exists($link['file_path'])) {
            http_response_code(404);
            exit('File not found');
        }
        
        $mimeType = $link['file_type'];
        
        // Only allow preview for images and PDFs
        if (!in_array($mimeType, [
            'image/jpeg', 'image/png', 'image/gif',
            'application/pdf'
        ])) {
            http_response_code(400);
            exit('File type not supported for preview');
        }
        
        // Increment click count
        $this->linkModel->incrementClicks($linkId);
        
        // Log preview
        Logger::logFile('preview', $link['file_name'], Auth::id());
        
        // Serve file for preview
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . $link['file_size']);
        header('Cache-Control: public, max-age=3600'); // Cache for 1 hour
        
        readfile($link['file_path']);
    }
    
    /**
     * Get file information via AJAX
     */
    public function info(string $id): void
    {
        $this->requireAuth();
        
        $linkId = (int)$id;
        $link = $this->linkModel->getLinkWithDetails($linkId);
        
        if (!$link) {
            $this->jsonError('Link tapılmadı', 404);
            return;
        }
        
        // Check permissions
        if (!Auth::isAdmin() && !Auth::hasPagePermission($link['page_id'])) {
            $this->jsonError('Bu fayla giriş icazəniz yoxdur', 403);
            return;
        }
        
        $fileInfo = [];
        
        if (!empty($link['file_path'])) {
            $fileInfo = FileUpload::getFileInfo($link['file_path']);
        }
        
        $this->jsonSuccess('Fayl məlumatları', [
            'link' => $this->linkModel->formatForDisplay($link),
            'file_info' => $fileInfo
        ]);
    }
    
    /**
     * Delete uploaded file (admin only)
     */
    public function deleteFile(string $id): void
    {
        $this->requireAdmin();
        
        $linkId = (int)$id;
        $link = $this->linkModel->find($linkId);
        
        if (!$link) {
            $this->jsonError('Link tapılmadı', 404);
            return;
        }
        
        if (empty($link['file_path'])) {
            $this->jsonError('Bu link fayl deyil', 400);
            return;
        }
        
        // Delete file from filesystem
        if (file_exists($link['file_path'])) {
            FileUpload::deleteFile($link['file_path']);
        }
        
        // Delete link from database
        $this->linkModel->delete($linkId);
        
        Logger::info('File and link deleted', [
            'link_id' => $linkId,
            'file_name' => $link['file_name'],
            'deleted_by' => Auth::id()
        ]);
        
        $this->jsonSuccess('Fayl və link silindi');
    }
    
    /**
     * Get upload statistics (admin only)
     */
    public function stats(): void
    {
        $this->requireAdmin();
        
        $uploadStats = FileUpload::getUploadStats();
        $linkStats = $this->linkModel->getStatistics();
        
        $this->jsonSuccess('Fayl statistikaları', [
            'upload_stats' => $uploadStats,
            'link_stats' => $linkStats
        ]);
    }
    
    /**
     * Clean up temporary files (admin only)
     */
    public function cleanup(): void
    {
        $this->requireAdmin();
        
        $deletedCount = FileUpload::cleanupTempFiles();
        
        $this->jsonSuccess('Müvəqqəti fayllar təmizləndi', [
            'deleted_count' => $deletedCount
        ]);
    }
}