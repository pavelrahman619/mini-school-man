import { createRouter, createWebHistory } from 'vue-router';
import Login from '../views/Login.vue';
import DashboardView from '../views/DashboardView.vue';
import StudentListView from '../views/StudentListView.vue';
import AttendanceRecordingView from '../views/AttendanceRecordingView.vue';

const routes = [
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { requiresAuth: false }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: DashboardView,
    meta: { requiresAuth: true }
  },
  {
    path: '/students',
    name: 'Students',
    component: StudentListView,
    meta: { requiresAuth: true }
  },
  {
    path: '/attendance',
    name: 'Attendance',
    component: AttendanceRecordingView,
    meta: { requiresAuth: true }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Navigation guard for authentication
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('auth_token');
  const requiresAuth = to.meta.requiresAuth !== false;
  
  if (requiresAuth && !token) {
    // Redirect to login if authentication is required but no token exists
    next({ name: 'Login', query: { redirect: to.fullPath } });
  } else if (to.name === 'Login' && token) {
    // Redirect to dashboard if already authenticated and trying to access login
    next({ name: 'Dashboard' });
  } else {
    next();
  }
});

export default router;
