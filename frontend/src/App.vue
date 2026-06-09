<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import BookForm from './components/BookForm.vue';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  timeout: 10_000,
});

const books   = ref([]);
const editing = ref(null);   // null | 'new' | bookObject
const error   = ref('');
const ok      = ref('');
const loading = ref(false);

async function loadBooks() {
  error.value = '';
  loading.value = true;
  try {
    const { data } = await api.get('/api/books');
    books.value = data.data ?? data; // accepts either shape
  } catch (e) {
    error.value = 'Failed to load: ' + (e.message || e);
  } finally {
    loading.value = false;
  }
}

async function saveBook(book) {
  error.value = ''; ok.value = '';
  try {
    if (book.id) {
      await api.put(`/api/books/${book.id}`, book);
      ok.value = 'Book updated';
    } else {
      await api.post('/api/books', book);
      ok.value = 'Book created';
    }
    editing.value = null;
    await loadBooks();
  } catch (e) {
    const data = e.response?.data;
    error.value = data?.errors ? Object.values(data.errors).join(' • ') : (data?.error || e.message);
  }
}

async function removeBook(book) {
  if (!confirm(`Delete "${book.title}"?`)) return;
  error.value = ''; ok.value = '';
  try {
    await api.delete(`/api/books/${book.id}`);
    ok.value = 'Book deleted';
    await loadBooks();
  } catch (e) {
    error.value = e.response?.data?.error || e.message;
  }
}

async function resetData() {
  if (!confirm('Restore the original seed data? This wipes any changes.')) return;
  error.value = ''; ok.value = '';
  try {
    await api.post('/api/reset');
    ok.value = 'Seed data restored';
    await loadBooks();
  } catch (e) {
    error.value = e.response?.data?.error || e.message;
  }
}

onMounted(loadBooks);
</script>

<template>
  <header>
    <h1>📚 Books API</h1>
    <span class="badge">Chapter 9 • File-backed JSON</span>
  </header>

  <main>
    <p class="note">
      <strong>Heads up:</strong> Chapter 9's API has no database yet — instead it
      persists books to a local <code>var/books.json</code> file. Your changes
      survive between requests and server restarts. Real database storage
      (MySQL via PDO) comes in Chapter 10. Use <strong>Reset</strong> to restore
      the original seed data.
    </p>

    <div class="card">
      <div class="row" style="align-items: end;">
        <button class="primary" :disabled="loading" @click="loadBooks">
          {{ loading ? 'Loading…' : '↻ Reload' }}
        </button>
        <button class="primary" @click="editing = 'new'">+ New book</button>
        <button @click="resetData">Reset to seed</button>
      </div>
    </div>

    <BookForm
      v-if="editing !== null"
      :book="editing === 'new' ? null : editing"
      @save="saveBook"
      @cancel="editing = null"
    />

    <p v-if="error" class="alert error">{{ error }}</p>
    <p v-if="ok"    class="alert ok">{{ ok }}</p>

    <div v-if="books.length" class="card">
      <div class="book" v-for="b in books" :key="b.id">
        <div>
          <strong>{{ b.title }}</strong>
          <span style="color: var(--muted); font-weight: normal;"> ({{ b.year }})</span>
          <div class="meta">{{ b.author }} • {{ b.genre }}</div>
        </div>
        <div class="actions">
          <button @click="editing = { ...b }">Edit</button>
          <button class="danger" @click="removeBook(b)">Delete</button>
        </div>
      </div>
    </div>
    <p v-else class="card" style="text-align: center; color: var(--muted);">
      No books yet — click <strong>+ New book</strong> to add one.
    </p>

    <p style="text-align: center; color: var(--muted); font-size: 11px; margin-top: 24px;">
      API: {{ api.defaults.baseURL }}
    </p>
  </main>
</template>
