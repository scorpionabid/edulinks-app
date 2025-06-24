<!-- Create User -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">Yeni İstifadəçi</h1>
                <p class="text-muted mb-0">Sistemə yeni istifadəçi əlavə edin</p>
            </div>
            <div>
                <a href="/admin/users" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>
                    Geri qayıt
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-plus me-2"></i>
                    İstifadəçi Məlumatları
                </h5>
            </div>
            
            <form method="POST" action="/admin/users/store" novalidate>
                <?= CSRF::field() ?>
                
                <div class="card-body">
                    <!-- Personal Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted border-bottom pb-2 mb-3">
                                <i class="fas fa-user me-1"></i>
                                Şəxsi Məlumatlar
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" 
                                       class="form-control <?= isset($validation_errors['first_name']) ? 'is-invalid' : '' ?>" 
                                       id="first_name" 
                                       name="first_name" 
                                       placeholder="Ad"
                                       value="<?= $this->e(Session::get('old_input')['first_name'] ?? '') ?>"
                                       required>
                                <label for="first_name">Ad *</label>
                                <?php if (isset($validation_errors['first_name'])): ?>
                                    <div class="invalid-feedback">
                                        <?= $this->e($validation_errors['first_name'][0]) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" 
                                       class="form-control <?= isset($validation_errors['last_name']) ? 'is-invalid' : '' ?>" 
                                       id="last_name" 
                                       name="last_name" 
                                       placeholder="Soyad"
                                       value="<?= $this->e(Session::get('old_input')['last_name'] ?? '') ?>"
                                       required>
                                <label for="last_name">Soyad *</label>
                                <?php if (isset($validation_errors['last_name'])): ?>
                                    <div class="invalid-feedback">
                                        <?= $this->e($validation_errors['last_name'][0]) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Account Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted border-bottom pb-2 mb-3">
                                <i class="fas fa-key me-1"></i>
                                Hesab Məlumatları
                            </h6>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-floating mb-3">
                                <input type="email" 
                                       class="form-control <?= isset($validation_errors['email']) ? 'is-invalid' : '' ?>" 
                                       id="email" 
                                       name="email" 
                                       placeholder="Email ünvanı"
                                       value="<?= $this->e(Session::get('old_input')['email'] ?? '') ?>"
                                       required>
                                <label for="email">Email ünvanı *</label>
                                <?php if (isset($validation_errors['email'])): ?>
                                    <div class="invalid-feedback">
                                        <?= $this->e($validation_errors['email'][0]) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="password" 
                                       class="form-control <?= isset($validation_errors['password']) ? 'is-invalid' : '' ?>" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Şifrə"
                                       required>
                                <label for="password">Şifrə *</label>
                                <?php if (isset($validation_errors['password'])): ?>
                                    <div class="invalid-feedback">
                                        <?= $this->e($validation_errors['password'][0]) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="form-text">
                                    Minimum 8 simvol olmalıdır
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       placeholder="Şifrə təkrarı"
                                       required>
                                <label for="password_confirmation">Şifrə təkrarı *</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Role and Status -->
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted border-bottom pb-2 mb-3">
                                <i class="fas fa-shield-alt me-1"></i>
                                İcazələr və Status
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select <?= isset($validation_errors['role']) ? 'is-invalid' : '' ?>" 
                                        id="role" 
                                        name="role" 
                                        required>
                                    <option value="">Rol seçin</option>
                                    <option value="user" <?= (Session::get('old_input')['role'] ?? '') === 'user' ? 'selected' : '' ?>>
                                        İstifadəçi
                                    </option>
                                    <option value="admin" <?= (Session::get('old_input')['role'] ?? '') === 'admin' ? 'selected' : '' ?>>
                                        Administrator
                                    </option>
                                </select>
                                <label for="role">İstifadəçi Rolu *</label>
                                <?php if (isset($validation_errors['role'])): ?>
                                    <div class="invalid-feedback">
                                        <?= $this->e($validation_errors['role'][0]) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-3 mb-3">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       <?= (Session::get('old_input')['is_active'] ?? true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">
                                    <strong>Hesab Aktiv</strong>
                                    <div class="form-text">İstifadəçi sistemə daxil ola bilər</div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Role Information -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-2"></i>
                            Rol İzahları:
                        </h6>
                        <ul class="mb-0">
                            <li><strong>İstifadəçi:</strong> Yalnız icazə verilmiş səhifələrə giriş edə bilər</li>
                            <li><strong>Administrator:</strong> Bütün sistem funksiyalarına tam giriş</li>
                        </ul>
                    </div>
                </div>
                
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="/admin/users" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>
                            Ləğv et
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>
                            İstifadəçini Yarat
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Help Panel -->
    <div class="col-lg-4 col-xl-6">
        <div class="card bg-light">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="fas fa-question-circle me-2"></i>
                    Kömək və Məsləhətlər
                </h6>
            </div>
            <div class="card-body">
                <div class="help-item mb-3">
                    <h6><i class="fas fa-lock text-primary me-2"></i>Şifrə Təhlükəsizliyi</h6>
                    <p class="small text-muted mb-0">
                        Güclü şifrə üçün ən azı 8 simvol, böyük və kiçik hərflər, rəqəmlər istifadə edin.
                    </p>
                </div>
                
                <div class="help-item mb-3">
                    <h6><i class="fas fa-user-shield text-success me-2"></i>Rol Seçimi</h6>
                    <p class="small text-muted mb-0">
                        Admin rolunu yalnız sistem idarəçilərinə verin. Adi istifadəçilər üçün "İstifadəçi" rolunu seçin.
                    </p>
                </div>
                
                <div class="help-item mb-3">
                    <h6><i class="fas fa-envelope text-info me-2"></i>Email Ünvanı</h6>
                    <p class="small text-muted mb-0">
                        Email ünvanı unikal olmalıdır və sistem bildirişləri üçün istifadə olunacaq.
                    </p>
                </div>
                
                <div class="help-item">
                    <h6><i class="fas fa-toggle-on text-warning me-2"></i>Hesab Statusu</h6>
                    <p class="small text-muted mb-0">
                        Deaktiv edilmiş hesablar sistemə giriş edə bilməz, lakin məlumatları qorunur.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Clear old input after displaying
Session::remove('old_input');
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password confirmation validation
    const password = document.getElementById('password');
    const passwordConfirmation = document.getElementById('password_confirmation');
    
    function validatePasswordMatch() {
        if (password.value && passwordConfirmation.value) {
            if (password.value !== passwordConfirmation.value) {
                passwordConfirmation.setCustomValidity('Şifrələr uyğun gəlmir');
            } else {
                passwordConfirmation.setCustomValidity('');
            }
        }
    }
    
    password.addEventListener('input', validatePasswordMatch);
    passwordConfirmation.addEventListener('input', validatePasswordMatch);
    
    // Role selection help
    const roleSelect = document.getElementById('role');
    roleSelect.addEventListener('change', function() {
        // Could add dynamic help text based on role selection
    });
});
</script>