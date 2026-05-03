# KanbanPro

A multi-category Kanban task management app built with Laravel 13, Alpine.js, SortableJS, and Material Design 3.

## Features

- Multi-category board (vertical lanes) × status columns (horizontal)
- Drag-and-drop tasks between columns — persisted via AJAX
- Drag to trash bin to delete
- One-click complete button (Asana-style circle)
- Quick-add tasks inline without opening a modal
- Priority arrows (↑↓) on each card to change priority instantly
- Right-click context menu on tasks, categories, and statuses
- Google OAuth + email/password login
- Role-based access (admin / user)
- Material Design 3 UI

---

## Quick Start (Docker)

### 1. Clone and configure

```bash
git clone https://github.com/krishnacck/kanban.git
cd kanban
cp .env.docker .env
```

Edit `.env` and set:
- `APP_KEY` — run `php artisan key:generate --show` locally or set any base64 string
- `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` — from [Google Cloud Console](https://console.cloud.google.com/)
- `GOOGLE_REDIRECT_URI` — set to `http://localhost:8000/auth/google/callback`

### 2. Start containers

```bash
docker compose up -d --build
```

This starts:
| Container | Role | Port |
|---|---|---|
| `kanban_app` | PHP 8.3-FPM | internal |
| `kanban_nginx` | Nginx web server | **8000** |
| `kanban_db` | MySQL 8.0 | 3306 |
| `kanban_queue` | Laravel queue worker | internal |

### 3. Run migrations and seed

```bash
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force
```

### 4. Open the app

Visit **http://localhost:8000**

Default credentials (from seeder):
- **Admin**: `admin@example.com` / `password`
- **User**: `alice@example.com` / `password`

---

## Local Development (without Docker)

```bash
cp .env.example .env
# Set DB_CONNECTION=sqlite and touch database/database.sqlite
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

---

## Running Tests

```bash
php artisan test
```

---

## Environment Variables

| Variable | Description |
|---|---|
| `DB_CONNECTION` | `mysql` (Docker) or `sqlite` (local) |
| `DB_HOST` | `db` in Docker, `127.0.0.1` locally |
| `GOOGLE_CLIENT_ID` | Google OAuth client ID |
| `GOOGLE_CLIENT_SECRET` | Google OAuth client secret |
| `GOOGLE_REDIRECT_URI` | Must match Google Console authorized redirect URI |

---

## Tech Stack

- **Backend**: Laravel 13, PHP 8.3
- **Frontend**: Alpine.js, SortableJS, Tailwind CSS v4, Material Design 3
- **Auth**: Laravel Socialite (Google OAuth)
- **DB**: MySQL (Docker) / SQLite (local)
- **Tests**: PestPHP with property-based tests
