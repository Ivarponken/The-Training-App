<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <div id="app">

    <nav>
      <RouterLink to="/">Träningar</RouterLink>
      <RouterLink to="/stats">Statistik</RouterLink>
    </nav>

    <main>
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
nav {
  display: flex;
  gap: 20px;
  padding: 20px;
  background: #f5f5f5;
}


nav a {
  text-decoration: none;
  color: #333;
  font-weight: bold;
}

nav a.router-link-active {
  color: #42b983;
}

main {
  padding: 20px;
}
</style>
