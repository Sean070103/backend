# Protocol & Discussion Platform – Laravel API

Community-powered API for protocols, threads, comments, reviews, and votes. Built for the Junior Full Stack Developer Assessment (React/Next.js + Laravel + Typesense).

## Stack

- **Laravel 12** (PHP 8.2+)
- **Typesense** (optional) – search for protocols and threads
- **MySQL / PostgreSQL / SQLite** (configurable via `.env`)

## Setup

### 1. Clone and install

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Database

**SQLite (default, no extra setup):**

```bash
touch database/database.sqlite
php artisan migrate
```

**MySQL/PostgreSQL:** set `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` in `.env`, then:

```bash
php artisan migrate
```

### 3. Seed data

```bash
php artisan db:seed
```

Creates 12 protocols, 10 threads, 30+ comments, 20 reviews, and 50 votes (mock data).

### 4. Typesense (optional)

To enable search:

1. In `.env` set:
   - `TYPESENSE_ENABLED=true`
   - `TYPESENSE_HOST=` (e.g. `f8g1svtrc4xbjdnwp-1.a1.typesense.net`)
   - `TYPESENSE_PORT=443`
   - `TYPESENSE_PROTOCOL=https`
   - `TYPESENSE_API_KEY=` (Admin API key)

2. After seeding (or if the index is empty), reindex:

   ```bash
   php artisan typesense:reindex
   ```

   Or call `POST /api/reindex` (returns counts of indexed protocols and threads).

With `TYPESENSE_ENABLED=false`, search falls back to database (title/content).

### 5. Run the API

```bash
php artisan serve
```

Base URL: `http://localhost:8000`. Health: `http://localhost:8000/up`.

---

## API overview

All API routes are under `/api`. Responses are JSON. Paginated lists use Laravel’s default `data`, `links`, `meta` structure.

### Protocols

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/protocols` | List protocols (paginated). Query: `search`, `sort`, `page` |
| GET | `/api/protocols/{id}` | Single protocol |
| POST | `/api/protocols` | Create (body: `title`, `content`, `tags[]`, `author`) |
| PUT | `/api/protocols/{id}` | Update |
| DELETE | `/api/protocols/{id}` | Delete |

**Sort:** `sort=recent` \| `most_reviewed` \| `top_rated` \| `most_upvoted` (default: `recent`).

### Threads

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/threads` | List threads. Query: `search`, `sort`, `protocol_id`, `page` |
| GET | `/api/threads/{id}` | Single thread |
| POST | `/api/threads` | Create (body: `protocol_id`, `title`, `body`, `tags[]`, `user_id`) |
| PUT | `/api/threads/{id}` | Update |
| DELETE | `/api/threads/{id}` | Delete |

**Sort:** `sort=recent` \| `upvoted`.

### Comments

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/threads/{id}/comments` | Nested comments for a thread |
| POST | `/api/comments` | Create (body: `thread_id`, `body`, `parent_id?`, `user_id`) |

### Reviews

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/protocols/{id}/reviews` | Paginated reviews for a protocol |
| POST | `/api/reviews` | Create/update (body: `protocol_id`, `rating` 1–5, `feedback?`, `user_id`). One review per user per protocol. |

### Votes

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/votes` | Create or update vote (body: `user_id`, `voteable_id`, `voteable_type`, `value`). `voteable_type`: `App\Models\Protocol`, `App\Models\Thread`, or `App\Models\Comment`. `value`: `1` or `-1`. One vote per user per item. Response includes `votes_count`. |

### Reindex (Typesense)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/reindex` | Reindex all protocols and threads into Typesense. Returns `protocols`, `threads`, `message`. |

---

## Typesense configuration

- **Collections:** `protocols` (id, title, tags, votes_count, average_rating), `threads` (id, title, body, tags, votes_count).
- **Indexing:** Protocols and threads are synced to Typesense on create/update/delete (when `TYPESENSE_ENABLED=true`).
- **Search:** For list endpoints, use query param `search=...`; with Typesense enabled, search uses Typesense; otherwise it uses the database.
- **Keys:** Use the **Admin API key** in the Laravel backend (`.env`). The **Search-only API key** is for frontend-only search if you ever call Typesense directly from the client; this API uses the backend to query Typesense.

---

## .env.example

`.env.example` includes:

- App, DB, and session settings
- CORS (`CORS_ALLOWED_ORIGINS`) for the frontend
- Typesense: `TYPESENSE_ENABLED`, `TYPESENSE_HOST`, `TYPESENSE_PORT`, `TYPESENSE_PROTOCOL`, `TYPESENSE_API_KEY`
- Production notes (e.g. Render)

Copy to `.env` and fill in values (and run `php artisan key:generate`).

---

## Deployment

See **DEPLOY.md** for deploying this API on Render (Docker + PostgreSQL).

---

## License

MIT.
