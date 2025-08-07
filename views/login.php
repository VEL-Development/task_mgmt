<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow Pro - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="assets/css/modern.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="brand-logo">
                    <i class="fas fa-tasks"></i>
                </div>
                <h1 class="brand-title">TaskFlow Pro</h1>
                <p class="brand-subtitle">Welcome back! Please sign in to your account</p>
            </div>
            
            <div class="login-form-container">
                <form method="POST" action="index.php?action=auth" id="loginForm">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-group-login">
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" name="username" class="form-input-login" placeholder="Username" required>
                            <label class="form-label-login">Username</label>
                        </div>
                    </div>
                    
                    <div class="form-group-login">
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" name="password" class="form-input-login" placeholder="Password" required>
                            <label class="form-label-login">Password</label>
                            <button type="button" class="password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye" id="passwordIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            <span class="checkmark"></span>
                            Remember me
                        </label>
                        <a href="#" class="forgot-password">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <span class="btn-text">Sign In</span>
                        <i class="fas fa-arrow-right btn-icon"></i>
                    </button>
                </form>
                

                

            </div>
        </div>
        
        <div class="login-background">
            <div class="bg-shape shape-1"></div>
            <div class="bg-shape shape-2"></div>
            <div class="bg-shape shape-3"></div>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const passwordInput = document.querySelector('input[name="password"]');
            const passwordIcon = document.getElementById('passwordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                passwordIcon.className = 'fas fa-eye';
            }
        }
        

        
        // Show alerts
        <?php if(isset($_GET['error'])): ?>
        Swal.fire({
            title: 'Login Failed',
            text: 'Invalid username or password!',
            icon: 'error',
            confirmButtonColor: '#6366f1'
        });
        <?php endif; ?>
        
        <?php if(isset($_GET['success'])): ?>
        Swal.fire({
            title: 'Registration Successful!',
            text: 'Please sign in with your credentials',
            icon: 'success',
            confirmButtonColor: '#6366f1'
        });
        <?php endif; ?>
        
        // Form animation
        document.addEventListener('DOMContentLoaded', function() {
            const loginCard = document.querySelector('.login-card');
            loginCard.style.opacity = '0';
            loginCard.style.transform = 'translateY(30px)';
            
            setTimeout(() => {
                loginCard.style.transition = 'all 0.6s ease';
                loginCard.style.opacity = '1';
                loginCard.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>