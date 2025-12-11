<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Log Masuk / Daftar - CompuPlay</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo e(asset('assets/images/LOGOCompuPlay.png')); ?>">
    <link rel="shortcut icon" type="image/png" href="<?php echo e(asset('assets/images/LOGOCompuPlay.png')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/css/auth.css')); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-wrapper">
            <!-- Logo Section -->
            <div class="auth-logo">
                <img src="<?php echo e(asset('assets/images/LogoCompuPlay.jpg')); ?>" alt="CompuPlay Logo" class="logo-img" onerror="this.onerror=null; this.src='<?php echo e(asset('assets/images/LogoCompuPlay.jpg')); ?>'">
            </div>

            <!-- Role Selection Tabs -->
            <div class="role-tabs">
                <button class="role-tab active" data-role="cikgu" onclick="switchRole('cikgu')">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Cikgu</span>
                    <p class="role-subtitle">Teacher</p>
                </button>
                <button class="role-tab" data-role="pelajar" onclick="switchRole('pelajar')">
                    <i class="fas fa-user-graduate"></i>
                    <span>Pelajar</span>
                    <p class="role-subtitle">Student</p>
                </button>
            </div>

            <!-- Form Container -->
            <div class="form-container">
                <!-- Login Form -->
                <div class="auth-form active" id="loginForm">
                    <h2>Log Masuk</h2>
                    <p class="form-subtitle">Login</p>
                    <p class="form-description">Selamat kembali! Sila log masuk untuk teruskan</p>
                    <p class="form-description-english">Welcome back! Please login to continue</p>

                    <div class="error-message" id="loginError"></div>

                    <form id="loginFormElement">
                        <div class="form-group">
                            <label for="loginEmail">
                                <i class="fas fa-envelope"></i> Alamat Emel
                            </label>
                            <input type="email" id="loginEmail" placeholder="Masukkan alamat emel anda" required>
                        </div>

                        <div class="form-group">
                            <label for="loginPassword">
                                <i class="fas fa-lock"></i> Kata Laluan
                            </label>
                            <div class="password-container">
                                <input type="password" id="loginPassword" placeholder="Masukkan kata laluan anda" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('loginPassword', 'loginPasswordIcon')">
                                    <i class="fas fa-eye" id="loginPasswordIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-options">
                            <label class="checkbox-label">
                                <input type="checkbox" id="rememberMe">
                                <span>Ingat saya</span>
                            </label>
                            <a href="#" class="forgot-password">Lupa kata laluan?</a>
                        </div>

                        <button type="submit" class="btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Log Masuk
                        </button>
                    </form>

                    <div class="form-switch">
                        <p>Tiada akaun? <a href="#" onclick="switchToRegister()">Daftar sekarang</a></p>
                    </div>
                </div>

                <!-- Registration Form -->
                <div class="auth-form" id="registerForm">
                    <h2>Daftar Akaun</h2>
                    <p class="form-subtitle">Create Account</p>
                    <p class="form-description">Sertai CompuPlay hari ini dan mulakan perjalanan pembelajaran anda</p>
                    <p class="form-description-english">Join CompuPlay today and start your learning journey</p>

                    <div class="error-message" id="registerError"></div>
                    <div class="success-message" id="registerSuccess"></div>

                    <form id="registerFormElement">
                        <div class="form-group">
                            <label for="registerName">
                                <i class="fas fa-user"></i> Nama Penuh
                            </label>
                            <input type="text" id="registerName" placeholder="Masukkan nama penuh anda" required>
                        </div>

                        <div class="form-group">
                            <label for="registerEmail">
                                <i class="fas fa-envelope"></i> Alamat Emel
                            </label>
                            <input type="email" id="registerEmail" placeholder="Masukkan alamat emel anda" required>
                        </div>

                        <div class="form-group">
                            <label for="registerPassword">
                                <i class="fas fa-lock"></i> Kata Laluan
                            </label>
                            <div class="password-container">
                                <input type="password" id="registerPassword" placeholder="Masukkan kata laluan (min. 8 aksara)" required minlength="8">
                                <button type="button" class="password-toggle" onclick="togglePassword('registerPassword', 'registerPasswordIcon')">
                                    <i class="fas fa-eye" id="registerPasswordIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="registerConfirmPassword">
                                <i class="fas fa-lock"></i> Sahkan Kata Laluan
                            </label>
                            <div class="password-container">
                                <input type="password" id="registerConfirmPassword" placeholder="Masukkan semula kata laluan" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('registerConfirmPassword', 'registerConfirmPasswordIcon')">
                                    <i class="fas fa-eye" id="registerConfirmPasswordIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="agreeTerms" required>
                                <span>Saya bersetuju dengan <a href="#">Terma & Syarat</a> dan <a href="#">Dasar Privasi</a></span>
                            </label>
                        </div>

                        <button type="submit" class="btn-primary">
                            <i class="fas fa-user-plus"></i> Daftar
                        </button>
                    </form>

                    <div class="form-switch">
                        <p>Sudah ada akaun? <a href="#" onclick="switchToLogin()">Log masuk</a></p>
                    </div>
                </div>
            </div>

            <!-- Back to Home -->
            <div class="back-home">
                <a href="<?php echo e(route('home')); ?>">
                    <i class="fas fa-arrow-left"></i> Kembali ke Laman Utama
                </a>
            </div>
        </div>
    </div>

    <script src="<?php echo e(asset('assets/js/api-config.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/js/auth.js')); ?>"></script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Material\resources\views/auth/login.blade.php ENDPATH**/ ?>