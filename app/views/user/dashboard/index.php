<!-- User Dashboard -->
<div class="container-fluid py-4">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-card bg-gradient-primary text-white rounded-4 p-4 mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="h2 mb-2">
                            Xoş gəldiniz, <?= $this->e($user['first_name']) ?>! 👋
                        </h1>
                        <p class="mb-0 opacity-90">
                            EduLinks sistemində <?= count($accessible_pages) ?> səhifəyə giriş icazəniz var.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="welcome-stats">
                            <div class="d-flex justify-content-md-end gap-3">
                                <div class="stat-item text-center">
                                    <div class="stat-number h4 mb-0"><?= count($accessible_pages) ?></div>
                                    <small class="opacity-75">Səhifə</small>
                                </div>
                                <div class="stat-item text-center">
                                    <div class="stat-number h4 mb-0"><?= count($recent_links) ?></div>
                                    <small class="opacity-75">Son Link</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Access to Pages -->
    <?php if (!empty($accessible_pages)): ?>
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="h4 mb-3">
                    <i class="fas fa-folder-open me-2 text-primary"></i>
                    Səhifələr
                </h3>
                <div class="row g-3">
                    <?php foreach ($accessible_pages as $page): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6">
                            <a href="/user/page/<?= $page['slug'] ?>" class="page-card-link text-decoration-none">
                                <div class="page-card card h-100 border-0 shadow-sm">
                                    <div class="card-body text-center p-4">
                                        <div class="page-icon mb-3" style="background: <?= $page['color'] ?? '#007bff' ?>">
                                            <?php if (!empty($page['icon'])): ?>
                                                <i class="<?= $page['icon'] ?> fa-2x"></i>
                                            <?php else: ?>
                                                <i class="fas fa-folder fa-2x"></i>
                                            <?php endif; ?>
                                        </div>
                                        <h5 class="card-title mb-2"><?= $this->e($page['title']) ?></h5>
                                        <?php if (!empty($page['description'])): ?>
                                            <p class="card-text text-muted small">
                                                <?= $this->e(substr($page['description'], 0, 80)) ?>
                                                <?= strlen($page['description']) > 80 ? '...' : '' ?>
                                            </p>
                                        <?php endif; ?>
                                        <div class="page-stats">
                                            <small class="text-muted">
                                                <i class="fas fa-link me-1"></i>
                                                <?= $page['total_links'] ?? 0 ?> link
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Featured Links -->
        <?php if (!empty($featured_links)): ?>
            <div class="col-xl-8 col-lg-7 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-star me-2 text-warning"></i>
                            Önə Çıxan Linklər
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="row g-0">
                            <?php foreach (array_slice($featured_links, 0, 4) as $index => $link): ?>
                                <div class="col-md-6">
                                    <div class="featured-link-item p-3 h-100 <?= $index % 2 === 0 ? 'border-end' : '' ?> <?= $index < 2 ? 'border-bottom' : '' ?>">
                                        <div class="d-flex align-items-start">
                                            <div class="link-icon me-3">
                                                <?php if ($link['type'] === 'file'): ?>
                                                    <div class="file-icon bg-info text-white">
                                                        <i class="fas fa-file"></i>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="file-icon bg-primary text-white">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    <?php if ($link['type'] === 'file'): ?>
                                                        <a href="/download/<?= $link['id'] ?>" class="text-decoration-none link-title" target="_blank">
                                                            <?= $this->e($link['title']) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?= $this->e($link['url']) ?>" class="text-decoration-none link-title" target="_blank" rel="noopener">
                                                            <?= $this->e($link['title']) ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </h6>
                                                <p class="text-muted small mb-2">
                                                    <?= $this->e(substr($link['description'] ?? '', 0, 60)) ?>
                                                    <?= strlen($link['description'] ?? '') > 60 ? '...' : '' ?>
                                                </p>
                                                <div class="link-meta small">
                                                    <span class="badge bg-light text-dark me-2">
                                                        <?= $this->e($link['page_title']) ?>
                                                    </span>
                                                    <span class="text-muted">
                                                        <i class="fas fa-mouse-pointer me-1"></i>
                                                        <?= $link['click_count'] ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Sidebar -->
        <div class="col-xl-4 col-lg-5">
            <!-- Popular Links -->
            <?php if (!empty($popular_links)): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-fire me-2 text-danger"></i>
                            Populyar Linklər
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <?php foreach ($popular_links as $index => $link): ?>
                            <div class="popular-link-item p-3 <?= $index < count($popular_links) - 1 ? 'border-bottom' : '' ?>">
                                <div class="d-flex align-items-center">
                                    <div class="ranking-number me-3">
                                        <span class="badge bg-primary rounded-circle"><?= $index + 1 ?></span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 small">
                                            <?php if ($link['type'] === 'file'): ?>
                                                <a href="/download/<?= $link['id'] ?>" class="text-decoration-none" target="_blank">
                                                    <?= $this->e($link['title']) ?>
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= $this->e($link['url']) ?>" class="text-decoration-none" target="_blank" rel="noopener">
                                                    <?= $this->e($link['title']) ?>
                                                </a>
                                            <?php endif; ?>
                                        </h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted"><?= $this->e($link['page_title']) ?></small>
                                            <small class="text-muted">
                                                <i class="fas fa-mouse-pointer me-1"></i>
                                                <?= $link['click_count'] ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Recent Links -->
            <?php if (!empty($recent_links)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-clock me-2 text-success"></i>
                            Son Əlavə Edilənlər
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <?php foreach (array_slice($recent_links, 0, 5) as $index => $link): ?>
                            <div class="recent-link-item p-3 <?= $index < 4 ? 'border-bottom' : '' ?>">
                                <div class="d-flex align-items-start">
                                    <div class="link-type-badge me-3">
                                        <?php if ($link['type'] === 'file'): ?>
                                            <span class="badge bg-info">
                                                <i class="fas fa-file"></i>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">
                                                <i class="fas fa-external-link-alt"></i>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 small">
                                            <?php if ($link['type'] === 'file'): ?>
                                                <a href="/download/<?= $link['id'] ?>" class="text-decoration-none" target="_blank">
                                                    <?= $this->e($link['title']) ?>
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= $this->e($link['url']) ?>" class="text-decoration-none" target="_blank" rel="noopener">
                                                    <?= $this->e($link['title']) ?>
                                                </a>
                                            <?php endif; ?>
                                        </h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted"><?= $this->e($link['page_title']) ?></small>
                                            <small class="text-muted">
                                                <?= $this->formatDate($link['created_at'], 'd.m.Y') ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.welcome-card {
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
}

.page-card {
    transition: all 0.3s ease;
    border-radius: 12px;
}

.page-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
}

.page-card-link {
    color: inherit;
}

.page-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    margin: 0 auto;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.featured-link-item {
    transition: background-color 0.2s ease;
}

.featured-link-item:hover {
    background-color: #f8f9fa;
}

.link-icon .file-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.link-title {
    color: #2d3748;
    font-weight: 500;
}

.link-title:hover {
    color: #667eea;
}

.popular-link-item:hover,
.recent-link-item:hover {
    background-color: #f8f9fa;
}

.ranking-number .badge {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
}

.card {
    border-radius: 12px;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
}

@media (max-width: 768px) {
    .featured-link-item {
        border-end: none !important;
    }
    
    .col-md-6:nth-child(odd) .featured-link-item {
        border-bottom: 1px solid #dee2e6 !important;
    }
}
</style>

<script>
// Track link clicks
document.addEventListener('click', function(e) {
    const linkElement = e.target.closest('a[href*="/download/"], a[href^="http"]');
    
    if (linkElement && linkElement.href.includes('/download/')) {
        // Extract link ID from download URL
        const match = linkElement.href.match(/\/download\/(\d+)/);
        if (match) {
            const linkId = match[1];
            
            // Send AJAX request to record click
            fetch('/api/links/' + linkId + '/click', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.csrfToken
                }
            }).catch(error => {
                console.error('Failed to record click:', error);
            });
        }
    }
});
</script>