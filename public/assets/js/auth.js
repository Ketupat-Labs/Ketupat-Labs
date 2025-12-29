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

// Restore remember me checkbox state from localStorage
document.addEventListener('DOMContentLoaded', function() {
    const rememberMeCheckbox = document.getElementById('rememberMe');
    const savedRememberMe = localStorage.getItem('rememberMe');
    if (rememberMeCheckbox && savedRememberMe === 'true') {
        rememberMeCheckbox.checked = true;
    }
    
    // Restore email if remember me was checked
    const savedEmail = localStorage.getItem('savedEmail');
    if (savedEmail && rememberMeCheckbox && rememberMeCheckbox.checked) {
        const emailInput = document.getElementById('loginEmail');
        if (emailInput) {
            emailInput.value = savedEmail;
        }
    }
});

// Login Form Handler
document.getElementById('loginFormElement').addEventListener('submit', async function (e) {
    e.preventDefault();

    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;
    const rememberMe = document.getElementById('rememberMe').checked;
    
    // Save remember me preference to localStorage
    localStorage.setItem('rememberMe', rememberMe ? 'true' : 'false');
    
    // Save email if remember me is checked
    if (rememberMe) {
        localStorage.setItem('savedEmail', email);
    } else {
        localStorage.removeItem('savedEmail');
    }

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
                    if (userData.avatar_url) {
                        sessionStorage.setItem('userAvatar', userData.avatar_url);
                    }

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

// Store registration data for OTP verification
let registrationData = null;

// Registration Form Handler
document.getElementById('registerFormElement').addEventListener('submit', async function (e) {
    e.preventDefault();

    const name = document.getElementById('registerName').value;
    const email = document.getElementById('registerEmail').value;
    const password = document.getElementById('registerPassword').value;
    const confirmPassword = document.getElementById('registerConfirmPassword').value;

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

    try {
        const submitButton = document.getElementById('registerSubmitBtn');
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
        if (data.status === 200 && data.data && data.data.requires_verification) {
            // Store registration data for OTP verification
            registrationData = { name, email, password, role: currentRole };

            // Show OTP verification section
            document.getElementById('otpVerificationSection').style.display = 'block';
            document.getElementById('registerSubmitBtn').style.display = 'none';
            document.getElementById('verifyOtpBtn').style.display = 'block';

            // Disable registration form fields
            document.getElementById('registerName').disabled = true;
            document.getElementById('registerEmail').disabled = true;
            document.getElementById('registerPassword').disabled = true;
            document.getElementById('registerConfirmPassword').disabled = true;

            // Focus on OTP input
            document.getElementById('registerOtp').focus();

            showSuccess('registerSuccess', data.message || 'Kod pengesahan telah dihantar ke emel anda.');
        } else if (data.status === 200) {
            // Old flow (no OTP required) - should not happen but handle it
            showSuccess('registerSuccess', 'Pendaftaran berjaya! Sila log masuk.');
            setTimeout(() => {
                switchToLogin();
                document.getElementById('loginEmail').value = email;
            }, 2000);
        } else {
            showError('registerError', data.message || 'Pendaftaran gagal. Sila cuba lagi.');
        }
    } catch (error) {
        console.error('Registration error:', error);
        const submitButton = document.getElementById('registerSubmitBtn');
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-user-plus"></i> Daftar';
        }

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

// Verify OTP function
async function verifyOtp() {
    if (!registrationData) {
        showError('registerError', 'Data pendaftaran tidak dijumpai. Sila daftar semula.');
        return;
    }

    const otp = document.getElementById('registerOtp').value.trim();

    if (!otp || otp.length !== 6) {
        showError('registerError', 'Sila masukkan kod pengesahan 6 digit');
        return;
    }

    hideMessage('registerError');
    hideMessage('registerSuccess');

    try {
        const verifyButton = document.getElementById('verifyOtpBtn');
        const originalButtonText = verifyButton.innerHTML;
        verifyButton.disabled = true;
        verifyButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyahkan...';

        const { response, data } = await apiPost(API_ENDPOINTS.verifyOtp, {
            email: registrationData.email,
            otp: otp
        });

        verifyButton.disabled = false;
        verifyButton.innerHTML = originalButtonText;

        if (data.status === 200) {
            showSuccess('registerSuccess', data.message || 'Pendaftaran berjaya! Sila log masuk.');

            // Store user info if available
            if (data.data) {
                sessionStorage.setItem('userEmail', data.data.email || registrationData.email);
                sessionStorage.setItem('userName', data.data.name || registrationData.name);
                sessionStorage.setItem('userRole', data.data.role || registrationData.role);
                if (data.data.user_id) {
                    sessionStorage.setItem('userId', data.data.user_id);
                }
            }

            // Clear form and reset
            document.getElementById('registerFormElement').reset();
            registrationData = null;
            document.getElementById('otpVerificationSection').style.display = 'none';
            document.getElementById('registerSubmitBtn').style.display = 'block';
            document.getElementById('verifyOtpBtn').style.display = 'none';
            document.getElementById('registerName').disabled = false;
            document.getElementById('registerEmail').disabled = false;
            document.getElementById('registerPassword').disabled = false;
            document.getElementById('registerConfirmPassword').disabled = false;

            // Switch to login after 2 seconds
            setTimeout(() => {
                switchToLogin();
                document.getElementById('loginEmail').value = registrationData?.email || '';
            }, 2000);
        } else {
            showError('registerError', data.message || 'Kod pengesahan tidak sah. Sila cuba lagi.');
        }
    } catch (error) {
        console.error('OTP verification error:', error);
        const verifyButton = document.getElementById('verifyOtpBtn');
        if (verifyButton) {
            verifyButton.disabled = false;
            verifyButton.innerHTML = '<i class="fas fa-check"></i> Sahkan Kod';
        }

        if (error.data && error.data.message) {
            showError('registerError', error.data.message);
        } else {
            showError('registerError', 'Ralat berlaku. Sila cuba lagi kemudian.');
        }
    }
}

// Resend OTP function
async function resendOtp() {
    if (!registrationData) {
        showError('registerError', 'Data pendaftaran tidak dijumpai. Sila daftar semula.');
        return;
    }

    hideMessage('registerError');
    hideMessage('registerSuccess');

    try {
        const resendButton = document.getElementById('resendOtpBtn');
        resendButton.disabled = true;
        resendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghantar...';

        const { response, data } = await apiPost(API_ENDPOINTS.resendOtp, {
            email: registrationData.email
        });

        resendButton.disabled = false;
        resendButton.innerHTML = '<i class="fas fa-redo"></i> Hantar Semula';

        if (data.status === 200) {
            showSuccess('registerSuccess', data.message || 'Kod pengesahan baru telah dihantar ke emel anda.');
        } else {
            showError('registerError', data.message || 'Gagal menghantar kod semula. Sila cuba lagi.');
        }
    } catch (error) {
        console.error('Resend OTP error:', error);
        const resendButton = document.getElementById('resendOtpBtn');
        if (resendButton) {
            resendButton.disabled = false;
            resendButton.innerHTML = '<i class="fas fa-redo"></i> Hantar Semula';
        }

        if (error.data && error.data.message) {
            showError('registerError', error.data.message);
        } else {
            showError('registerError', 'Ralat berlaku. Sila cuba lagi kemudian.');
        }
    }
}

// Allow Enter key to submit OTP
document.addEventListener('DOMContentLoaded', function () {
    const otpInput = document.getElementById('registerOtp');
    if (otpInput) {
        otpInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                verifyOtp();
            }
        });

        // Auto-format OTP (numbers only)
        otpInput.addEventListener('input', function (e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });
    }
});

// Forgot Password Modal Functions
function showForgotPasswordModal() {
    const modal = document.getElementById('forgotPasswordModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        // Clear previous messages
        hideMessage('forgotPasswordError');
        hideMessage('forgotPasswordSuccess');
        // Clear form
        document.getElementById('forgotPasswordForm').reset();
    }
}

function closeForgotPasswordModal() {
    const modal = document.getElementById('forgotPasswordModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        // Clear messages
        hideMessage('forgotPasswordError');
        hideMessage('forgotPasswordSuccess');
        // Clear form
        document.getElementById('forgotPasswordForm').reset();
    }
}

// Close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('forgotPasswordModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeForgotPasswordModal();
            }
        });
    }
});

// Forgot Password Form Handler
document.addEventListener('DOMContentLoaded', function() {
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const email = document.getElementById('forgotPasswordEmail').value;

            // Hide previous messages
            hideMessage('forgotPasswordError');
            hideMessage('forgotPasswordSuccess');

            // Basic validation
            if (!email) {
                showError('forgotPasswordError', 'Sila masukkan alamat emel anda');
                return;
            }

            try {
                // Show loading state
                const submitButton = forgotPasswordForm.querySelector('button[type="submit"]');
                const originalButtonText = submitButton.innerHTML;
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghantar...';

                const { response, data } = await apiPost(API_ENDPOINTS.forgotPassword, {
                    email: email
                });

                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;

                if (response && response.ok && data && data.status === 200) {
                    showSuccess('forgotPasswordSuccess', data.message || 'Pautan set semula kata laluan telah dihantar ke emel anda. Sila semak peti masuk anda.');
                    
                    // Clear email field
                    document.getElementById('forgotPasswordEmail').value = '';
                    
                    // Close modal after 3 seconds
                    setTimeout(() => {
                        closeForgotPasswordModal();
                    }, 3000);
                } else {
                    showError('forgotPasswordError', data?.message || 'Gagal menghantar pautan set semula. Sila cuba lagi.');
                }
            } catch (error) {
                console.error('Forgot password error:', error);
                const submitButton = forgotPasswordForm.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fas fa-paper-plane"></i> Hantar Pautan Set Semula';
                }

                if (error.data && error.data.message) {
                    showError('forgotPasswordError', error.data.message);
                } else {
                    showError('forgotPasswordError', 'Ralat berlaku. Sila cuba lagi kemudian.');
                }
            }
        });
    }
});

// Check if already logged in - verify with server first
// Note: 401 responses are expected when user is not logged in (normal behavior)
(async function checkAuthStatus() {
    // Only check if we're on the login page
    if (window.location.pathname !== '/login' && !window.location.pathname.includes('/login')) {
        return;
    }

    // If no sessionStorage data, skip API call (user definitely not logged in)
    // This avoids unnecessary 401 errors in console
    if (!sessionStorage.getItem('userLoggedIn') && !sessionStorage.getItem('userId')) {
        return;
    }

    try {
        // Make a direct fetch call to verify server-side authentication
        const response = await fetch(API_ENDPOINTS.me, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'include',
        });

        // If 401 or 403, user is not authenticated (expected on login page)
        // Handle this silently - this is normal behavior, not an error
        if (response.status === 401 || response.status === 403) {
            // Not authenticated, clear any stale sessionStorage
            sessionStorage.removeItem('userLoggedIn');
            sessionStorage.removeItem('userId');
            sessionStorage.removeItem('userEmail');
            sessionStorage.removeItem('userName');
            sessionStorage.removeItem('userRole');
            sessionStorage.removeItem('userAvatar');
            return; // Silently return - 401 is expected when not logged in
        }

        // Only parse JSON if response is OK
        if (!response.ok) {
            return;
        }

        const data = await response.json();

        if (data.status === 200 && data.data) {
            // User is authenticated on server, redirect to dashboard
            sessionStorage.setItem('userLoggedIn', 'true');
            sessionStorage.setItem('userId', data.data.user_id);
            sessionStorage.setItem('userEmail', data.data.email);
            sessionStorage.setItem('userName', data.data.name);
            sessionStorage.setItem('userRole', data.data.role);
            if (data.data.avatar_url) {
                sessionStorage.setItem('userAvatar', data.data.avatar_url);
            }
            window.location.href = '/dashboard';
        } else {
            // Not authenticated, clear any stale sessionStorage
            sessionStorage.removeItem('userLoggedIn');
            sessionStorage.removeItem('userId');
            sessionStorage.removeItem('userEmail');
            sessionStorage.removeItem('userName');
            sessionStorage.removeItem('userRole');
            sessionStorage.removeItem('userAvatar');
            // Stay on login page - don't redirect
        }
    } catch (error) {
        // Network error or other exception
        // Silently handle - clear sessionStorage
        sessionStorage.removeItem('userLoggedIn');
        sessionStorage.removeItem('userId');
        sessionStorage.removeItem('userEmail');
        sessionStorage.removeItem('userName');
        sessionStorage.removeItem('userRole');
        sessionStorage.removeItem('userAvatar');
        // Stay on login page - don't redirect
    }
})();

