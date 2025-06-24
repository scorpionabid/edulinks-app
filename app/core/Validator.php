<?php
/**
 * EduLinks Input Validation Class
 * 
 * Handles form validation and input sanitization
 */

namespace App\Core;

class Validator
{
    private array $errors = [];
    private array $data = [];
    
    /**
     * Validate input data against rules
     */
    public function validate(array $data, array $rules): array
    {
        $this->data = $data;
        $this->errors = [];
        
        foreach ($rules as $field => $ruleString) {
            $fieldRules = explode('|', $ruleString);
            $value = $data[$field] ?? null;
            
            foreach ($fieldRules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }
        
        return [
            'valid' => empty($this->errors),
            'errors' => $this->errors,
            'data' => $this->data
        ];
    }
    
    /**
     * Apply single validation rule
     */
    private function applyRule(string $field, $value, string $rule): void
    {
        // Parse rule parameters
        $params = [];
        if (strpos($rule, ':') !== false) {
            [$rule, $paramString] = explode(':', $rule, 2);
            $params = explode(',', $paramString);
        }
        
        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->addError($field, 'Bu sahə mütləqdir');
                }
                break;
                
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'Düzgün email ünvanı daxil edin');
                }
                break;
                
            case 'min':
                $min = (int)($params[0] ?? 0);
                if (!empty($value) && strlen($value) < $min) {
                    $this->addError($field, "Minimum {$min} simvol olmalıdır");
                }
                break;
                
            case 'max':
                $max = (int)($params[0] ?? 0);
                if (!empty($value) && strlen($value) > $max) {
                    $this->addError($field, "Maksimum {$max} simvol ola bilər");
                }
                break;
                
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, 'Rəqəm daxil edin');
                }
                break;
                
            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->addError($field, 'Tam rəqəm daxil edin');
                }
                break;
                
            case 'url':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, 'Düzgün URL daxil edin');
                }
                break;
                
            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if ($value !== ($this->data[$confirmField] ?? null)) {
                    $this->addError($field, 'Təsdiq sahəsi uyğun gəlmir');
                }
                break;
                
            case 'unique':
                if (!empty($value)) {
                    $table = $params[0] ?? '';
                    $column = $params[1] ?? $field;
                    $except = $params[2] ?? null;
                    
                    if ($this->isValueUnique($table, $column, $value, $except)) {
                        $this->addError($field, 'Bu dəyər artıq mövcuddur');
                    }
                }
                break;
                
            case 'exists':
                if (!empty($value)) {
                    $table = $params[0] ?? '';
                    $column = $params[1] ?? $field;
                    
                    if (!$this->valueExists($table, $column, $value)) {
                        $this->addError($field, 'Seçilmiş dəyər mövcud deyil');
                    }
                }
                break;
                
            case 'file':
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                    $this->validateFile($field, $_FILES[$field]);
                }
                break;
                
            case 'image':
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                    $this->validateImage($field, $_FILES[$field]);
                }
                break;
                
            case 'in':
                if (!empty($value) && !in_array($value, $params)) {
                    $this->addError($field, 'Seçilmiş dəyər etibarlı deyil');
                }
                break;
                
            case 'alpha':
                if (!empty($value) && !ctype_alpha($value)) {
                    $this->addError($field, 'Yalnız hərflər daxil edin');
                }
                break;
                
            case 'alpha_num':
                if (!empty($value) && !ctype_alnum($value)) {
                    $this->addError($field, 'Yalnız hərf və rəqəm daxil edin');
                }
                break;
                
            case 'date':
                if (!empty($value) && !$this->isValidDate($value)) {
                    $this->addError($field, 'Düzgün tarix formatı daxil edin');
                }
                break;
        }
    }
    
    /**
     * Add validation error
     */
    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        
        $this->errors[$field][] = $message;
    }
    
    /**
     * Check if value is unique in database
     */
    private function isValueUnique(string $table, string $column, $value, $except = null): bool
    {
        $db = Database::getInstance();
        
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
        $params = [$value];
        
        if ($except) {
            $sql .= " AND id != ?";
            $params[] = $except;
        }
        
        $result = $db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Check if value exists in database
     */
    private function valueExists(string $table, string $column, $value): bool
    {
        $db = Database::getInstance();
        
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
        $result = $db->fetchOne($sql, [$value]);
        
        return $result['count'] > 0;
    }
    
    /**
     * Validate file upload
     */
    private function validateFile(string $field, array $file): void
    {
        $config = require dirname(__DIR__) . '/config/app.php';
        $uploadConfig = $config['upload'];
        
        // Check file size
        if ($file['size'] > $uploadConfig['max_size']) {
            $maxSize = $this->formatFileSize($uploadConfig['max_size']);
            $this->addError($field, "Fayl ölçüsü {$maxSize}-dan böyük ola bilməz");
        }
        
        // Check file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $uploadConfig['allowed_types'])) {
            $allowedTypes = implode(', ', $uploadConfig['allowed_types']);
            $this->addError($field, "İcazə verilən formatlar: {$allowedTypes}");
        }
    }
    
    /**
     * Validate image upload
     */
    private function validateImage(string $field, array $file): void
    {
        $this->validateFile($field, $file);
        
        // Check if file is actually an image
        $imageInfo = getimagesize($file['tmp_name']);
        if (!$imageInfo) {
            $this->addError($field, 'Düzgün şəkil faylı yükləyin');
        }
    }
    
    /**
     * Check if date is valid
     */
    private function isValidDate(string $date, string $format = 'Y-m-d'): bool
    {
        $dateTime = \DateTime::createFromFormat($format, $date);
        return $dateTime && $dateTime->format($format) === $date;
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
     * Sanitize string input
     */
    public static function sanitize(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Clean HTML input
     */
    public static function cleanHtml(string $input): string
    {
        return strip_tags(trim($input));
    }
    
    /**
     * Validate CSRF token
     */
    public static function csrf(): bool
    {
        return CSRF::verify();
    }
}