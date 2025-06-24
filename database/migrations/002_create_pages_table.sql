-- EduLinks Pages Table
-- Created: Phase 1 - Core System

CREATE TABLE pages (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INTEGER DEFAULT 0,
    icon VARCHAR(100) NULL,
    color VARCHAR(7) DEFAULT '#007bff',
    created_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for performance  
CREATE INDEX idx_pages_active ON pages(is_active);
CREATE INDEX idx_pages_sort ON pages(sort_order);
CREATE UNIQUE INDEX idx_pages_slug ON pages(slug) WHERE is_active = TRUE;
CREATE INDEX idx_pages_created_by ON pages(created_by);

-- Comments
COMMENT ON TABLE pages IS 'Sənəd kateqoriyaları/səhifələri - Maliyyə, Təhsil və s.';
COMMENT ON COLUMN pages.slug IS 'URL-friendly səhifə adı';
COMMENT ON COLUMN pages.icon IS 'Font Awesome icon class';
COMMENT ON COLUMN pages.color IS 'Səhifənin rəng kodu (hex format)';