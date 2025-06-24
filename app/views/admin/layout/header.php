<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center" href="/admin">
            <i class="fas fa-graduation-cap me-2"></i>
            <span class="fw-bold">EduLinks</span>
            <small class="badge bg-primary ms-2">Admin</small>
        </a>

        <!-- Mobile toggle -->
        <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Main Navigation -->
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $this->url() == '/admin' ? 'active' : '' ?>" href="/admin">
                        <i class="fas fa-tachometer-alt me-1"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($this->url(), '/admin/users') === 0 ? 'active' : '' ?>" href="/admin/users">
                        <i class="fas fa-users me-1"></i>
                        İstifadəçilər
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($this->url(), '/admin/pages') === 0 ? 'active' : '' ?>" href="/admin/pages">
                        <i class="fas fa-folder-open me-1"></i>
                        Səhifələr
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= strpos($this->url(), '/admin/links') === 0 ? 'active' : '' ?>" href="/admin/links">
                        <i class="fas fa-link me-1"></i>
                        Linklər
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/analytics">
                        <i class="fas fa-chart-bar me-1"></i>
                        Analitika
                    </a>
                </li>
            </ul>

            <!-- Right side navigation -->
            <ul class="navbar-nav">
                <!-- Quick Actions Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="quickActionsDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-plus-circle me-1"></i>
                        Əlavə Et
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/admin/users/create">
                            <i class="fas fa-user-plus me-2"></i>İstifadəçi
                        </a></li>
                        <li><a class="dropdown-item" href="/admin/pages/create">
                            <i class="fas fa-folder-plus me-2"></i>Səhifə
                        </a></li>
                        <li><a class="dropdown-item" href="/admin/links/create">
                            <i class="fas fa-plus-square me-2"></i>Link
                        </a></li>
                    </ul>
                </li>

                <!-- User Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <div class="user-avatar me-2">
                            <i class="fas fa-user-circle fa-lg"></i>
                        </div>
                        <span class="d-none d-md-inline">
                            <?= $this->e($this->user()['first_name'] ?? 'Admin') ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <div class="dropdown-header">
                                <div class="fw-bold"><?= $this->e($this->user()['first_name'] . ' ' . $this->user()['last_name']) ?></div>
                                <small class="text-muted"><?= $this->e($this->user()['email']) ?></small>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/user">
                            <i class="fas fa-eye me-2"></i>İstifadəçi Görünüşü
                        </a></li>
                        <li><a class="dropdown-item" href="/admin/profile">
                            <i class="fas fa-user-edit me-2"></i>Profil
                        </a></li>
                        <li><a class="dropdown-item" href="/admin/settings">
                            <i class="fas fa-cog me-2"></i>Sistem Parametrləri
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="/logout" class="d-inline">
                                <?= CSRF::field() ?>
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>Çıxış
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Spacer for fixed navbar -->
<div style="height: 76px;"></div>