// API Configuration for Laravel
// Updated to use Laravel routes

const API_BASE_URL = window.location.origin;

// Laravel API Routes (using named routes or direct paths)
const API_ENDPOINTS = {
    // Auth endpoints
    login: `${API_BASE_URL}/api/auth/login`,
    register: `${API_BASE_URL}/api/auth/register`,
    logout: `${API_BASE_URL}/api/auth/logout`,
    me: `${API_BASE_URL}/api/auth/me`,
    
    // Upload endpoint
    upload: `${API_BASE_URL}/api/upload`,
    
    // Forum endpoints (to be implemented)
    forum: {
        create: `${API_BASE_URL}/api/forum`,
        list: `${API_BASE_URL}/api/forum`,
        detail: (id) => `${API_BASE_URL}/api/forum/${id}`,
        post: {
            create: (forumId) => `${API_BASE_URL}/api/forum/${forumId}/post`,
            update: (postId) => `${API_BASE_URL}/api/forum/post/${postId}`,
            delete: (postId) => `${API_BASE_URL}/api/forum/post/${postId}`,
        },
        comment: {
            create: `${API_BASE_URL}/api/forum/comment`,
        },
    },
    
    // Messaging endpoints (to be implemented)
    messaging: {
        send: `${API_BASE_URL}/api/messaging/send`,
        conversations: `${API_BASE_URL}/api/messaging/conversations`,
        messages: (conversationId) => `${API_BASE_URL}/api/messaging/conversation/${conversationId}/messages`,
    },
    
    // Notification endpoints (to be implemented)
    notifications: {
        list: `${API_BASE_URL}/api/notifications`,
        markRead: (id) => `${API_BASE_URL}/api/notifications/${id}/read`,
    },
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
            const responseClone = response.clone();
            try {
                data = await response.json();
            } catch (jsonError) {
                const text = await responseClone.text();
                console.error('Failed to parse JSON response:', text.substring(0, 500));
                throw new Error('Invalid JSON response from server.');
            }
        } else {
            const text = await response.text();
            console.error('Non-JSON response received:', text.substring(0, 500));
            throw new Error('Server returned non-JSON response.');
        }

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

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { API_ENDPOINTS, apiCall, apiPost, apiGet, apiPut, apiDelete };
}

