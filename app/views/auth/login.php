<?php
use App\Core\Session;
use App\Core\CSRF;
?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- CSRF protection temporarily disabled for Docker testing -->
    
    <title>Giriş - EduLinks</title>
    <meta name="description" content="EduLinks - Təhsil İdarəsi Sənəd İdarəetmə Sistemi">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            max-width: 400px;
            margin: 0 auto;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .login-header {
            text-align: center;
            padding: 2rem 2rem 1rem;
        }
        
        .login-logo {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .login-title {
            color: #2d3748;
            font-weight: 700;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            color: #718096;
            margin-bottom: 0;
        }
        
        .login-form {
            padding: 1rem 2rem 2rem;
        }
        
        .form-floating .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem 0.75rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-floating .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-floating label {
            color: #718096;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 12px;
            padding: 0.875rem 1.5rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .remember-checkbox {
            margin: 1rem 0;
        }
        
        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 1rem;
        }
        
        .footer-text {
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 2rem;
            font-size: 0.9rem;
        }
        
        .input-group-text {
            border: 2px solid #e2e8f0;
            border-right: none;
            background: transparent;
            border-radius: 12px 0 0 12px;
        }
        
        .password-toggle {
            border: 2px solid #e2e8f0;
            border-left: none;
            background: transparent;
            border-radius: 0 12px 12px 0;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-card">
                <!-- Header -->
                <div class="login-header">
                    <div class="login-logo">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h1 class="login-title">EduLinks</h1>
                    <p class="login-subtitle">Təhsil İdarəsi Sənəd İdarəetmə Sistemi</p>
                </div>
                
                <!-- Form -->
                <div class="login-form">
                    <!-- Flash Messages -->
                    <?php
                    $flashTypes = ['success', 'error', 'warning', 'info'];
                    foreach ($flashTypes as $type):
                        $message = Session::getFlash($type);
                        if ($message):
                            $alertClass = match($type) {
                                'success' => 'alert-success',
                                'error' => 'alert-danger',
                                'warning' => 'alert-warning',
                                'info' => 'alert-info',
                                default => 'alert-info'
                            };
                    ?>
                        <div class="alert <?= $alertClass ?> alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                    
                    <form method="POST" action="/login" id="loginForm">
                        <?= CSRF::field() ?>
                        
                        <!-- Email -->
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="Email ünvanınız" required>
                            <label for="email">
                                <i class="fas fa-envelope me-2"></i>Email ünvanı
                            </label>
                        </div>
                        
                        <!-- Password -->
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Şifrəniz" required>
                            <label for="password">
                                <i class="fas fa-lock me-2"></i>Şifrə
                            </label>
                        </div>
                        
                        <!-- Remember Me -->
                        <div class="form-check remember-checkbox">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                            <label class="form-check-label" for="remember">
                                Məni xatırla
                            </label>
                        </div>
                        
                        <!-- Submit -->
                        <button type="submit" class="btn btn-primary btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Daxil ol
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="footer-text">
                <p>&copy; <?= date('Y') ?> Təhsil İdarəsi. Bütün hüquqlar qorunur.</p>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Email və şifrə sahələri mütləqdir.');
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Giriş edilir...';
            submitBtn.disabled = true;
        });
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>