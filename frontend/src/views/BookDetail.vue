<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, RouterLink } from 'vue-router';
import api from '../api/client';

const props = defineProps({ id: { type: [String, Number], required: true } });
const router = useRouter();

const book  = ref(null);
const error = ref('');

async function load() {
  error.value = '';
  try {
    const { data } = await api.get(`/api/books/${props.id}`);
    book.value = data;
  } catch (e) {
    error.value = e.response?.status === 404 ? 'Book not found' : e.message;
  }
}

async function remove() {
  if (!confirm(`Delete "${book.value.title}"?`)) return;
  try {
    await api.delete(`/api/books/${props.id}`);
    router.push('/');
  } catch (e) {
    error.value = e.response?.data?.error || e.message;
  }
}

onMounted(load);
</script>

<template>
  <p v-if="error" class="alert error">{{ error }}</p>

  <div v-if="book" class="card">
    <h2 style="margin-top: 0;">{{ book.title }}</h2>
    <p style="color: var(--muted); margin: 0 0 16px;">
      by <strong>{{ book.author }}</strong> ({{ book.year }})
      <span class="tag">{{ book.genre }}</span>
    </p>
    <dl style="display: grid; grid-template-columns: 120px 1fr; gap: 6px 14px; font-size: 14px;">
      <dt style="color: var(--muted);">ID</dt>          <dd style="margin: 0;">{{ book.id }}</dd>
      <dt style="color: var(--muted);">Created at</dt>  <dd style="margin: 0;">{{ book.created_at }}</dd>
      <dt style="color: var(--muted);">Updated at</dt>  <dd style="margin: 0;">{{ book.updated_at }}</dd>
    </dl>
    <p style="margin-top: 18px; display: flex; gap: 10px;">
      <RouterLink :to="{ name: 'edit', params: { id: book.id } }">
        <button class="primary">Edit</button>
      </RouterLink>
      <button class="danger" @click="remove">Delete</button>
      <RouterLink to="/"><button>← Back to list</button></RouterLink>
    </p>
  </div>
</template>
