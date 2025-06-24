-- EduLinks Default System Settings

INSERT INTO system_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('site_title', 'EduLinks - Təhsil İdarəsi', 'string', 'Saytın başlığı', TRUE),
('site_description', 'Sənəd İdarəetmə və Paylaşım Sistemi', 'string', 'Saytın təsviri', TRUE),
('max_file_size', '52428800', 'integer', 'Maksimum fayl ölçüsü (bytes) - 50MB', FALSE),
('allowed_file_types', '["pdf","doc","docx","xls","xlsx","ppt","pptx","jpg","jpeg","png"]', 'json', 'İcazə verilmiş fayl tipləri', FALSE),
('backup_frequency', 'daily', 'string', 'Backup tezliyi', FALSE),
('email_notifications_enabled', 'true', 'boolean', 'Email bildirişləri aktiv', FALSE),
('session_timeout', '28800', 'integer', 'Session timeout müddəti (saniyə) - 8 saat', FALSE),
('maintenance_mode', 'false', 'boolean', 'Təmir rejimi', TRUE),
('records_per_page', '20', 'integer', 'Səhifə başına qeyd sayı', FALSE),
('system_timezone', 'Asia/Baku', 'string', 'Sistem vaxt zonası', FALSE);