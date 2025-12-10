// Authentication Helper Functions
// Handles token storage and API authentication

class AuthHelper {
    constructor() {
        this.tokenKey = 'auth_token';
        this.sessionKey = 'user_session';
    }

    // Store authentication token
    setToken(token) {
        if (token) {
            localStorage.setItem(this.tokenKey, token);
        }
    }

    // Get authentication token
    getToken() {
        return localStorage.getItem(this.tokenKey);
    }

    // Remove token
    removeToken() {
        localStorage.removeItem(this.tokenKey);
        sessionStorage.removeItem(this.sessionKey);
    }

    // Check if user is authenticated (has token or session)
    isAuthenticated() {
        return !!this.getToken() || sessionStorage.getItem('userLoggedIn') === 'true';
    }

    // Get auth headers for API requests
    getAuthHeaders() {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };

        // Add Bearer token if available
        const token = this.getToken();
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        return headers;
    }

    // Store user session data
    setSession(userData) {
        sessionStorage.setItem('userLoggedIn', 'true');
        sessionStorage.setItem('userEmail', userData.email || '');
        sessionStorage.setItem('userRole', userData.role || '');
        sessionStorage.setItem('userName', userData.name || userData.full_name || '');
        sessionStorage.setItem('userId', userData.user_id || userData.id || '');
        sessionStorage.setItem(this.sessionKey, JSON.stringify(userData));
    }

    // Get user session data
    getSession() {
        const sessionData = sessionStorage.getItem(this.sessionKey);
        return sessionData ? JSON.parse(sessionData) : null;
    }

    // Clear all auth data
    clearAuth() {
        this.removeToken();
        sessionStorage.removeItem('userLoggedIn');
        sessionStorage.removeItem('userEmail');
        sessionStorage.removeItem('userRole');
        sessionStorage.removeItem('userName');
        sessionStorage.removeItem('userId');
        sessionStorage.removeItem(this.sessionKey);
    }
}

// Create global instance
const authHelper = new AuthHelper();

// Enhanced API call function with authentication
async function authenticatedApiCall(url, options = {}) {
    const authHeaders = authHelper.getAuthHeaders();
    
    const mergedOptions = {
        ...options,
        headers: {
            ...authHeaders,
            ...(options.headers || {}),
        },
        credentials: 'include', // Important for cookies/sessions
    };

    try {
        const response = await fetch(url, mergedOptions);
        
        // Handle 401 Unauthorized - token expired or invalid
        if (response.status === 401) {
            authHelper.clearAuth();
            // Redirect to login if not already there
            if (!window.location.pathname.includes('login')) {
                window.location.href = '/login';
            }
            throw new Error('Authentication required');
        }

        const contentType = response.headers.get('content-type');
        let data;
        
        if (contentType && contentType.includes('application/json')) {
            data = await response.json();
        } else {
            const text = await response.text();
            console.error('Non-JSON response:', text.substring(0, 500));
            throw new Error('Invalid response format');
        }

        return { response, data };
    } catch (error) {
        console.error('API call error:', error);
        throw error;
    }
}

