-- EduLinks Users Table
-- Created: Phase 1 - Core System

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('admin', 'user')),
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_active ON users(is_active);
CREATE INDEX idx_users_remember_token ON users(remember_token) WHERE remember_token IS NOT NULL;

-- Comments
COMMENT ON TABLE users IS 'Sistema istifadəçiləri - admin və adi istifadəçilər';
COMMENT ON COLUMN users.role IS 'İstifadəçi rolu: admin və ya user';
COMMENT ON COLUMN users.is_active IS 'İstifadəçinin aktiv statusu';
COMMENT ON COLUMN users.remember_token IS 'Remember me funksiyası üçün token';