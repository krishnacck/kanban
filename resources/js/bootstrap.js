// Bootstrap: set up CSRF token for all fetch requests
const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
    window.__CSRF_TOKEN__ = token.getAttribute('content');
}
