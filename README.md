# Protocol & Discussion API (Laravel)

REST API for protocols, threads, comments, reviews, and votes. Optional Typesense search.

## Setup

- PHP 8.4+, Composer, PostgreSQL (or MySQL)
- Copy `.env.example` to `.env`, set `APP_KEY`, `DB_*`, `CORS_ALLOWED_ORIGINS`, optional `TYPESENSE_*`
- `composer install && php artisan migrate --seed`
- Optional: `php artisan typesense:reindex`

## API overview

Base URL: your app URL (e.g. `https://backend-9ho7.onrender.com`). All responses JSON. Paginated lists use `data` + `meta`.

### Protocols

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/protocols` | List. Query: `?search=`, `?sort=recent\|most_reviewed\|top_rated\|most_upvoted`, `?page=` |
| GET | `/api/protocols/{id}` | Single protocol |
| POST | `/api/protocols` | Create (title, content, tags, etc.) |
| PUT/PATCH | `/api/protocols/{id}` | Update |
| DELETE | `/api/protocols/{id}` | Delete |

### Threads

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/threads` | List. Query: `?protocol_id=`, `?search=`, `?sort=recent\|upvoted`, `?page=` |
| GET | `/api/threads/{id}` | Single thread |
| POST | `/api/threads` | Create (title, body, protocol_id) |
| PUT/PATCH | `/api/threads/{id}` | Update |
| DELETE | `/api/threads/{id}` | Delete |

### Comments

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/threads/{id}/comments` | List comments (flat, with `parent_id`). All comments for thread. |
| POST | `/api/threads/{id}/comments` | Create comment. Body: `{ "body": "...", "parent_id": null \| id }`. `thread_id` and `user_id` set by backend. Rate limited (30/min). |

### Reviews

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/protocols/{id}/reviews` | List reviews for protocol |
| POST | `/api/reviews` | Create. Body: `protocol_id`, `rating`, optional `feedback`. Rate limited (20/min). |

### Votes

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/votes` | Create or update vote. Body: `voteable_id`, `voteable_type` (`thread` \| `comment` \| `protocol`), `value` (1 or -1). One vote per user per item. Rate limited (60/min). |

### Reindex (optional)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/reindex` | Reindex protocols and threads to Typesense |

## Auth

Endpoints that create data (comments, votes, reviews) use `user_id` from the request or fallback to a default when no auth is present. For production, use Laravel Sanctum (or similar) and set `user_id` from the authenticated user.

## CORS

Set `CORS_ALLOWED_ORIGINS` to your frontend origin (e.g. `https://task-sand-sigma.vercel.app`).
