// API Configuration
// Get base URL including path (e.g., http://localhost/Material)
// Extract base path from current location (removes filename from pathname)
const getBasePath = () => {
    const pathname = window.location.pathname;
    const pathParts = pathname.split('/').filter(part => part);
    // Remove the last part (filename like login.html)
    pathParts.pop();
    return pathParts.length > 0 ? '/' + pathParts.join('/') : '';
};

const API_BASE_URL = window.location.origin + getBasePath();

// API Endpoints
const API_ENDPOINTS = {
    login: `${API_BASE_URL}/api/auth/login.php`,
    register: `${API_BASE_URL}/api/auth/register.php`,
    logout: `${API_BASE_URL}/api/auth/logout`,
    me: `${API_BASE_URL}/api/auth/me.php`,
};

// API Helper Functions
async function apiCall(url, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        credentials: 'include', // Important for cookies/sessions
    };

    const mergedOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...(options.headers || {}),
        },
    };

    try {
        const response = await fetch(url, mergedOptions);
        
        // Check content type before parsing
        const contentType = response.headers.get('content-type');
        let data;
        
        if (contentType && contentType.includes('application/json')) {
            // Clone response before parsing in case we need to read as text
            const responseClone = response.clone();
            try {
                data = await response.json();
            } catch (jsonError) {
                // If JSON parsing fails, read text from clone for debugging
                const text = await responseClone.text();
                console.error('Failed to parse JSON response:', text.substring(0, 500));
                throw new Error('Invalid JSON response from server. Server may have returned an error page.');
            }
        } else {
            // Not JSON - likely an HTML error page
            const text = await response.text();
            console.error('Non-JSON response received:', text.substring(0, 500));
            throw new Error('Server returned non-JSON response. This usually indicates a PHP error. Check server logs.');
        }

        // Return response and data - let the caller check the status code
        // HTTP error status codes (4xx, 5xx) are still valid responses with JSON data
        
        // Log error responses for debugging
        if (!response.ok) {
            console.error(`API Error [${response.status}]:`, {
                url: url,
                status: response.status,
                statusText: response.statusText,
                data: data
            });
        }
        
        return { response, data };
    } catch (error) {
        // Only log if it's not a network error we've already handled
        if (!error.message.includes('Invalid JSON') && !error.message.includes('non-JSON')) {
            console.error('API call error:', error);
        }
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

