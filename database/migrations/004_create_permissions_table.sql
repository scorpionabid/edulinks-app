-- EduLinks User Permissions Table
-- Created: Phase 1 - Core System

CREATE TABLE user_permissions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    page_id INTEGER NOT NULL REFERENCES pages(id) ON DELETE CASCADE,
    permission_type VARCHAR(20) DEFAULT 'read' CHECK (permission_type IN ('read', 'edit')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Prevent duplicate permissions
    UNIQUE(user_id, page_id)
);

-- Indexes for performance
CREATE INDEX idx_user_permissions_user ON user_permissions(user_id);
CREATE INDEX idx_user_permissions_page ON user_permissions(page_id);
CREATE INDEX idx_user_permissions_type ON user_permissions(permission_type);

-- Comments
COMMENT ON TABLE user_permissions IS 'İstifadəçilərin səhifələrə giriş icazələri';
COMMENT ON COLUMN user_permissions.permission_type IS 'İcazə növü: read və ya edit';