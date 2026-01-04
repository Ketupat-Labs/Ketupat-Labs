<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Set Semula Kata Laluan - CompuPlay</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/LOGOCompuPlay.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets/images/LOGOCompuPlay.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-wrapper">
            <!-- Logo Section -->
            <div class="auth-logo">
                <img src="{{ asset('assets/images/LogoCompuPlay.jpg') }}" alt="CompuPlay Logo" class="logo-img" onerror="this.onerror=null; this.src='{{ asset('assets/images/LogoCompuPlay.jpg') }}'">
            </div>

            <!-- Reset Password Form -->
            <div class="auth-form active">
                <h2>Set Semula Kata Laluan</h2>
                <p class="form-subtitle">Reset Password</p>
                <p class="form-description">Masukkan kata laluan baharu anda</p>
                <p class="form-description-english">Enter your new password</p>

                <div class="error-message" id="resetPasswordError"></div>
                <div class="success-message" id="resetPasswordSuccess"></div>

                <form id="resetPasswordForm">
                    <input type="hidden" id="resetToken" value="{{ $token }}">
                    <input type="hidden" id="resetEmail" value="{{ request('email', '') }}">

                    <div class="form-group">
                        <label for="resetPasswordEmail">
                            <i class="fas fa-envelope"></i> Alamat Emel
                        </label>
                        <input type="email" id="resetPasswordEmailInput" placeholder="Masukkan alamat emel anda" value="{{ request('email', '') }}" autocomplete="email" required>
                    </div>

                    <div class="form-group">
                        <label for="newPassword">
                            <i class="fas fa-lock"></i> Kata Laluan Baharu
                        </label>
                        <div class="password-container">
                            <input type="password" id="newPassword" placeholder="Masukkan kata laluan baharu (min. 8 aksara)" autocomplete="new-password" required minlength="8">
                            <button type="button" class="password-toggle" onclick="togglePassword('newPassword', 'newPasswordIcon')">
                                <i class="fas fa-eye" id="newPasswordIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirmNewPassword">
                            <i class="fas fa-lock"></i> Sahkan Kata Laluan Baharu
                        </label>
                        <div class="password-container">
                            <input type="password" id="confirmNewPassword" placeholder="Masukkan semula kata laluan baharu" autocomplete="new-password" required minlength="8">
                            <button type="button" class="password-toggle" onclick="togglePassword('confirmNewPassword', 'confirmNewPasswordIcon')">
                                <i class="fas fa-eye" id="confirmNewPasswordIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="fas fa-key"></i> Set Semula Kata Laluan
                    </button>
                </form>

                <div class="form-switch">
                    <p>Ingat kata laluan anda? <a href="{{ route('login') }}">Log masuk</a></p>
                </div>
            </div>

            <!-- Back to Home -->
            <div class="back-home">
                <a href="{{ route('home') }}">
                    <i class="fas fa-arrow-left"></i> Kembali ke Laman Utama
                </a>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/api-config.js') }}"></script>
    <script>
        // Toggle password visibility
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const passwordIcon = document.getElementById(iconId);

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.classList.remove('fa-eye');
                passwordIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordIcon.classList.remove('fa-eye-slash');
                passwordIcon.classList.add('fa-eye');
            }
        }

        // Show error message
        function showError(elementId, message) {
            const errorDiv = document.getElementById(elementId);
            errorDiv.textContent = message;
            errorDiv.classList.add('show');
            setTimeout(() => {
                errorDiv.classList.remove('show');
            }, 5000);
        }

        // Show success message
        function showSuccess(elementId, message) {
            const successDiv = document.getElementById(elementId);
            successDiv.textContent = message;
            successDiv.classList.add('show');
            setTimeout(() => {
                successDiv.classList.remove('show');
            }, 5000);
        }

        // Hide message
        function hideMessage(elementId) {
            const messageDiv = document.getElementById(elementId);
            messageDiv.classList.remove('show');
        }

        // Reset Password Form Handler
        document.getElementById('resetPasswordForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const token = document.getElementById('resetToken').value;
            const email = document.getElementById('resetPasswordEmailInput').value;
            const password = document.getElementById('newPassword').value;
            const passwordConfirmation = document.getElementById('confirmNewPassword').value;

            // Hide previous messages
            hideMessage('resetPasswordError');
            hideMessage('resetPasswordSuccess');

            // Validation
            if (!email || !password || !passwordConfirmation) {
                showError('resetPasswordError', 'Sila isi semua medan yang diperlukan');
                return;
            }

            if (password.length < 8) {
                showError('resetPasswordError', 'Kata laluan mesti sekurang-kurangnya 8 aksara');
                return;
            }

            if (password !== passwordConfirmation) {
                showError('resetPasswordError', 'Kata laluan tidak sepadan');
                return;
            }

            try {
                // Show loading state
                const submitButton = document.querySelector('#resetPasswordForm button[type="submit"]');
                const originalButtonText = submitButton.innerHTML;
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

                const { response, data } = await apiPost(API_ENDPOINTS.resetPassword, {
                    token: token,
                    email: email,
                    password: password,
                    password_confirmation: passwordConfirmation
                });

                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;

                if (response && response.ok && data && data.status === 200) {
                    showSuccess('resetPasswordSuccess', data.message || 'Kata laluan anda telah berjaya ditetapkan semula. Sila log masuk dengan kata laluan baharu.');
                    
                    // Clear form
                    document.getElementById('resetPasswordForm').reset();
                    document.getElementById('resetToken').value = token;
                    document.getElementById('resetPasswordEmailInput').value = email;
                    
                    // Redirect to login after 3 seconds
                    setTimeout(() => {
                        window.location.href = '{{ route("login") }}';
                    }, 3000);
                } else {
                    showError('resetPasswordError', data?.message || 'Gagal menetapkan semula kata laluan. Sila cuba lagi.');
                }
            } catch (error) {
                console.error('Reset password error:', error);
                const submitButton = document.querySelector('#resetPasswordForm button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fas fa-key"></i> Set Semula Kata Laluan';
                }

                if (error.data && error.data.message) {
                    showError('resetPasswordError', error.data.message);
                } else {
                    showError('resetPasswordError', 'Ralat berlaku. Sila cuba lagi kemudian.');
                }
            }
        });
    </script>
</body>
</html>
