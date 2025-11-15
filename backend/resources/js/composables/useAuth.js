import { ref, computed } from 'vue';
import { authService } from '../services/authService';
import { useRouter } from 'vue-router';

const user = ref(authService.getUser());
const isAuthenticated = ref(authService.isAuthenticated());
const loading = ref(false);
const error = ref(null);

export function useAuth() {
    const router = useRouter();

    /**
     * Login user
     * @param {Object} credentials - { email, password }
     */
    const login = async (credentials) => {
        loading.value = true;
        error.value = null;
        try {
            const data = await authService.login(credentials);
            user.value = data.user;
            isAuthenticated.value = true;
            return data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Login failed';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Logout user
     */
    const logout = async () => {
        loading.value = true;
        try {
            await authService.logout();
            user.value = null;
            isAuthenticated.value = false;
            router.push('/login');
        } catch (err) {
            error.value = err.response?.data?.message || 'Logout failed';
        } finally {
            loading.value = false;
        }
    };

    /**
     * Fetch current user data
     */
    const fetchUser = async () => {
        loading.value = true;
        error.value = null;
        try {
            const data = await authService.getCurrentUser();
            user.value = data.user;
            isAuthenticated.value = true;
            return data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch user';
            user.value = null;
            isAuthenticated.value = false;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Check if user has specific role
     * @param {string} role - Role to check
     */
    const hasRole = computed(() => (role) => {
        return user.value?.role === role;
    });

    return {
        user,
        isAuthenticated,
        loading,
        error,
        login,
        logout,
        fetchUser,
        hasRole,
    };
}
