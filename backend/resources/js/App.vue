<template>
  <div id="app">
    <NavigationBar v-if="$route.name !== 'Login'" />
    <main :class="$route.name === 'Login' ? '' : 'main-content'">
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

body {
  margin: 0;
  padding: 0;
}

#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  min-height: 100vh;
}

.main-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1rem;
  background-color: #f5f5f5;
  min-height: 100vh;
}
</style>
