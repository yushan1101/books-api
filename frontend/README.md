# Chapter 9 — Vue 3 Frontend

A minimal **Vue 3 + Vite + Axios** single-page app that consumes the Chapter 9 in-memory Books REST API. No auth, no router, no state-management library — the goal is to keep the code as small and readable as possible.

## What's inside

```
frontend/
├── package.json           # Vue 3 + Vite + Axios only
├── vite.config.js
├── index.html
├── .env.development       # VITE_API_BASE_URL=http://localhost:8000
├── .gitignore
└── src/
    ├── main.js
    ├── style.css
    ├── App.vue                          # main page (list + state)
    └── components/
        └── BookForm.vue                 # create / edit form
```

## What you can do

- Browse all books in the API.
- Click **+ New book** to create one.
- Click **Edit** on any book to update it.
- Click **Delete** to remove a book.
- Click **↻ Reload** to refetch from the API.
- Click **Reset to seed** to wipe your changes and restore the original three books.

> 💾 **Persistence:** The Chapter 9 backend writes books to a local JSON file
> (`var/books.json`) inside the PHP project, so the buttons in this UI actually
> change something that stays around. Real database storage comes in
> Chapter 10 (MySQL via PDO).

## Prerequisites

| Tool             | Version | Verify with |
|------------------|---------|-------------|
| Node.js          | 18 +    | `node -v`   |
| npm              | 9 +     | `npm -v`    |
| Laragon / PHP    | running | `php -v` and Laragon's Apache green |
| Ch9 backend running on `http://localhost:8000` | — | open `http://localhost:8000/` in a browser, see JSON |

## Setup

### 1. Start the Chapter 9 backend

From the **parent** folder (the PHP project):

```
cd ..                  # back to Ch9_BooksAPI_Solution
composer install
php -S localhost:8000 -t public
```

Leave that terminal running. You should be able to open `http://localhost:8000/` and see a JSON response like:

```json
{ "name": "Books REST API", "version": "1.0.0", "docs": "/api/books" }
```

### 2. Install the frontend dependencies

In a **new** terminal:

```
cd Ch9_BooksAPI_Solution/frontend
npm install
```

### 3. Start the dev server

```
npm run dev
```

Vite prints a URL — usually `http://localhost:5173/`. Open it in your browser. The app loads, calls the API, and shows the three seeded books.

## How it works

The whole app fits in two files:

- **`src/App.vue`** — top-level component that:
  - Creates a single Axios instance pointed at `VITE_API_BASE_URL`.
  - Fetches the list on mount with `axios.get('/api/books')`.
  - Tracks the currently-edited book in a `ref(null)`.
  - Sends `POST` / `PUT` / `DELETE` to the API and reloads the list.

- **`src/components/BookForm.vue`** — controlled form that emits `save` and `cancel` events. Its parent (App.vue) does the actual API call.

Environment URL comes from `.env.development`:

```
VITE_API_BASE_URL=http://localhost:8000
```

To target a different backend, change that file and restart `npm run dev` (or add `.env.production` and run `npm run build`).

## Troubleshooting

| Symptom                                             | Fix |
|-----------------------------------------------------|-----|
| Page loads but list is empty + console shows CORS error | The Ch9 backend already enables CORS in `src/Middleware/Cors.php`. Restart the PHP server. |
| `ECONNREFUSED`                                       | The backend isn't running. Start it with `php -S localhost:8000 -t public`. |
| Add / edit / delete returns 200 but the list doesn't change | You're running an older Ch9 backend that uses an in-memory array (state doesn't survive between PHP requests). Pull the latest `BookController.php` — it now persists to `var/books.json`. |
| Want to start over with the original three books    | Click **Reset to seed** in the UI, or stop the server and delete `var/books.json`. |
| `npm: command not found`                            | Install Node.js 18 + from nodejs.org. |
| Vite picks a different port (e.g. 5174)             | Another process is using 5173 — just use whatever URL Vite prints. |

## Build for production

```
npm run build      # writes dist/
npm run preview    # serves dist/ on http://localhost:4173 for a final smoke-test
```

You can deploy the `dist/` folder to any static host (Vercel, Netlify, GitHub Pages, etc.). Don't forget to update `.env.production` to point at the real API URL before building.
