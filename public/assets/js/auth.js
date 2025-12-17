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

// Login Form Handler
document.getElementById('loginFormElement').addEventListener('submit', async function (e) {
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

        console.log('Login response:', {
            response: {
                status: response.status,
                ok: response.ok,
                statusText: response.statusText
            },
            data: data
        });

        // Check response - handle both success and error cases
        console.log('Login check:', {
            responseOk: response?.ok,
            responseStatus: response?.status,
            dataStatus: data?.status,
            hasData: !!data?.data,
            hasDataObject: data?.data ? Object.keys(data.data) : null,
            fullData: data
        });

        // Success if HTTP status is 200 and JSON status is 200
        if (response && response.ok && data) {
            // Check for successful login - either status 200 or just successful HTTP response
            const isSuccess = (data.status === 200) || (response.status === 200 && !data.status);

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

                    console.log('Login successful! User data stored in sessionStorage');
                    console.log('Stored data:', {
                        userId: userId,
                        email: userData.email || email,
                        name: userData.name || userData.full_name,
                        role: userData.role || currentRole
                    });

                    // Redirect to intended page or dashboard
                    const redirectUrl = data.redirect_url || '/dashboard';
                    console.log('Login successful! Redirecting to:', redirectUrl);

                    // Create a form and submit it to trigger a proper navigation
                    // This ensures cookies are sent with the request
                    const form = document.createElement('form');
                    form.method = 'GET';
                    form.action = redirectUrl;
                    form.style.display = 'none';
                    document.body.appendChild(form);
                    form.submit();
                    return; // Stop execution
                } else {
                    // Response is ok but missing user ID
                    console.error('Login response missing user ID:', data);
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                    showError('loginError', 'Respons pelayan tidak lengkap. Sila cuba lagi.');
                    return;
                }
            }
        }

        // Handle error cases
        // Restore button
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;

        const errorMessage = data?.message || 'Emel atau kata laluan tidak betul. Sila cuba lagi.';
        console.error('Login failed - showing error:', {
            errorMessage,
            responseStatus: response?.status,
            responseOk: response?.ok,
            dataStatus: data?.status,
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
        if (error.response && error.response.status === 404) {
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
});

// Registration Form Handler
document.getElementById('registerFormElement').addEventListener('submit', async function (e) {
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
        const { response, data } = await apiPost(API_ENDPOINTS.register, {
            name,
            email,
            password,
            role: currentRole
        });

        // Success if status is 200
        if (data.status === 200) {
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
            showError('registerError', data.message || 'Pendaftaran gagal. Sila cuba lagi.');
        }
    } catch (error) {
        console.error('Registration error:', error);

        // Show more specific error messages
        if (error.response && error.response.status === 404) {
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
            showError('registerError', 'Ralat berlaku. Sila cuba lagi kemudian.');
        }
    }
});

// Check if already logged in - verify with server first
(async function checkAuthStatus() {
    // Only check if we're on the login page
    if (window.location.pathname !== '/login' && !window.location.pathname.includes('/login')) {
        return;
    }

    try {
        // Make a direct fetch call to avoid the error throwing in apiCall
        const response = await fetch(API_ENDPOINTS.me, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'include',
        });

        const data = await response.json();

        if (data.status === 200 && data.data) {
            // User is authenticated on server, redirect to dashboard
            sessionStorage.setItem('userLoggedIn', 'true');
            sessionStorage.setItem('userId', data.data.user_id);
            sessionStorage.setItem('userEmail', data.data.email);
            sessionStorage.setItem('userName', data.data.name);
            sessionStorage.setItem('userRole', data.data.role);
            window.location.href = '/dashboard';
        } else {
            // Not authenticated, clear any stale sessionStorage
            sessionStorage.removeItem('userLoggedIn');
            sessionStorage.removeItem('userId');
            sessionStorage.removeItem('userEmail');
            sessionStorage.removeItem('userName');
            sessionStorage.removeItem('userRole');
            // Stay on login page - don't redirect
        }
    } catch (error) {
        // API call failed or user not authenticated, clear sessionStorage
        console.log('Auth check: User not authenticated');
        sessionStorage.removeItem('userLoggedIn');
        sessionStorage.removeItem('userId');
        sessionStorage.removeItem('userEmail');
        sessionStorage.removeItem('userName');
        sessionStorage.removeItem('userRole');
        // Stay on login page - don't redirect
    }
})();

