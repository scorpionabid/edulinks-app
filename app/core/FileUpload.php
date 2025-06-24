<?php
/**
 * EduLinks File Upload Handler
 * 
 * Handles secure file uploads with validation
 */

namespace App\Core;

class FileUpload
{
    private array $config;
    private array $errors = [];
    
    public function __construct()
    {
        $this->config = require dirname(__DIR__) . '/config/app.php';
    }
    
    /**
     * Upload file with validation
     */
    public function upload(array $file, string $destination = null): array
    {
        $this->errors = [];
        
        // Validate file upload
        if (!$this->validateUpload($file)) {
            return [
                'success' => false,
                'errors' => $this->errors,
                'file_path' => null
            ];
        }
        
        // Generate destination path
        if ($destination === null) {
            $destination = $this->generateDestinationPath($file['name']);
        }
        
        // Create directory if doesn't exist
        $destinationDir = dirname($destination);
        if (!is_dir($destinationDir)) {
            if (!mkdir($destinationDir, 0755, true)) {
                $this->errors[] = 'Upload dizini yaradıla bilmədi';
                return [
                    'success' => false,
                    'errors' => $this->errors,
                    'file_path' => null
                ];
            }
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->errors[] = 'Fayl yüklənərkən xəta baş verdi';
            return [
                'success' => false,
                'errors' => $this->errors,
                'file_path' => null
            ];
        }
        
        // Set proper permissions
        chmod($destination, 0644);
        
        Logger::logFile('upload', basename($destination), Auth::id());
        
        return [
            'success' => true,
            'errors' => [],
            'file_path' => $destination,
            'file_name' => $file['name'],
            'file_size' => $file['size'],
            'file_type' => $file['type'],
            'file_url' => $this->getFileUrl($destination)
        ];
    }
    
    /**
     * Validate file upload
     */
    private function validateUpload(array $file): bool
    {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($file['error']);
            return false;
        }
        
        // Check file size
        if ($file['size'] > $this->config['upload']['max_size']) {
            $maxSize = $this->formatFileSize($this->config['upload']['max_size']);
            $this->errors[] = "Fayl ölçüsü {$maxSize}-dan böyük ola bilməz";
            return false;
        }
        
        // Check file type by extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->config['upload']['allowed_types'])) {
            $allowedTypes = implode(', ', $this->config['upload']['allowed_types']);
            $this->errors[] = "İcazə verilən formatlar: {$allowedTypes}";
            return false;
        }
        
        // Check MIME type
        if (!$this->isAllowedMimeType($file['type'])) {
            $this->errors[] = 'Bu fayl tipi icazə verilmir';
            return false;
        }
        
        // Check file content (basic security)
        if (!$this->isSecureFile($file['tmp_name'], $extension)) {
            $this->errors[] = 'Fayl təhlükəli məzmun ehtiva edir';
            return false;
        }
        
        return true;
    }
    
    /**
     * Generate destination path for uploaded file
     */
    private function generateDestinationPath(string $originalName): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Sanitize filename
        $filename = $this->sanitizeFilename($filename);
        
        // Generate unique filename
        $uniqueFilename = $filename . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        
        // Organize by date
        $year = date('Y');
        $month = date('m');
        
        return $this->config['upload']['path'] . $year . '/' . $month . '/' . $uniqueFilename;
    }
    
    /**
     * Sanitize filename
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        
        // Replace multiple underscores with single
        $filename = preg_replace('/_+/', '_', $filename);
        
        // Trim underscores
        $filename = trim($filename, '_');
        
        // Limit length
        if (strlen($filename) > 50) {
            $filename = substr($filename, 0, 50);
        }
        
        return $filename ?: 'file';
    }
    
    /**
     * Check if MIME type is allowed
     */
    private function isAllowedMimeType(string $mimeType): bool
    {
        $allowedMimes = [
            // Documents
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            
            // Images
            'image/jpeg',
            'image/png',
            'image/gif',
            
            // Text
            'text/plain',
            'text/csv'
        ];
        
        return in_array($mimeType, $allowedMimes);
    }
    
    /**
     * Basic security check for file content
     */
    private function isSecureFile(string $filePath, string $extension): bool
    {
        // Read first few bytes to check for executable signatures
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            return false;
        }
        
        $header = fread($handle, 1024);
        fclose($handle);
        
        // Check for common executable signatures
        $dangerousSignatures = [
            "\x4d\x5a", // PE executable
            "\x7f\x45\x4c\x46", // ELF executable
            "#!/", // Shell script
            "<?php", // PHP code
            "<script", // JavaScript
            "<html", // HTML (for non-image files)
        ];
        
        foreach ($dangerousSignatures as $signature) {
            if (strpos($header, $signature) === 0 || strpos($header, $signature) !== false) {
                // Allow HTML content only for specific file types
                if ($signature === "<html" && in_array($extension, ['html', 'htm'])) {
                    continue;
                }
                return false;
            }
        }
        
        // Additional checks for image files
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            $imageInfo = getimagesize($filePath);
            if (!$imageInfo) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get upload error message
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'Fayl çox böyükdür (server limiti)',
            UPLOAD_ERR_FORM_SIZE => 'Fayl çox böyükdür (form limiti)',
            UPLOAD_ERR_PARTIAL => 'Fayl qismən yükləndi',
            UPLOAD_ERR_NO_FILE => 'Heç bir fayl seçilmədi',
            UPLOAD_ERR_NO_TMP_DIR => 'Müvəqqəti fayl qovluğu yoxdur',
            UPLOAD_ERR_CANT_WRITE => 'Fayl yazıla bilmir',
            UPLOAD_ERR_EXTENSION => 'Fayl yükləmə extension tərəfindən dayandırıldı'
        ];
        
        return $messages[$errorCode] ?? 'Naməlum yükləmə xətası';
    }
    
    /**
     * Get file URL from path
     */
    private function getFileUrl(string $filePath): string
    {
        $uploadPath = $this->config['upload']['path'];
        $uploadUrl = $this->config['upload']['url'];
        
        $relativePath = str_replace($uploadPath, '', $filePath);
        return $uploadUrl . ltrim($relativePath, '/');
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
     * Delete uploaded file
     */
    public static function deleteFile(string $filePath): bool
    {
        if (file_exists($filePath)) {
            Logger::logFile('delete', basename($filePath), Auth::id());
            return unlink($filePath);
        }
        
        return true; // File doesn't exist, consider it deleted
    }
    
    /**
     * Get file info
     */
    public static function getFileInfo(string $filePath): ?array
    {
        if (!file_exists($filePath)) {
            return null;
        }
        
        $size = filesize($filePath);
        $mimeType = mime_content_type($filePath);
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        return [
            'size' => $size,
            'mime_type' => $mimeType,
            'extension' => $extension,
            'modified' => filemtime($filePath)
        ];
    }
    
    /**
     * Clean up old temporary files
     */
    public static function cleanupTempFiles(): int
    {
        $config = require dirname(__DIR__) . '/config/app.php';
        $tempPath = $config['upload']['temp_path'];
        
        if (!is_dir($tempPath)) {
            return 0;
        }
        
        $deleted = 0;
        $cutoffTime = time() - 3600; // 1 hour ago
        
        $files = glob($tempPath . '*');
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoffTime) {
                if (unlink($file)) {
                    $deleted++;
                }
            }
        }
        
        Logger::info("Cleaned up temporary files", ['deleted_count' => $deleted]);
        
        return $deleted;
    }
    
    /**
     * Get upload statistics
     */
    public static function getUploadStats(): array
    {
        $config = require dirname(__DIR__) . '/config/app.php';
        $uploadPath = $config['upload']['path'];
        
        if (!is_dir($uploadPath)) {
            return [
                'total_files' => 0,
                'total_size' => 0,
                'disk_usage' => '0 B'
            ];
        }
        
        $totalFiles = 0;
        $totalSize = 0;
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($uploadPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $totalFiles++;
                $totalSize += $file->getSize();
            }
        }
        
        return [
            'total_files' => $totalFiles,
            'total_size' => $totalSize,
            'disk_usage' => self::formatFileSize($totalSize)
        ];
    }
    
    /**
     * Format file size (static version)
     */
    private static function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        
        return sprintf("%.1f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }
}