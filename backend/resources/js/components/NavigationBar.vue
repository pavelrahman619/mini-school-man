<template>
  <nav class="navbar">
    <div class="navbar-container">
      <div class="navbar-brand">
        <router-link to="/dashboard" class="brand-link">
          School Attendance System
        </router-link>
      </div>
      
      <div class="navbar-menu" v-if="isAuthenticated">
        <router-link to="/dashboard" class="nav-link" active-class="active">
          Dashboard
        </router-link>
        <router-link to="/students" class="nav-link" active-class="active">
          Students
        </router-link>
        <router-link to="/attendance" class="nav-link" active-class="active">
          Attendance
        </router-link>
      </div>
      
      <div class="navbar-user" v-if="isAuthenticated && user">
        <span class="user-name">{{ user.name }}</span>
        <button @click="handleLogout" class="logout-btn" :disabled="loading">
          {{ loading ? 'Logging out...' : 'Logout' }}
        </button>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { useAuth } from '../composables/useAuth';
import { useRouter } from 'vue-router';

const router = useRouter();
const { user, isAuthenticated, logout, loading } = useAuth();

const handleLogout = async () => {
  try {
    await logout();
    router.push('/login');
  } catch (error) {
    console.error('Logout failed:', error);
  }
};
</script>

<style scoped>
.navbar {
  background-color: #2c3e50;
  color: white;
  padding: 1rem 0;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.navbar-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 2rem;
}

.navbar-brand {
  font-size: 1.25rem;
  font-weight: bold;
}

.brand-link {
  color: white;
  text-decoration: none;
  transition: opacity 0.2s;
}

.brand-link:hover {
  opacity: 0.8;
}

.navbar-menu {
  display: flex;
  gap: 1.5rem;
  flex: 1;
}

.nav-link {
  color: white;
  text-decoration: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.nav-link:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.nav-link.active {
  background-color: rgba(255, 255, 255, 0.2);
  font-weight: 500;
}

.navbar-user {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-name {
  font-size: 0.9rem;
  color: #ecf0f1;
}

.logout-btn {
  background-color: #e74c3c;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background-color 0.2s;
}

.logout-btn:hover:not(:disabled) {
  background-color: #c0392b;
}

.logout-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .navbar-container {
    flex-direction: column;
    gap: 1rem;
  }
  
  .navbar-menu {
    width: 100%;
    justify-content: center;
  }
  
  .navbar-user {
    width: 100%;
    justify-content: center;
  }
}
</style>
