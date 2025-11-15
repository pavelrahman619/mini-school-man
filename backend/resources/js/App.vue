<template>
  <div id="app">
    <NavigationBar v-if="$route.name !== 'Login'" />
    <main class="main-content">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { NavigationBar } from './components';
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  min-height: 100vh;
  background-color: #f5f5f5;
}

.main-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1rem;
}
</style>
