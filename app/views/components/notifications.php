<!-- Flash Messages -->
<div class="notifications-container">
    <?php
    $flashTypes = ['success', 'error', 'warning', 'info'];
    foreach ($flashTypes as $type):
        $message = $this->flash($type);
        if ($message):
            $alertClass = match($type) {
                'success' => 'alert-success',
                'error' => 'alert-danger',
                'warning' => 'alert-warning',
                'info' => 'alert-info',
                default => 'alert-info'
            };
            
            $icon = match($type) {
                'success' => 'check-circle',
                'error' => 'exclamation-triangle',
                'warning' => 'exclamation-circle',
                'info' => 'info-circle',
                default => 'info-circle'
            };
    ?>
        <div class="alert <?= $alertClass ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?= $icon ?> me-2"></i>
            <?= $this->e($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Bağla"></button>
        </div>
    <?php 
        endif;
    endforeach; 
    ?>
</div>

<!-- Validation Errors -->
<?php 
$validationErrors = Session::get('validation_errors');
if ($validationErrors): 
?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Formdakı xətalar:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($validationErrors as $field => $errors): ?>
                <?php foreach ($errors as $error): ?>
                    <li><?= $this->e($error) ?></li>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Bağla"></button>
    </div>
    <?php Session::remove('validation_errors'); ?>
<?php endif; ?>

<style>
.notifications-container .alert {
    margin-bottom: 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.notifications-container .alert:last-child {
    margin-bottom: 0;
}

.alert ul {
    padding-left: 1.5rem;
}

.alert ul li {
    margin-bottom: 0.25rem;
}

.alert ul li:last-child {
    margin-bottom: 0;
}
</style>