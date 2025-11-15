<template>
  <div class="login">
    <h1>Login</h1>
    <form @submit.prevent="handleLogin">
      <div>
        <input v-model="email" type="email" placeholder="Email" required />
      </div>
      <div>
        <input v-model="password" type="password" placeholder="Password" required />
      </div>
      <button type="submit">Login</button>
      <p v-if="error" class="error">{{ error }}</p>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const email = ref('');
const password = ref('');
const error = ref('');

const handleLogin = async () => {
  try {
    error.value = '';
    await axios.get('/sanctum/csrf-cookie');
    const response = await axios.post('/api/login', {
      email: email.value,
      password: password.value
    });
    
    localStorage.setItem('token', response.data.token);
    router.push('/');
  } catch (err) {
    error.value = 'Invalid credentials';
    console.error('Login failed:', err);
  }
};
</script>

<style scoped>
.login {
  max-width: 400px;
  margin: 50px auto;
  padding: 20px;
}
form div {
  margin-bottom: 15px;
}
input {
  width: 100%;
  padding: 10px;
  box-sizing: border-box;
}
button {
  width: 100%;
  padding: 10px;
  cursor: pointer;
}
.error {
  color: red;
  margin-top: 10px;
}
</style>
