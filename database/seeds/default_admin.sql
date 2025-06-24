-- EduLinks Default Admin User
-- Password: admin123 (change immediately after first login)

INSERT INTO users (email, password_hash, first_name, last_name, role, is_active) VALUES
('admin@sim.edu.az', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'İstifadəçi', 'admin', TRUE);

-- Note: The password hash above is for "admin123"
-- It should be changed immediately after first login for security