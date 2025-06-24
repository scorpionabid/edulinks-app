-- EduLinks Links Table
-- Created: Phase 1 - Core System

CREATE TABLE links (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    url VARCHAR(500) NULL,
    page_id INTEGER NOT NULL REFERENCES pages(id) ON DELETE CASCADE,
    file_path VARCHAR(500) NULL,
    file_name VARCHAR(255) NULL,
    file_size BIGINT NULL,
    file_type VARCHAR(50) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    click_count INTEGER DEFAULT 0,
    sort_order INTEGER DEFAULT 0,
    created_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Either URL or file_path must be provided
    CONSTRAINT check_link_source CHECK (
        (url IS NOT NULL AND file_path IS NULL) OR 
        (url IS NULL AND file_path IS NOT NULL)
    )
);

-- Indexes for performance
CREATE INDEX idx_links_page ON links(page_id);
CREATE INDEX idx_links_active ON links(is_active);
CREATE INDEX idx_links_featured ON links(is_featured);
CREATE INDEX idx_links_sort ON links(sort_order);
CREATE INDEX idx_links_created_by ON links(created_by);
CREATE INDEX idx_links_click_count ON links(click_count DESC);

-- Full-text search indexes (Phase 2)
CREATE INDEX idx_links_title_search ON links USING gin(to_tsvector('simple', title));
CREATE INDEX idx_links_description_search ON links USING gin(to_tsvector('simple', description));

-- Comments
COMMENT ON TABLE links IS 'Sənəd linkləri və yüklənmiş fayllar';
COMMENT ON COLUMN links.url IS 'Xarici link URL-i';
COMMENT ON COLUMN links.file_path IS 'Yüklənmiş faylın server yolu';
COMMENT ON COLUMN links.is_featured IS 'Önə çıxan/prioritet link';
COMMENT ON COLUMN links.click_count IS 'Link klik sayı';