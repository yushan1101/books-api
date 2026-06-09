<script setup>
import { ref, watchEffect } from 'vue';

const props = defineProps({ book: { type: Object, default: null } });
const emit  = defineEmits(['save', 'cancel']);

const form = ref({ title: '', author: '', year: new Date().getFullYear(), genre: '' });

watchEffect(() => {
  form.value = props.book
    ? { ...props.book }
    : { title: '', author: '', year: new Date().getFullYear(), genre: '' };
});

function submit() {
  emit('save', {
    ...(props.book?.id ? { id: props.book.id } : {}),
    title:  form.value.title.trim(),
    author: form.value.author.trim(),
    year:   Number(form.value.year),
    genre:  form.value.genre?.trim() || undefined,
  });
}
</script>

<template>
  <div class="card">
    <h3 style="margin-top: 0;">{{ props.book?.id ? 'Edit book' : 'New book' }}</h3>
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
    <p style="margin-top: 16px; display: flex; gap: 10px;">
      <button class="primary" @click="submit">Save</button>
      <button @click="$emit('cancel')">Cancel</button>
    </p>
  </div>
</template>
