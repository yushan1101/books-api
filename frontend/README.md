# Chapter 10 — Vue 3 Frontend

A **Vue 3 + Vite + Axios + Vue Router** single-page app that consumes the Chapter 10 MySQL/PDO Books API. Adds router-based navigation (list → detail → edit), search & limit, and a dedicated edit page.

## What's inside

```
frontend/
├── package.json           # Vue 3 + Vite + Axios + Vue Router
├── vite.config.js
├── index.html
├── .env.development       # VITE_API_BASE_URL=http://localhost:8000
├── .gitignore
└── src/
    ├── main.js
    ├── style.css
    ├── App.vue                          # top nav + <RouterView />
    ├── api/
    │   └── client.js                    # single Axios instance
    ├── router/
    │   └── index.js                     # 4 routes (list, detail, new, edit)
    └── views/
        ├── BookList.vue                 # search, limit, list + delete
        ├── BookDetail.vue               # full record + actions
        └── BookEdit.vue                 # create OR edit (route-aware)
```

## What you can do

- **Search** by title or author (uses `?q=`).
- **Limit** the result count (uses `?limit=`).
- Open a **detail page** for any book (shows `created_at` / `updated_at`).
- **Create**, **Edit**, **Delete** books.
- Data **persists** across server restarts — the API is backed by MySQL.

## Prerequisites

| Tool                                                | Verify with |
|-----------------------------------------------------|-------------|
| Node.js 18 +                                        | `node -v`   |
| npm 9 +                                             | `npm -v`    |
| Laragon running with MySQL                          | Laragon's MySQL icon green |
| `books_api` database created and seeded            | `mysql -u root -e "SELECT COUNT(*) FROM books_api.books"` returns ≥ 3 |
| Ch10 backend running at `http://localhost:8000`     | open `http://localhost:8000/` |

## Setup

### 1. Set up MySQL (one-time)

```
cd ..                  # back to Ch10_BooksAPI_Solution
mysql -u root < sql/schema.sql
```

You should see a `books_api` database with a `books` table containing three seed rows.

### 2. Start the Chapter 10 backend

Still in `Ch10_BooksAPI_Solution`:

```
composer install
copy .env.example .env       # then fill DB_* values for your Laragon MySQL
php -S localhost:8000 -t public
```

Smoke-test:

```
curl http://localhost:8000/api/books
```

You should see a JSON object with `count` and `data`.

### 3. Install the frontend dependencies

In a **new** terminal:

```
cd Ch10_BooksAPI_Solution/frontend
npm install
```

### 4. Start the dev server

```
npm run dev
```

Open the URL Vite prints (default: `http://localhost:5173/`). The list view loads and shows your seed books.

## Walk-through

1. **List view (`/`)**
   - Type `clean` in the search box, press Enter → API call `/api/books?q=clean`.
   - Set `Limit` to `2` → API call `/api/books?q=clean&limit=2`.
   - Click any book title → opens the detail view.
   - Click **+ New book** → opens the edit form.
2. **Detail view (`/books/1`)**
   - Shows full record with timestamps.
   - **Edit** routes to `/books/1/edit`.
   - **Delete** calls `DELETE /api/books/1` and returns to the list.
3. **Edit / Create (`/books/new` or `/books/1/edit`)**
   - Same component, different mode (decided by the route param).
   - **Update** sends `PUT`; **Create** sends `POST`.

## Verify persistence

1. Add a new book through the UI.
2. **Stop** the PHP server (Ctrl+C in its terminal).
3. **Restart** the PHP server.
4. **Refresh** the browser — your book is still there. (Compare with Chapter 9 where data resets!)

## Build for production

```
npm run build       # outputs ./dist
npm run preview     # serves dist/ on http://localhost:4173
```

To target a different backend URL, set `VITE_API_BASE_URL` in `.env.production` before building.

## Troubleshooting

| Symptom                                    | Likely cause                                         | Fix |
|--------------------------------------------|------------------------------------------------------|-----|
| "Failed to connect" / network error        | PHP server not running                                | `php -S localhost:8000 -t public` |
| "Database connection failed"               | Wrong DB creds, or MySQL stopped                      | Check `.env`, restart Laragon MySQL |
| List is empty, no error                    | `books_api` database empty                            | Re-run `mysql -u root < sql/schema.sql` |
| CORS error in DevTools                     | Frontend origin not in backend CORS                   | Ch10 default is `*`; make sure the backend was restarted after editing |
| Edit page shows blank inputs               | Route param mismatch                                  | Use `:id` not `:bookId` in routes; verify with `console.log(props.id)` |
| 400 on save                                | Validation failed                                     | Check field shown in the error banner (year must be 1000..now, etc.) |
