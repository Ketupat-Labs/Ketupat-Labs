// API Configuration
const API_BASE_URL = window.location.origin;

// API Endpoints
const API_ENDPOINTS = {
    login: `${API_BASE_URL}/login`,
    register: `${API_BASE_URL}/api/auth/register`,
    logout: `${API_BASE_URL}/api/auth/logout`,
    me: `${API_BASE_URL}/api/auth/me`,
};

// Get CSRF token from meta tag
function getCsrfToken() {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.getAttribute('content') : null;
}

// API Helper Functions
async function apiCall(url, options = {}) {
    const csrfToken = getCsrfToken();
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        credentials: 'include', // Important for cookies/sessions
    };
    
    // Add CSRF token if available
    if (csrfToken) {
        defaultOptions.headers['X-CSRF-TOKEN'] = csrfToken;
    }

    const mergedOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...options.headers,
        },
    };

    try {
        const response = await fetch(url, mergedOptions);
        const data = await response.json();

        return { response, data };
    } catch (error) {
        console.error('API call error:', error);
        throw error;
    }
}

// POST request helper
async function apiPost(endpoint, data) {
    return apiCall(endpoint, {
        method: 'POST',
        body: JSON.stringify(data),
    });
}

// GET request helper
async function apiGet(endpoint) {
    return apiCall(endpoint, {
        method: 'GET',
    });
}

// PUT request helper
async function apiPut(endpoint, data) {
    return apiCall(endpoint, {
        method: 'PUT',
        body: JSON.stringify(data),
    });
}

// DELETE request helper
async function apiDelete(endpoint) {
    return apiCall(endpoint, {
        method: 'DELETE',
    });
}

