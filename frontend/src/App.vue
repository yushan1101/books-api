<script setup>
import { RouterLink, RouterView } from 'vue-router';
const apiBase = import.meta.env.VITE_API_BASE_URL;
</script>

<template>
  <header>
    <h1>📚 Books API</h1>
    <RouterLink to="/">Books</RouterLink>
    <span class="badge">Chapter 10 • MySQL + PDO</span>
  </header>

  <main>
    <RouterView />
    <p style="text-align: center; color: var(--muted); font-size: 11px; margin-top: 24px;">
      API: {{ apiBase }}
    </p>
  </main>
</template>
