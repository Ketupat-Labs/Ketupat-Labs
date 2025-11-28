// Current role (cikgu or pelajar)
let currentRole = 'cikgu';

// Switch between roles
function switchRole(role) {
    currentRole = role;
    
    // Update tab buttons
    document.querySelectorAll('.role-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelector(`[data-role="${role}"]`).classList.add('active');
    
    // Update form placeholders or labels if needed
    updateFormForRole(role);
}

// Update form based on role
function updateFormForRole(role) {
    // You can customize form fields based on role here if needed
    const roleText = role === 'cikgu' ? 'Cikgu' : 'Pelajar';
    console.log(`Switched to ${roleText} mode`);
}

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

// Switch to registration form
function switchToRegister() {
    document.getElementById('loginForm').classList.remove('active');
    document.getElementById('registerForm').classList.add('active');
    
    // Clear any error messages
    hideMessage('loginError');
    hideMessage('registerError');
    hideMessage('registerSuccess');
}

// Switch to login form
function switchToLogin() {
    document.getElementById('registerForm').classList.remove('active');
    document.getElementById('loginForm').classList.add('active');
    
    // Clear any error messages
    hideMessage('loginError');
    hideMessage('registerError');
    hideMessage('registerSuccess');
}

// Show error message
function showError(elementId, message) {
    const errorDiv = document.getElementById(elementId);
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.classList.add('show');
        setTimeout(() => {
            errorDiv.classList.remove('show');
        }, 5000);
    }
}

// Show success message
function showSuccess(elementId, message) {
    const successDiv = document.getElementById(elementId);
    if (successDiv) {
        successDiv.textContent = message;
        successDiv.classList.add('show');
        setTimeout(() => {
            successDiv.classList.remove('show');
        }, 5000);
    }
}

// Hide message
function hideMessage(elementId) {
    const messageDiv = document.getElementById(elementId);
    if (messageDiv) {
        messageDiv.classList.remove('show');
    }
}

// Login Form Handler
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginFormElement');
    const registerForm = document.getElementById('registerFormElement');
    
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }
    
    // Check if already logged in
    checkAuthStatus();
});

async function handleLogin(e) {
    e.preventDefault();
    
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    const rememberMe = document.getElementById('rememberMe').checked;
    
    // Hide previous errors
    hideMessage('loginError');
    
    // Basic validation
    if (!email || !password) {
        showError('loginError', 'Sila isi semua medan yang diperlukan');
        return;
    }
    
    try {
        // Show loading state
        const submitButton = document.querySelector('#loginFormElement button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        
        const { response, data } = await apiPost(API_ENDPOINTS.login, {
            email, 
            password, 
            role: currentRole,
            remember_me: rememberMe 
        });
        
        // Check if login was successful
        // Success requires: HTTP 200 AND JSON status 200
        const httpOk = response && response.ok && response.status === 200;
        const jsonOk = data && data.status === 200;
        const isSuccess = httpOk && jsonOk;
        
        if (isSuccess) {
            // Get user data from response
            const userData = data.data || data;
            const userId = userData.user_id || userData.id;
            
            if (userId) {
                // Store user session in sessionStorage
                sessionStorage.setItem('userLoggedIn', 'true');
                sessionStorage.setItem('userEmail', userData.email || email);
                sessionStorage.setItem('userRole', userData.role || currentRole);
                sessionStorage.setItem('userName', userData.name || userData.full_name || email);
                sessionStorage.setItem('userId', userId);
                
                console.log('Login successful! Session data:', {
                    userId: userId,
                    email: userData.email,
                    role: userData.role
                });
                
                // Small delay to ensure session cookie is set before redirect
                // This helps prevent session loss during redirect
                setTimeout(() => {
                    console.log('Redirecting to dashboard...');
                    window.location.href = 'Dashboard/dashboard.php';
                }, 100);
                return;
            } else {
                // Response is ok but missing user ID
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
                showError('loginError', 'Respons pelayan tidak lengkap. Sila cuba lagi.');
                return;
            }
        }
        
        // Handle error cases - login failed
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
        
        // Get error message from response
        const errorMessage = data?.message || 
                           (response?.status === 401 ? 'Emel atau kata laluan tidak betul. Sila cuba lagi.' : 
                           (response?.status === 500 ? 'Ralat pelayan. Sila cuba lagi kemudian.' :
                           'Log masuk gagal. Sila cuba lagi.'));
        
        console.error('Login failed:', {
            httpStatus: response?.status,
            jsonStatus: data?.status,
            message: errorMessage,
            data: data
        });
        
        showError('loginError', errorMessage);
    } catch (error) {
        console.error('Login error:', error);
        
        // Restore button
        const submitButton = document.querySelector('#loginFormElement button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-sign-in-alt"></i> Log Masuk';
        }
        
        // Show more specific error messages
        if (error.message && error.message.includes('Invalid JSON')) {
            showError('loginError', 'Ralat pelayan: Respons tidak sah. Sila semak log pelayan atau hubungi pentadbir.');
        } else if (error.message && error.message.includes('non-JSON')) {
            showError('loginError', 'Ralat pelayan: PHP error mungkin berlaku. Sila semak log pelayan.');
        } else if (error.response && error.response.status === 404) {
            showError('loginError', 'API endpoint tidak dijumpai. Sila pastikan server sedang berjalan.');
        } else if (error.response && (error.response.status === 401 || error.status === 401)) {
            showError('loginError', error.data?.message || 'Emel atau kata laluan tidak betul. Sila cuba lagi.');
        } else if (error.response && error.response.status >= 500) {
            showError('loginError', 'Ralat pelayan. Sila cuba lagi kemudian.');
        } else if (error.data && error.data.message) {
            showError('loginError', error.data.message);
        } else {
            showError('loginError', error.message || 'Ralat berlaku. Sila cuba lagi kemudian.');
        }
    }
}

async function handleRegister(e) {
    e.preventDefault();
    
    const name = document.getElementById('registerName').value;
    const email = document.getElementById('registerEmail').value;
    const password = document.getElementById('registerPassword').value;
    const confirmPassword = document.getElementById('registerConfirmPassword').value;
    const agreeTerms = document.getElementById('agreeTerms').checked;
    
    // Hide previous messages
    hideMessage('registerError');
    hideMessage('registerSuccess');
    
    // Validation
    if (!name || !email || !password || !confirmPassword) {
        showError('registerError', 'Sila isi semua medan yang diperlukan');
        return;
    }
    
    if (password.length < 8) {
        showError('registerError', 'Kata laluan mesti sekurang-kurangnya 8 aksara');
        return;
    }
    
    if (password !== confirmPassword) {
        showError('registerError', 'Kata laluan tidak sepadan');
        return;
    }
    
    if (!agreeTerms) {
        showError('registerError', 'Sila bersetuju dengan Terma & Syarat');
        return;
    }
    
    try {
        const submitButton = document.querySelector('#registerFormElement button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        
        const { response, data } = await apiPost(API_ENDPOINTS.register, {
            name, 
            email, 
            password, 
            role: currentRole 
        });
        
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
        
        // Success if status is 200
        if (data && data.status === 200) {
            showSuccess('registerSuccess', 'Pendaftaran berjaya! Sila log masuk.');
            
            // Store user info if available
            if (data.data) {
                sessionStorage.setItem('userEmail', data.data.email || email);
                sessionStorage.setItem('userName', data.data.name || name);
                sessionStorage.setItem('userRole', data.data.role || currentRole);
                if (data.data.user_id) {
                    sessionStorage.setItem('userId', data.data.user_id);
                }
            }
            
            // Clear form
            document.getElementById('registerFormElement').reset();
            
            // Switch to login after 2 seconds
            setTimeout(() => {
                switchToLogin();
                document.getElementById('loginEmail').value = email;
            }, 2000);
        } else {
            showError('registerError', data?.message || 'Pendaftaran gagal. Sila cuba lagi.');
        }
    } catch (error) {
        console.error('Registration error:', error);
        
        // Restore button
        const submitButton = document.querySelector('#registerFormElement button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-user-plus"></i> Daftar';
        }
        
        // Show more specific error messages
        if (error.message && error.message.includes('Invalid JSON')) {
            showError('registerError', 'Ralat pelayan: Respons tidak sah. Sila semak log pelayan atau hubungi pentadbir.');
        } else if (error.message && error.message.includes('non-JSON')) {
            showError('registerError', 'Ralat pelayan: PHP error mungkin berlaku. Sila semak log pelayan.');
        } else if (error.response && error.response.status === 404) {
            showError('registerError', 'API endpoint tidak dijumpai. Sila pastikan server sedang berjalan.');
        } else if (error.response && error.response.status === 409) {
            showError('registerError', error.data?.message || 'Emel sudah didaftarkan. Sila gunakan emel lain atau log masuk.');
        } else if (error.response && error.response.status === 400) {
            showError('registerError', error.data?.message || 'Data tidak sah. Sila periksa semua medan.');
        } else if (error.response && error.response.status >= 500) {
            showError('registerError', 'Ralat pelayan. Sila cuba lagi kemudian.');
        } else if (error.data && error.data.message) {
            showError('registerError', error.data.message);
        } else {
            showError('registerError', error.message || 'Ralat berlaku. Sila cuba lagi kemudian.');
        }
    }
}

// Check if already logged in
async function checkAuthStatus() {
    // Only check if we're on the login page
    if (!window.location.pathname.includes('login.html') && !window.location.pathname.endsWith('/login')) {
        return;
    }
    
    // Check sessionStorage first, then verify with server
    if (sessionStorage.getItem('userLoggedIn') === 'true') {
        // Check with server to verify session is still valid
        try {
            const response = await apiGet(API_ENDPOINTS.me);
            
            // If response is ok and we have session data, redirect to dashboard
            if (response && response.ok && response.data && response.data.status === 200 && response.data.data) {
                window.location.href = 'Dashboard/dashboard.php';
            } else if (response && response.data && response.data.status === 401) {
                // 401 is expected if not logged in - clear sessionStorage silently
                sessionStorage.removeItem('userLoggedIn');
                sessionStorage.removeItem('userId');
                sessionStorage.removeItem('userEmail');
                sessionStorage.removeItem('userName');
                sessionStorage.removeItem('userRole');
            }
        } catch (error) {
            // Handle different error types
            if (error.response && error.response.status === 401) {
                // 401 is expected - user is not logged in, clear sessionStorage silently
                sessionStorage.removeItem('userLoggedIn');
                sessionStorage.removeItem('userId');
                sessionStorage.removeItem('userEmail');
                sessionStorage.removeItem('userName');
                sessionStorage.removeItem('userRole');
            } else if (error.response && error.response.status === 500) {
                // 500 is a server error - log it but don't show to user on login page
                console.error('Server error during auth check:', error);
                // Clear sessionStorage in case of server error
                sessionStorage.removeItem('userLoggedIn');
                sessionStorage.removeItem('userId');
                sessionStorage.removeItem('userEmail');
                sessionStorage.removeItem('userName');
                sessionStorage.removeItem('userRole');
            } else {
                // Other errors - clear sessionStorage and stay on login page
                console.log('Auth check failed:', error);
                sessionStorage.removeItem('userLoggedIn');
                sessionStorage.removeItem('userId');
                sessionStorage.removeItem('userEmail');
                sessionStorage.removeItem('userName');
                sessionStorage.removeItem('userRole');
            }
        }
    } else {
        // Also check server session even if sessionStorage is not set
        // This handles cases where session exists but sessionStorage was cleared
        try {
            const response = await apiGet(API_ENDPOINTS.me);
            if (response && response.ok && response.data && response.data.status === 200 && response.data.data) {
                // Session exists on server, update sessionStorage and redirect
                const userData = response.data.data;
                sessionStorage.setItem('userLoggedIn', 'true');
                sessionStorage.setItem('userEmail', userData.email);
                sessionStorage.setItem('userRole', userData.role);
                sessionStorage.setItem('userName', userData.name);
                sessionStorage.setItem('userId', userData.user_id);
                window.location.href = 'Dashboard/dashboard.php';
            }
            // If 401, that's expected - user is not logged in, do nothing
        } catch (error) {
            // Handle errors silently - 401 is expected, 500 should be logged
            if (error.response && error.response.status === 500) {
                console.error('Server error during auth check:', error);
            }
            // No valid session, stay on login page (this is normal)
        }
    }
}

