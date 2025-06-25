<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <?= \App\Core\CSRF::metaTag() ?>
    
    <title><?= isset($title) ? htmlspecialchars($title) . ' - ' : '' ?>EduLinks</title>
    <meta name="description" content="Təhsil İdarəsi Sənəd İdarəetmə Sistemi">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $this->asset('css/admin.css') ?>">
    <link rel="stylesheet" href="<?= $this->asset('css/responsive.css') ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= $this->asset('images/favicon.ico') ?>">
</head>
<body class="<?= $this->isAdmin() ? 'admin-layout' : 'user-layout' ?>">

    <?php if ($this->auth()): ?>
        <!-- Navigation -->
        <?php if ($this->isAdmin()): ?>
            <?php $this->include('admin.layout.header') ?>
            <?php $this->include('admin.layout.sidebar') ?>
        <?php else: ?>
            <?php $this->include('user.layout.header') ?>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="<?= $this->auth() ? 'main-content' : 'auth-content' ?>">
        <?php if ($this->auth()): ?>
            <div class="container-fluid">
                <!-- Flash Messages -->
                <?php $this->include('components.notifications') ?>
                
                <!-- Page Content -->
                <?= $content ?? '' ?>
            </div>
        <?php else: ?>
            <?= $content ?? '' ?>
        <?php endif; ?>
    </main>

    <?php if ($this->auth()): ?>
        <!-- Footer -->
        <?php $this->include('layout.footer') ?>
    <?php endif; ?>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?= $this->asset('js/admin.js') ?>"></script>
    
    <?php if ($this->isAdmin()): ?>
        <script src="<?= $this->asset('js/admin-dashboard.js') ?>"></script>
    <?php else: ?>
        <script src="<?= $this->asset('js/user.js') ?>"></script>
    <?php endif; ?>

    <!-- CSRF Token for AJAX -->
    <script>
        window.csrfToken = '<?= $this->csrfToken() ?>';
        $.ajaxSetup({
            headers: {
                'X-CSRF-Token': window.csrfToken
            }
        });
    </script>
</body>
</html>