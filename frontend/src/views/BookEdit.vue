<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api/client';

const props = defineProps({ id: { type: [String, Number], default: null } });
const router = useRouter();

const isEdit = computed(() => !!props.id);

const form = ref({ title: '', author: '', year: new Date().getFullYear(), genre: '' });
const error = ref('');
const busy  = ref(false);

async function load() {
  if (!isEdit.value) return;
  try {
    const { data } = await api.get(`/api/books/${props.id}`);
    form.value = { ...data };
  } catch (e) {
    error.value = e.message;
  }
}

async function submit() {
  error.value = '';
  busy.value  = true;
  try {
    const payload = {
      title:  form.value.title?.trim(),
      author: form.value.author?.trim(),
      year:   Number(form.value.year),
      genre:  form.value.genre?.trim() || undefined,
    };
    if (isEdit.value) {
      await api.put(`/api/books/${props.id}`, payload);
      router.push({ name: 'book', params: { id: props.id } });
    } else {
      const { data } = await api.post('/api/books', payload);
      router.push({ name: 'book', params: { id: data.data?.id || data.id } });
    }
  } catch (e) {
    const d = e.response?.data;
    error.value = d?.errors ? Object.values(d.errors).join(' • ') : (d?.error || e.message);
  } finally {
    busy.value = false;
  }
}

onMounted(load);
</script>

<template>
  <div class="card" style="max-width: 600px; margin: 0 auto;">
    <h2 style="margin-top: 0;">{{ isEdit ? 'Edit book' : 'New book' }}</h2>
    <p v-if="error" class="alert error">{{ error }}</p>
    <div class="row">
      <div>
        <label>Title</label>
        <input v-model="form.title" />
      </div>
      <div>
        <label>Author</label>
        <input v-model="form.author" />
      </div>
    </div>
    <div class="row">
      <div>
        <label>Year</label>
        <input v-model.number="form.year" type="number" />
      </div>
      <div>
        <label>Genre</label>
        <input v-model="form.genre" />
      </div>
    </div>
    <p style="margin-top: 18px; display: flex; gap: 10px;">
      <button class="primary" :disabled="busy" @click="submit">
        {{ busy ? 'Saving…' : (isEdit ? 'Update' : 'Create') }}
      </button>
      <button @click="router.back()">Cancel</button>
    </p>
  </div>
</template>
