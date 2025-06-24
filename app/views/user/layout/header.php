<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center" href="/user">
            <i class="fas fa-graduation-cap me-2 text-primary"></i>
            <span class="fw-bold text-dark">EduLinks</span>
        </a>

        <!-- Mobile toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#userNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Main Navigation -->
        <div class="collapse navbar-collapse" id="userNavbar">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $_SERVER['REQUEST_URI'] === '/user' || $_SERVER['REQUEST_URI'] === '/' ? 'active' : '' ?>" href="/user">
                        <i class="fas fa-home me-1"></i>
                        Ana Səhifə
                    </a>
                </li>
                
                <!-- User's Accessible Pages -->
                <?php if (!empty($accessible_pages)): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="pagesDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-folder-open me-1"></i>
                            Səhifələr
                        </a>
                        <ul class="dropdown-menu">
                            <?php foreach ($accessible_pages as $page): ?>
                                <li>
                                    <a class="dropdown-item" href="/user/page/<?= $page['slug'] ?>">
                                        <?php if (!empty($page['icon'])): ?>
                                            <i class="<?= $page['icon'] ?> me-2" style="color: <?= $page['color'] ?? '#007bff' ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-folder me-2" style="color: <?= $page['color'] ?? '#007bff' ?>"></i>
                                        <?php endif; ?>
                                        <?= $this->e($page['title']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- Search Form -->
            <form class="d-flex me-3" method="GET" action="/user/search">
                <div class="input-group">
                    <input class="form-control" type="search" name="q" placeholder="Axtarış..." 
                           value="<?= $this->e($_GET['q'] ?? '') ?>" aria-label="Search">
                    <button class="btn btn-outline-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>

            <!-- Right side navigation -->
            <ul class="navbar-nav">
                <!-- User Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <div class="user-avatar me-2">
                            <div class="avatar-circle bg-primary">
                                <?= strtoupper(substr($this->user()['first_name'] ?? 'U', 0, 1)) ?>
                            </div>
                        </div>
                        <span class="d-none d-md-inline">
                            <?= $this->e($this->user()['first_name'] ?? 'İstifadəçi') ?>
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
                        
                        <?php if ($this->isAdmin()): ?>
                            <li><a class="dropdown-item" href="/admin">
                                <i class="fas fa-crown me-2 text-warning"></i>Admin Panel
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        
                        <li><a class="dropdown-item" href="/user/profile">
                            <i class="fas fa-user-edit me-2"></i>Profil
                        </a></li>
                        
                        <li><a class="dropdown-item" href="/user/search">
                            <i class="fas fa-search me-2"></i>Axtarış
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

<style>
.navbar {
    border-bottom: 1px solid #e9ecef;
}

.navbar-nav .nav-link {
    font-weight: 500;
    padding: 0.75rem 1rem;
    border-radius: 6px;
    margin: 0 0.25rem;
    transition: all 0.3s ease;
}

.navbar-nav .nav-link:hover {
    background-color: #f8f9fa;
    color: #007bff;
}

.navbar-nav .nav-link.active {
    background-color: #e3f2fd;
    color: #1976d2;
    font-weight: 600;
}

.dropdown-menu {
    border: none;
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    padding: 0.5rem;
    margin-top: 0.5rem;
}

.dropdown-item {
    border-radius: 6px;
    padding: 0.5rem 1rem;
    transition: all 0.2s ease;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}

.avatar-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 0.85rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.input-group {
    max-width: 300px;
}

.input-group .form-control {
    border-right: none;
}

.input-group .btn {
    border-left: none;
}

@media (max-width: 991.98px) {
    .navbar-collapse {
        background-color: #f8f9fa;
        border-radius: 8px;
        margin-top: 0.5rem;
        padding: 1rem;
    }
    
    .d-flex.me-3 {
        margin: 1rem 0 !important;
    }
    
    .input-group {
        max-width: none;
    }
}
</style>