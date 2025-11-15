<template>
  <div class="home">
    <h1>Welcome to Laravel + Vue + Sanctum</h1>
    <p>User: {{ user?.name }}</p>
    <button @click="logout">Logout</button>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const user = ref(null);

onMounted(async () => {
  try {
    const response = await axios.get('/api/user');
    user.value = response.data;
  } catch (error) {
    console.error('Failed to fetch user:', error);
  }
});

const logout = async () => {
  try {
    await axios.post('/api/logout');
    localStorage.removeItem('token');
    router.push('/login');
  } catch (error) {
    console.error('Logout failed:', error);
  }
};
</script>

<style scoped>
.home {
  padding: 20px;
  text-align: center;
}
button {
  margin-top: 20px;
  padding: 10px 20px;
  cursor: pointer;
}
</style>
