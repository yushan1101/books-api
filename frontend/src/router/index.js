import { createRouter, createWebHistory } from 'vue-router';

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/',           name: 'books',  component: () => import('../views/BookList.vue') },
    { path: '/books/:id',  name: 'book',   component: () => import('../views/BookDetail.vue'), props: true },
    { path: '/books/new',  name: 'create', component: () => import('../views/BookEdit.vue') },
    { path: '/books/:id/edit', name: 'edit', component: () => import('../views/BookEdit.vue'), props: true },
  ],
});

export default router;
