import api from './api';

export const authService = {
    /**
     * Login user and store token
     * @param {Object} credentials - { email, password }
     * @returns {Promise}
     */
    async login(credentials) {
        const response = await api.post('/login', credentials);
        if (response.data.token) {
            localStorage.setItem('auth_token', response.data.token);
            if (response.data.user) {
                localStorage.setItem('user', JSON.stringify(response.data.user));
            }
        }
        return response.data;
    },

    /**
     * Logout user and clear token
     * @returns {Promise}
     */
    async logout() {
        try {
            await api.post('/logout');
        } finally {
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
        }
    },

    /**
     * Get current authenticated user
     * @returns {Promise}
     */
    async getCurrentUser() {
        const response = await api.get('/me');
        if (response.data.user) {
            localStorage.setItem('user', JSON.stringify(response.data.user));
        }
        return response.data;
    },

    /**
     * Check if user is authenticated
     * @returns {boolean}
     */
    isAuthenticated() {
        return !!localStorage.getItem('auth_token');
    },

    /**
     * Get stored user data
     * @returns {Object|null}
     */
    getUser() {
        const user = localStorage.getItem('user');
        return user ? JSON.parse(user) : null;
    },
};
