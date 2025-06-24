<!-- Users Management -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">İstifadəçi İdarəetməsi</h1>
                <p class="text-muted mb-0">Sistem istifadəçilərini idarə edin</p>
            </div>
            <div>
                <a href="/admin/users/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Yeni İstifadəçi
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/admin/users" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Axtarış</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Ad, soyad və ya email..." 
                       value="<?= $this->e($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label for="role" class="form-label">Rol</label>
                <select class="form-select" id="role" name="role">
                    <option value="">Bütün rollar</option>
                    <option value="admin" <?= ($filters['role'] ?? '') === 'admin' ? 'selected' : '' ?>>
                        Administrator
                    </option>
                    <option value="user" <?= ($filters['role'] ?? '') === 'user' ? 'selected' : '' ?>>
                        İstifadəçi
                    </option>
                </select>
            </div>
            <div class="col-md-3">
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

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-users me-2"></i>
                İstifadəçilər 
                <span class="badge bg-secondary"><?= $users['total'] ?></span>
            </h5>
            <div class="card-tools">
                <?php if ($users['total'] > 0): ?>
                    <small class="text-muted">
                        Səhifə <?= $users['page'] ?> / <?= $users['total_pages'] ?>
                        (Toplam: <?= $users['total'] ?>)
                    </small>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="card-body p-0">
        <?php if (!empty($users['data'])): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>İstifadəçi</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Status</th>
                            <th>Son Giriş</th>
                            <th>Yaradılma</th>
                            <th width="120">Əməliyyatlar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users['data'] as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="user-avatar me-3">
                                            <div class="avatar-circle bg-<?= $user['role'] === 'admin' ? 'danger' : 'primary' ?>">
                                                <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?= $this->e($user['full_name']) ?></div>
                                            <small class="text-muted">ID: <?= $user['id'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-break"><?= $this->e($user['email']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : 'primary' ?>">
                                        <i class="fas fa-<?= $user['role'] === 'admin' ? 'crown' : 'user' ?> me-1"></i>
                                        <?= $user['role_label'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $user['is_active'] ? 'success' : 'secondary' ?>">
                                        <i class="fas fa-<?= $user['is_active'] ? 'check' : 'times' ?> me-1"></i>
                                        <?= $user['status_label'] ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= $user['last_login_formatted'] ?>
                                    </small>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= $this->formatDate($user['created_at'], 'd.m.Y') ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="/admin/users/<?= $user['id'] ?>/edit" 
                                           class="btn btn-outline-primary" 
                                           title="Redaktə et">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user['id'] !== Auth::id()): ?>
                                            <button type="button" 
                                                    class="btn btn-outline-danger"
                                                    onclick="deleteUser(<?= $user['id'] ?>, '<?= $this->e($user['full_name']) ?>')"
                                                    title="Sil">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">İstifadəçi tapılmadı</h5>
                <p class="text-muted">Sistemdə heç bir istifadəçi mövcud deyil və ya axtarış şərtlərinə uyğun istifadəçi yoxdur.</p>
                <a href="/admin/users/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    İlk istifadəçini yarat
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($users['total_pages'] > 1): ?>
        <div class="card-footer">
            <nav aria-label="Users pagination">
                <ul class="pagination pagination-sm mb-0 justify-content-center">
                    <!-- Previous -->
                    <?php if ($users['page'] > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $users['page'] - 1 ?>&<?= http_build_query($filters) ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Page Numbers -->
                    <?php
                    $start = max(1, $users['page'] - 2);
                    $end = min($users['total_pages'], $users['page'] + 2);
                    
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <li class="page-item <?= $i === $users['page'] ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&<?= http_build_query($filters) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <!-- Next -->
                    <?php if ($users['page'] < $users['total_pages']): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $users['page'] + 1 ?>&<?= http_build_query($filters) ?>">
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
                    İstifadəçini Sil
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Aşağıdakı istifadəçini silmək istədiyinizə əminsiniz?</p>
                <div class="alert alert-warning">
                    <strong id="deleteUserName"></strong>
                </div>
                <p class="text-muted small mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Bu əməliyyat geri alına bilməz.
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
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1rem;
}

.user-avatar {
    flex-shrink: 0;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

.table-responsive {
    border-radius: 0;
}

.card-tools {
    opacity: 0.8;
}
</style>

<script>
function deleteUser(userId, userName) {
    document.getElementById('deleteUserName').textContent = userName;
    document.getElementById('deleteForm').action = '/admin/users/' + userId + '/delete';
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Auto-submit form when filters change
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            searchInput.form.submit();
        }, 500);
    });
});
</script>