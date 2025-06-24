<!-- Admin Sidebar (Optional - can be used for more detailed navigation) -->
<aside class="admin-sidebar d-none d-xl-block">
    <div class="sidebar-content">
        <!-- Quick Stats -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Sistem Statistikaları
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="row g-2">
                    <div class="col-6">
                        <div class="stat-item text-center">
                            <div class="stat-value text-primary fw-bold">
                                <?= $user_stats['active'] ?? 0 ?>
                            </div>
                            <div class="stat-label small text-muted">Aktiv İstifadəçi</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-item text-center">
                            <div class="stat-value text-success fw-bold">
                                <?= $total_pages ?? 0 ?>
                            </div>
                            <div class="stat-label small text-muted">Səhifə</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-item text-center">
                            <div class="stat-value text-info fw-bold">
                                <?= $total_links ?? 0 ?>
                            </div>
                            <div class="stat-label small text-muted">Link</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-item text-center">
                            <div class="stat-value text-warning fw-bold">
                                <?= $total_clicks ?? 0 ?>
                            </div>
                            <div class="stat-label small text-muted">Klik</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Son Fəaliyyətlər
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="activity-feed">
                    <?php if (isset($recent_activity) && !empty($recent_activity)): ?>
                        <?php foreach ($recent_activity as $activity): ?>
                            <div class="activity-item p-3 border-bottom">
                                <div class="d-flex">
                                    <div class="activity-icon me-3">
                                        <i class="fas fa-<?= $activity['icon'] ?? 'info-circle' ?> text-muted"></i>
                                    </div>
                                    <div class="activity-content flex-grow-1">
                                        <div class="activity-text small">
                                            <?= $this->e($activity['text']) ?>
                                        </div>
                                        <div class="activity-time text-muted small">
                                            <?= $this->formatDate($activity['created_at'], 'd.m.Y H:i') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-3 text-center text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            Hələ fəaliyyət yoxdur
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</aside>