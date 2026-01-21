<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <div id="app">

    <nav>
      <RouterLink to="/">Träningar</RouterLink>
      <RouterLink to="/lista">Lista</RouterLink>
      <RouterLink to="/stats">Statistik</RouterLink>
      <p style="cursor: pointer;">SE</p>
      <p style="cursor: pointer;">ENG</p>
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
  color: gray;
  font-weight: bold;
}

nav a.router-link-active {
  color: #da7618;
}

main {
  padding: 20px;
}
</style>
