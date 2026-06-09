import axios from 'axios';

/**
 * Single Axios client for the Chapter 10 Books API.
 * Reads VITE_API_BASE_URL from .env.<mode> at build time.
 */
const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  timeout: 10_000,
});

export default api;
