<!-- Admin Dashboard -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">Admin Panel</h1>
                <p class="text-muted mb-0">EduLinks sistemi idarəetmə paneli</p>
            </div>
            <div>
                <span class="badge bg-success">
                    <i class="fas fa-circle me-1"></i>
                    Sistem Aktiv
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-75">Aktiv İstifadəçilər</div>
                        <div class="h2 mb-0"><?= $user_stats['active'] ?? 0 ?></div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="/admin/users">Detayları gör</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-75">Toplam Səhifələr</div>
                        <div class="h2 mb-0"><?= $total_pages ?? 5 ?></div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-folder-open fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="/admin/pages">Səhifələri idarə et</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-75">Toplam Linklər</div>
                        <div class="h2 mb-0"><?= $total_links ?? 0 ?></div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-link fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="/admin/links">Linkləri idarə et</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-white-75">Bu ay kliklər</div>
                        <div class="h2 mb-0"><?= $monthly_clicks ?? 0 ?></div>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-mouse-pointer fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="/admin/analytics">Analitikaya bax</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions and Recent Activity -->
<div class="row">
    <!-- Quick Actions -->
    <div class="col-xl-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-bolt me-1"></i>
                Tez Əməliyyatlar
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <a href="/admin/users/create" class="btn btn-outline-primary w-100">
                            <i class="fas fa-user-plus me-2"></i>
                            Yeni İstifadəçi
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="/admin/pages/create" class="btn btn-outline-success w-100">
                            <i class="fas fa-folder-plus me-2"></i>
                            Yeni Səhifə
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="/admin/links/create" class="btn btn-outline-info w-100">
                            <i class="fas fa-plus-square me-2"></i>
                            Yeni Link
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="/admin/settings/backup" class="btn btn-outline-warning w-100">
                            <i class="fas fa-download me-2"></i>
                            Sistem Backup
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="col-xl-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-clock me-1"></i>
                Son Fəaliyyətlər
            </div>
            <div class="card-body">
                <?php if (isset($recent_activity) && !empty($recent_activity)): ?>
                    <div class="timeline">
                        <?php foreach ($recent_activity as $activity): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-<?= $activity['type'] ?? 'secondary' ?>">
                                    <i class="fas fa-<?= $activity['icon'] ?? 'info' ?>"></i>
                                </div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title"><?= $this->e($activity['title']) ?></h6>
                                    <p class="timeline-text"><?= $this->e($activity['description']) ?></p>
                                    <small class="text-muted">
                                        <?= $this->formatDate($activity['created_at'], 'd.m.Y H:i') ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <p>Hələ fəaliyyət qeydə alınmayıb.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- System Information -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-server me-1"></i>
                Sistem Məlumatları
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="border-end pe-3">
                            <h6 class="text-muted">EduLinks Versiyası</h6>
                            <p class="h5 mb-0">v1.0.0</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border-end pe-3">
                            <h6 class="text-muted">PHP Versiyası</h6>
                            <p class="h5 mb-0"><?= PHP_VERSION ?></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border-end pe-3">
                            <h6 class="text-muted">Disk Sahəsi</h6>
                            <p class="h5 mb-0">
                                <?php
                                $diskFree = disk_free_space('./');
                                $diskTotal = disk_total_space('./');
                                $diskUsed = $diskTotal - $diskFree;
                                $percentage = round(($diskUsed / $diskTotal) * 100, 1);
                                echo $percentage . '%';
                                ?>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div>
                            <h6 class="text-muted">Son Backup</h6>
                            <p class="h5 mb-0">
                                <span class="text-muted">-</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.timeline-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: white;
}

.timeline-title {
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.timeline-text {
    font-size: 0.85rem;
    margin-bottom: 0.25rem;
    color: #6c757d;
}

.card .border-end:last-child {
    border-end: none !important;
}
</style>