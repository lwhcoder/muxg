import './bootstrap';

// Set up authentication token from Laravel session
document.addEventListener('DOMContentLoaded', function() {
    // Get auth token from meta tag or session data
    const token = document.querySelector('meta[name="auth-token"]')?.getAttribute('content');
    if (token) {
        localStorage.setItem('auth_token', token);
    }
    
    // Set up CSRF token for axios requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    }
    
    // Set up auth token for axios requests
    const authToken = localStorage.getItem('auth_token');
    if (authToken) {
        window.axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;
    }
});

// Global utility functions
window.formatDate = function(dateString) {
    return new Date(dateString).toLocaleDateString();
};

window.formatTime = function(dateString) {
    return new Date(dateString).toLocaleTimeString();
};

window.formatDateTime = function(dateString) {
    return new Date(dateString).toLocaleString();
};
