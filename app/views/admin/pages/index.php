<!-- Pages Management -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">Səhifə İdarəetməsi</h1>
                <p class="text-muted mb-0">Sənəd kateqoriyalarını idarə edin</p>
            </div>
            <div>
                <a href="/admin/pages/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Yeni Səhifə
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
                        <div class="text-white-75">Toplam Səhifələr</div>
                        <div class="h4 mb-0"><?= $page_stats['total'] ?? 0 ?></div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-folder-open fa-2x opacity-75"></i>
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
                        <div class="text-white-75">Aktiv Səhifələr</div>
                        <div class="h4 mb-0"><?= $page_stats['active'] ?? 0 ?></div>
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
                        <div class="text-white-75">Ən Populyar</div>
                        <div class="h4 mb-0">
                            <?php if (isset($page_stats['most_popular'])): ?>
                                <?= $page_stats['most_popular']['links_count'] ?> link
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-star fa-2x opacity-75"></i>
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
                        <div class="text-white-75">Deaktiv</div>
                        <div class="h4 mb-0"><?= $page_stats['inactive'] ?? 0 ?></div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-times-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/admin/pages" class="row g-3">
            <div class="col-md-6">
                <label for="search" class="form-label">Axtarış</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Səhifə adı və ya təsvir..." 
                       value="<?= $this->e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-4">
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

<!-- Pages Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-folder-open me-2"></i>
                Səhifələr 
                <span class="badge bg-secondary"><?= $pages['total'] ?? 0 ?></span>
            </h5>
            <div class="card-tools">
                <?php if (($pages['total'] ?? 0) > 0): ?>
                    <small class="text-muted">
                        Səhifə <?= $pages['page'] ?? 1 ?> / <?= $pages['total_pages'] ?? 1 ?>
                        (Toplam: <?= $pages['total'] ?? 0 ?>)
                    </small>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="card-body p-0">
        <?php if (!empty($pages['data'])): ?>
            <!-- Sortable Pages List -->
            <div id="sortable-pages" class="list-group list-group-flush">
                <?php foreach ($pages['data'] as $page): ?>
                    <div class="list-group-item page-item" data-page-id="<?= $page['id'] ?>">
                        <div class="row align-items-center">
                            <!-- Drag Handle -->
                            <div class="col-auto">
                                <div class="drag-handle text-muted" style="cursor: grab;">
                                    <i class="fas fa-grip-vertical"></i>
                                </div>
                            </div>
                            
                            <!-- Page Icon & Color -->
                            <div class="col-auto">
                                <div class="page-icon" style="<?= $page['color_style'] ?? '' ?>">
                                    <?= $page['icon_html'] ?? '<i class="fas fa-folder"></i>' ?>
                                </div>
                            </div>
                            
                            <!-- Page Details -->
                            <div class="col">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?= $this->e($page['title']) ?></h6>
                                        <p class="text-muted mb-1 small">
                                            <?= $this->e($page['description'] ?? 'Təsvir yoxdur') ?>
                                        </p>
                                        <div class="d-flex align-items-center gap-3">
                                            <small class="text-muted">
                                                <i class="fas fa-link me-1"></i>
                                                <?= $page['links_count'] ?? 0 ?> link
                                            </small>
                                            <small class="text-muted">
                                                <i class="fas fa-code me-1"></i>
                                                /<span class="text-info"><?= $this->e($page['slug']) ?></span>
                                            </small>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i>
                                                <?= $this->e($page['created_by_name'] ?? 'Naməlum') ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status -->
                            <div class="col-auto">
                                <span class="badge bg-<?= $page['status_class'] ?? 'secondary' ?>">
                                    <i class="fas fa-<?= $page['is_active'] ? 'check' : 'times' ?> me-1"></i>
                                    <?= $page['status_label'] ?? 'Naməlum' ?>
                                </span>
                            </div>
                            
                            <!-- Actions -->
                            <div class="col-auto">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="/user/page/<?= $page['slug'] ?>" 
                                       class="btn btn-outline-info" 
                                       title="Görüntülə"
                                       target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="/admin/pages/<?= $page['id'] ?>/edit" 
                                       class="btn btn-outline-primary" 
                                       title="Redaktə et">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-danger"
                                            onclick="deletePage(<?= $page['id'] ?>, '<?= $this->e($page['title']) ?>')"
                                            title="Sil">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Səhifə tapılmadı</h5>
                <p class="text-muted">Sistemdə heç bir səhifə mövcud deyil və ya axtarış şərtlərinə uyğun səhifə yoxdur.</p>
                <a href="/admin/pages/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    İlk səhifəni yarat
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if (($pages['total_pages'] ?? 1) > 1): ?>
        <div class="card-footer">
            <nav aria-label="Pages pagination">
                <ul class="pagination pagination-sm mb-0 justify-content-center">
                    <!-- Previous -->
                    <?php if (($pages['page'] ?? 1) > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= ($pages['page'] ?? 1) - 1 ?>&<?= http_build_query($filters ?? []) ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Page Numbers -->
                    <?php
                    $start = max(1, ($pages['page'] ?? 1) - 2);
                    $end = min(($pages['total_pages'] ?? 1), ($pages['page'] ?? 1) + 2);
                    
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <li class="page-item <?= $i === ($pages['page'] ?? 1) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query($filters ?? []) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <!-- Next -->
                    <?php if (($pages['page'] ?? 1) < ($pages['total_pages'] ?? 1)): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= ($pages['page'] ?? 1) + 1 ?>&<?= http_build_query($filters ?? []) ?>">
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
                    Səhifəni Sil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Aşağıdakı səhifəni silmək istədiyinizə əminsiniz?</p>
                <div class="alert alert-warning">
                    <strong id="deletePageName"></strong>
                </div>
                <div id="deleteWarnings"></div>
                <p class="text-muted small mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Bu əməliyyat geri alına bilməz.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ləğv et</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    <?= CSRF::field() ?>
                    <button type="submit" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="fas fa-trash me-2"></i>
                        Sil
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.page-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.page-item {
    border: none !important;
    padding: 1rem;
    transition: all 0.2s ease;
}

.page-item:hover {
    background-color: #f8f9fa;
}

.drag-handle:hover {
    color: #007bff !important;
}

.list-group-item.sortable-chosen {
    background-color: #e3f2fd;
    transform: scale(1.02);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

#sortable-pages {
    min-height: 200px;
}

.sortable-ghost {
    opacity: 0.4;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
// Initialize sortable
let sortable;
document.addEventListener('DOMContentLoaded', function() {
    const sortableElement = document.getElementById('sortable-pages');
    
    if (sortableElement) {
        sortable = Sortable.create(sortableElement, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            onEnd: function(evt) {
                updateSortOrder();
            }
        });
    }
});

// Update sort order via AJAX
function updateSortOrder() {
    const items = document.querySelectorAll('.page-item');
    const sortData = [];
    
    items.forEach((item, index) => {
        sortData.push({
            id: item.dataset.pageId,
            sort_order: index + 1
        });
    });
    
    fetch('/admin/pages/sort', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': window.csrfToken
        },
        body: JSON.stringify({pages: sortData})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            EduLinksAdmin.showToast('Səhifə sıralaması yeniləndi', 'success');
        } else {
            EduLinksAdmin.showToast('Sıralama yenilənərkən xəta baş verdi', 'danger');
            location.reload(); // Reload to reset order
        }
    })
    .catch(error => {
        console.error('Error:', error);
        EduLinksAdmin.showToast('Xəta baş verdi', 'danger');
        location.reload();
    });
}

// Delete page function
function deletePage(pageId, pageName) {
    document.getElementById('deletePageName').textContent = pageName;
    document.getElementById('deleteForm').action = '/admin/pages/' + pageId + '/delete';
    
    // Check if page can be deleted
    fetch('/admin/pages/' + pageId + '/check-delete', {
        headers: {
            'X-CSRF-Token': window.csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        const warningsDiv = document.getElementById('deleteWarnings');
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        
        if (data.can_delete) {
            warningsDiv.innerHTML = '';
            confirmBtn.disabled = false;
        } else {
            let warningsHtml = '<div class="alert alert-danger">';
            warningsHtml += '<strong>Səhifə silinə bilməz:</strong><ul class="mb-0 mt-2">';
            data.reasons.forEach(reason => {
                if (reason) {
                    warningsHtml += '<li>' + reason + '</li>';
                }
            });
            warningsHtml += '</ul></div>';
            
            warningsDiv.innerHTML = warningsHtml;
            confirmBtn.disabled = true;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('deleteWarnings').innerHTML = 
            '<div class="alert alert-warning">Yoxlama zamanı xəta baş verdi.</div>';
    });
    
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
});
</script>