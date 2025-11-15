<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-500 via-purple-500 to-purple-700 p-5">
    <div class="bg-white rounded-xl shadow-2xl p-10 w-full max-w-md">
      <h1 class="text-gray-800 text-2xl font-bold mb-2 text-center">
        School Attendance System
      </h1>
      <h2 class="text-gray-600 text-xl mb-8 text-center font-normal">
        Login
      </h2>
      
      <form @submit.prevent="handleLogin" class="space-y-5">
        <div>
          <label for="email" class="block mb-2 text-gray-700 font-medium text-sm">
            Email
          </label>
          <input 
            id="email"
            v-model="email" 
            type="email" 
            placeholder="Enter your email" 
            required 
            :disabled="loading"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm transition-colors focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 disabled:bg-gray-100 disabled:cursor-not-allowed"
          />
        </div>
        
        <div>
          <label for="password" class="block mb-2 text-gray-700 font-medium text-sm">
            Password
          </label>
          <input 
            id="password"
            v-model="password" 
            type="password" 
            placeholder="Enter your password" 
            required 
            :disabled="loading"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm transition-colors focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 disabled:bg-gray-100 disabled:cursor-not-allowed"
          />
        </div>
        
        <button 
          type="submit" 
          :disabled="loading"
          class="w-full py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg text-base font-semibold transition-all hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:translate-y-0"
        >
          {{ loading ? 'Logging in...' : 'Login' }}
        </button>
        
        <p v-if="error" class="mt-4 text-red-600 text-center text-sm py-2.5 px-4 bg-red-50 rounded-lg border border-red-200">
          {{ error }}
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth';

const router = useRouter();
const { login, loading, error } = useAuth();

const email = ref('');
const password = ref('');

const handleLogin = async () => {
  try {
    await login({
      email: email.value,
      password: password.value
    });
    
    // Redirect to dashboard after successful login
    router.push('/');
  } catch (err) {
    // Error is already handled by useAuth composable
    console.error('Login failed:', err);
  }
};
</script>
