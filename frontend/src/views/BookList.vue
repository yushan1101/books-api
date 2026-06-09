<script setup>
import { ref, onMounted, watch } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import api from '../api/client';

const router = useRouter();

const books   = ref([]);
const total   = ref(0);
const q       = ref('');
const limit   = ref(0);          // 0 = no cap
const error   = ref('');
const ok      = ref('');
const loading = ref(false);

async function load() {
  error.value = '';
  loading.value = true;
  try {
    const params = {};
    if (q.value)     params.q     = q.value;
    if (limit.value) params.limit = limit.value;
    const { data } = await api.get('/api/books', { params });
    books.value = data.data;
    total.value = data.count;
  } catch (e) {
    error.value = e.response?.data?.error || e.message;
  } finally {
    loading.value = false;
  }
}

async function remove(book) {
  if (!confirm(`Delete "${book.title}"?`)) return;
  error.value = ''; ok.value = '';
  try {
    await api.delete(`/api/books/${book.id}`);
    ok.value = `Deleted "${book.title}"`;
    await load();
  } catch (e) {
    error.value = e.response?.data?.error || e.message;
  }
}

watch(limit, load);
onMounted(load);
</script>

<template>
  <p class="note">
    <strong>Persistent storage!</strong> The Chapter 10 backend uses MySQL through PDO.
    Restart the server, refresh — your books are still there.
  </p>

  <div class="card">
    <div class="row" style="align-items: end;">
      <div style="flex: 2;">
        <label>Search by title or author</label>
        <input v-model="q" placeholder="e.g. clean" @keyup.enter="load" />
      </div>
      <div style="max-width: 140px;">
        <label>Limit</label>
        <input v-model.number="limit" type="number" min="0" placeholder="0 = all" />
      </div>
      <div>
        <button class="primary" :disabled="loading" @click="load">
          {{ loading ? 'Searching…' : 'Search' }}
        </button>
      </div>
      <div>
        <button class="primary" @click="router.push({ name: 'create' })">+ New book</button>
      </div>
    </div>
  </div>

  <p v-if="error" class="alert error">{{ error }}</p>
  <p v-if="ok"    class="alert ok">{{ ok }}</p>

  <div v-if="books.length" class="card">
    <p style="color: var(--muted); margin: 0 0 12px;">Showing {{ books.length }} of {{ total }}</p>
    <div class="book" v-for="b in books" :key="b.id">
      <div>
        <RouterLink :to="{ name: 'book', params: { id: b.id } }">
          <strong>{{ b.title }}</strong>
        </RouterLink>
        <span class="tag">{{ b.year }}</span>
        <div class="meta">{{ b.author }} • {{ b.genre }}</div>
      </div>
      <div class="actions">
        <button @click="router.push({ name: 'edit', params: { id: b.id } })">Edit</button>
        <button class="danger" @click="remove(b)">Delete</button>
      </div>
    </div>
  </div>
  <p v-else class="card" style="text-align: center; color: var(--muted);">
    No books match — try a different search or click <strong>+ New book</strong>.
  </p>
</template>
