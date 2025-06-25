-- EduLinks Complete Database Installation Script
-- This script creates the complete database structure and inserts default data

\echo 'Installing EduLinks Database...'

-- Users table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('admin', 'user')),
    is_active BOOLEAN DEFAULT true,
    remember_token VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_active ON users(is_active);

\echo 'Users table created.'

-- Pages table
CREATE TABLE pages (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    color VARCHAR(7) DEFAULT '#007bff',
    icon VARCHAR(100),
    is_active BOOLEAN DEFAULT true,
    sort_order INTEGER DEFAULT 0,
    created_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_pages_slug ON pages(slug);
CREATE INDEX idx_pages_active ON pages(is_active);
CREATE INDEX idx_pages_sort ON pages(sort_order);

\echo 'Pages table created.'

-- Links table
CREATE TABLE links (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    url TEXT,
    type VARCHAR(10) DEFAULT 'url' CHECK (type IN ('url', 'file')),
    file_name VARCHAR(255),
    file_path VARCHAR(500),
    file_size BIGINT,
    file_type VARCHAR(100),
    page_id INTEGER NOT NULL REFERENCES pages(id) ON DELETE CASCADE,
    sort_order INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    is_featured BOOLEAN DEFAULT false,
    click_count INTEGER DEFAULT 0,
    created_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_links_page ON links(page_id);
CREATE INDEX idx_links_type ON links(type);
CREATE INDEX idx_links_active ON links(is_active);
CREATE INDEX idx_links_featured ON links(is_featured);
CREATE INDEX idx_links_sort ON links(sort_order);
CREATE INDEX idx_links_clicks ON links(click_count);

\echo 'Links table created.'

-- Page permissions table
CREATE TABLE page_permissions (
    id SERIAL PRIMARY KEY,
    page_id INTEGER NOT NULL REFERENCES pages(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    permission_type VARCHAR(20) DEFAULT 'read' CHECK (permission_type IN ('read', 'write', 'admin')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(page_id, user_id)
);

CREATE INDEX idx_page_permissions_page ON page_permissions(page_id);
CREATE INDEX idx_page_permissions_user ON page_permissions(user_id);

\echo 'Permissions table created.'

-- Settings table
CREATE TABLE settings (
    id SERIAL PRIMARY KEY,
    key VARCHAR(255) UNIQUE NOT NULL,
    value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

\echo 'Settings table created.'

-- Update timestamps trigger function
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Create triggers for updated_at
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_pages_updated_at BEFORE UPDATE ON pages FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_links_updated_at BEFORE UPDATE ON links FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_settings_updated_at BEFORE UPDATE ON settings FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

\echo 'Functions and triggers created.'

-- Insert default admin user
INSERT INTO users (first_name, last_name, email, password, role, is_active) VALUES 
('Admin', 'User', 'admin@edulinks.az', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', true);

-- Insert test user
INSERT INTO users (first_name, last_name, email, password, role, is_active) VALUES 
('Test', 'User', 'user@edulinks.az', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', true);

\echo 'Default admin user created.'

-- Insert default pages
INSERT INTO pages (title, slug, description, color, icon, is_active, sort_order, created_by) VALUES 
('Riyaziyyat', 'riyaziyyat', 'Riyaziyyat dərsi üçün təhsil materialları', '#007bff', 'fas fa-calculator', true, 1, 1),
('Fizika', 'fizika', 'Fizika dərsi üçün təhsil materialları', '#28a745', 'fas fa-atom', true, 2, 1),
('Kimya', 'kimya', 'Kimya dərsi üçün təhsil materialları', '#dc3545', 'fas fa-flask', true, 3, 1),
('Biologiya', 'biologiya', 'Biologiya dərsi üçün təhsil materialları', '#17a2b8', 'fas fa-dna', true, 4, 1),
('Tarix', 'tarix', 'Tarix dərsi üçün təhsil materialları', '#ffc107', 'fas fa-monument', true, 5, 1);

\echo 'Default pages created.'

-- Insert default settings
INSERT INTO settings (key, value, description) VALUES 
('site_name', 'EduLinks', 'Site adı'),
('site_description', 'Təhsil səndləri idarəetmə sistemi', 'Site təsviri'),
('max_file_size', '104857600', 'Maksimum fayl ölçüsü (bayt)'),
('allowed_file_types', 'pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,jpeg,png,gif', 'İcazə verilən fayl növləri'),
('session_lifetime', '7200', 'Session müddəti (saniyə)'),
('timezone', 'Asia/Baku', 'Zaman qurşağı');

\echo 'Default settings created.'

\echo 'EduLinks database installation completed successfully!'
\echo 'Default admin login: admin@edulinks.az / password'
\echo 'Default user login: user@edulinks.az / password'
\echo 'Please change the default passwords immediately after first login.'