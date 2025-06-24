<!-- Links Management -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">Link İdarəetməsi</h1>
                <p class="text-muted mb-0">Sənəd linkləri və faylları idarə edin</p>
            </div>
            <div>
                <a href="/admin/links/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Yeni Link
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-75">Toplam Linklər</div>
                        <div class="h4 mb-0"><?= $link_stats['total'] ?? 0 ?></div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-link fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-75">Aktiv Linklər</div>
                        <div class="h4 mb-0"><?= $link_stats['active'] ?? 0 ?></div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-75">Fayl Linkləri</div>
                        <div class="h4 mb-0"><?= $link_stats['file_links'] ?? 0 ?></div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-file fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-75">Toplam Kliklər</div>
                        <div class="h4 mb-0"><?= number_format($link_stats['total_clicks'] ?? 0) ?></div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-mouse-pointer fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/admin/links" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Axtarış</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Link başlığı və ya təsvir..." 
                       value="<?= $this->e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label for="page_id" class="form-label">Səhifə</label>
                <select class="form-select" id="page_id" name="page_id">
                    <option value="">Bütün səhifələr</option>
                    <?php if (!empty($pages)): ?>
                        <?php foreach ($pages as $page): ?>
                            <option value="<?= $page['id'] ?>" 
                                    <?= ($filters['page_id'] ?? '') == $page['id'] ? 'selected' : '' ?>>
                                <?= $this->e($page['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="type" class="form-label">Tip</label>
                <select class="form-select" id="type" name="type">
                    <option value="">Hamısı</option>
                    <option value="file" <?= ($filters['type'] ?? '') === 'file' ? 'selected' : '' ?>>
                        Fayllar
                    </option>
                    <option value="url" <?= ($filters['type'] ?? '') === 'url' ? 'selected' : '' ?>>
                        URL Linkləri
                    </option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Bütün statuslar</option>
                    <option value="1" <?= ($filters['is_active'] ?? '') === '1' ? 'selected' : '' ?>>
                        Aktiv
                    </option>
                    <option value="0" <?= ($filters['is_active'] ?? '') === '0' ? 'selected' : '' ?>>
                        Deaktiv
                    </option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search me-1"></i>
                        Filtrele
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Links Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-link me-2"></i>
                Linklər 
                <span class="badge bg-secondary"><?= $links['total'] ?? 0 ?></span>
            </h5>
            <div class="card-tools">
                <?php if (($links['total'] ?? 0) > 0): ?>
                    <small class="text-muted">
                        Səhifə <?= $links['page'] ?? 1 ?> / <?= $links['total_pages'] ?? 1 ?>
                        (Toplam: <?= $links['total'] ?? 0 ?>)
                    </small>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="card-body p-0">
        <?php if (!empty($links['data'])): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">Tip</th>
                            <th>Link</th>
                            <th>Səhifə</th>
                            <th>Status</th>
                            <th>Kliklər</th>
                            <th class="d-none d-md-table-cell">Yaradılma</th>
                            <th width="150">Əməliyyatlar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($links['data'] as $link): ?>
                            <?php $formattedLink = $this->linkModel->formatForDisplay($link); ?>
                            <tr>
                                <!-- Type Icon -->
                                <td>
                                    <div class="link-type-icon">
                                        <?php if ($formattedLink['type'] === 'file'): ?>
                                            <span class="badge bg-info">
                                                <i class="fas fa-file me-1"></i>
                                                <?= $formattedLink['file_extension'] ?? 'FILE' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">
                                                <i class="fas fa-external-link-alt me-1"></i>
                                                URL
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                
                                <!-- Link Details -->
                                <td>
                                    <div class="link-details">
                                        <h6 class="mb-1">
                                            <?= $this->e($link['title']) ?>
                                            <?php if ($link['is_featured']): ?>
                                                <i class="fas fa-star text-warning ms-1" title="Önə çıxan"></i>
                                            <?php endif; ?>
                                        </h6>
                                        <?php if (!empty($link['description'])): ?>
                                            <p class="text-muted mb-1 small">
                                                <?= $this->e(substr($link['description'], 0, 100)) ?>
                                                <?= strlen($link['description']) > 100 ? '...' : '' ?>
                                            </p>
                                        <?php endif; ?>
                                        <div class="link-meta small text-muted">
                                            <?php if ($formattedLink['type'] === 'file'): ?>
                                                <i class="fas fa-file me-1"></i>
                                                <?= $this->e($link['file_name']) ?>
                                                <?php if ($formattedLink['file_size_formatted']): ?>
                                                    (<?= $formattedLink['file_size_formatted'] ?>)
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <i class="fas fa-external-link-alt me-1"></i>
                                                <span class="text-break"><?= $this->e($link['url']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Page -->
                                <td>
                                    <?php if (!empty($link['page_title'])): ?>
                                        <span class="badge" style="background-color: <?= $link['page_color'] ?? '#007bff' ?>">
                                            <?= $this->e($link['page_title']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Status -->
                                <td>
                                    <span class="badge bg-<?= $formattedLink['status_class'] ?>">
                                        <i class="fas fa-<?= $link['is_active'] ? 'check' : 'times' ?> me-1"></i>
                                        <?= $formattedLink['status_label'] ?>
                                    </span>
                                </td>
                                
                                <!-- Clicks -->
                                <td>
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-mouse-pointer me-1"></i>
                                        <?= number_format($link['click_count']) ?>
                                    </span>
                                </td>
                                
                                <!-- Created Date -->
                                <td class="d-none d-md-table-cell">
                                    <small class="text-muted">
                                        <?= $this->formatDate($link['created_at'], 'd.m.Y') ?>
                                        <br>
                                        <?= $this->e($link['created_by_name'] ?? 'Naməlum') ?>
                                    </small>
                                </td>
                                
                                <!-- Actions -->
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <?php if ($formattedLink['type'] === 'file'): ?>
                                            <a href="/download/<?= $link['id'] ?>" 
                                               class="btn btn-outline-success" 
                                               title="Endir"
                                               target="_blank">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= $this->e($link['url']) ?>" 
                                               class="btn btn-outline-success" 
                                               title="Aç"
                                               target="_blank"
                                               rel="noopener noreferrer">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="/admin/links/<?= $link['id'] ?>/edit" 
                                           class="btn btn-outline-primary" 
                                           title="Redaktə et">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <button type="button" 
                                                class="btn btn-outline-danger"
                                                onclick="deleteLink(<?= $link['id'] ?>, '<?= $this->e($link['title']) ?>')"
                                                title="Sil">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-link fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Link tapılmadı</h5>
                <p class="text-muted">Sistemdə heç bir link mövcud deyil və ya axtarış şərtlərinə uyğun link yoxdur.</p>
                <a href="/admin/links/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    İlk linki yarat
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if (($links['total_pages'] ?? 1) > 1): ?>
        <div class="card-footer">
            <nav aria-label="Links pagination">
                <ul class="pagination pagination-sm mb-0 justify-content-center">
                    <!-- Previous -->
                    <?php if (($links['page'] ?? 1) > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= ($links['page'] ?? 1) - 1 ?>&<?= http_build_query($filters ?? []) ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Page Numbers -->
                    <?php
                    $start = max(1, ($links['page'] ?? 1) - 2);
                    $end = min(($links['total_pages'] ?? 1), ($links['page'] ?? 1) + 2);
                    
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <li class="page-item <?= $i === ($links['page'] ?? 1) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query($filters ?? []) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <!-- Next -->
                    <?php if (($links['page'] ?? 1) < ($links['total_pages'] ?? 1)): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= ($links['page'] ?? 1) + 1 ?>&<?= http_build_query($filters ?? []) ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                    Linki Sil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Aşağıdakı linki silmək istədiyinizə əminsiniz?</p>
                <div class="alert alert-warning">
                    <strong id="deleteLinkName"></strong>
                </div>
                <p class="text-muted small mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Bu əməliyyat geri alına bilməz. Əgər bu fayl linkidirsə, fayl da silinəcək.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ləğv et</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    <?= CSRF::field() ?>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>
                        Sil
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.link-type-icon .badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}

.link-details h6 {
    font-size: 1rem;
    line-height: 1.3;
}

.link-meta {
    max-width: 300px;
    word-break: break-all;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-weight: 500;
}
</style>

<script>
// Delete link function
function deleteLink(linkId, linkName) {
    document.getElementById('deleteLinkName').textContent = linkName;
    document.getElementById('deleteForm').action = '/admin/links/' + linkId + '/delete';
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Auto-submit form when filters change
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    let searchTimeout;
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                searchInput.form.submit();
            }, 500);
        });
    }
    
    // Filter change handlers
    const filterSelects = document.querySelectorAll('#page_id, #type, #status');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
});
</script>