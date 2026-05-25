# Project Setup Guide

This guide helps a new developer set up and run `back-end-echo-pandas-v1`.

## Stack

- Laravel 12 (PHP)
- React + TypeScript + Vite
- PostgreSQL 15
- Docker Compose (recommended)

## Option A (Recommended): Run with Docker

### 1) Prerequisites

- Docker Desktop (with Docker Compose)
- Git

### 2) Clone and enter the project

```bash
git clone <your-repository-url>
cd back-end-echo-pandas-v1
```

### 3) Start services

```bash
docker compose up -d --build
```

This starts:

- `postgres` on `localhost:5432`
- `pgadmin` on `http://localhost:5050`
- `nginx` (app entry) on `http://localhost:8082`
- `app` (php-fpm) on `localhost:9000`

### 4) Wait for first boot

On first run, the app container runs:

- `composer install`
- `.env` creation if missing
- `php artisan key:generate`
- `php artisan migrate --force`
- `npm install`
- `npm run build`

You can watch logs with:

```bash
docker compose logs -f app
```

### 5) Open the app

- Application: `http://localhost:8082`
- pgAdmin: `http://localhost:5050`
  - Email: `admin@example.com`
  - Password: `admin`

### 6) Useful Docker commands

```bash
# Start
docker compose up -d

# Stop
docker compose down

# Stop and remove DB data volume
docker compose down -v

# Run Laravel commands in container
docker compose exec app php artisan migrate

docker compose exec app php artisan test
```

## Option B: Run Locally (without Docker)

Use this only if you prefer local tooling.

### 1) Prerequisites

- PHP 8.2+
- Composer
- Node.js 20+
- npm
- PostgreSQL 15+

### 2) Install dependencies

```bash
composer install
npm install
```

### 3) Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` database values for your local PostgreSQL. Example:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=echo_panda_db
DB_USERNAME=echo_panda_user
DB_PASSWORD=your_password_here
```

### 4) Migrate database

```bash
php artisan migrate
```

### 5) Run development servers

Use one command (recommended):

```bash
composer run dev
```

Or run manually in separate terminals:

```bash
php artisan serve
npm run dev
php artisan queue:listen --tries=1
```

### 6) Build production assets

```bash
npm run build
```

## Testing and Linting

```bash
# Laravel tests
composer test

# Frontend lint
npm run lint
```

## Common Issues

### Port conflict

If ports `5432`, `5050`, `8082`, or `9000` are in use, stop conflicting services or change the mapped ports in `docker-compose.yml`.

### Permission issues (Linux/macOS)

If Laravel cannot write cache/storage:

```bash
chmod -R 775 storage bootstrap/cache
```

### Fresh reset

```bash
docker compose down -v
docker compose up -d --build
```

## Default Database Values in This Project

Current defaults in `.env.example`:

- DB host: `postgres` (works in Docker network)
- DB name: `echo_panda_db`
- DB user: `echo_panda_user`

If running outside Docker, change `DB_HOST` to `127.0.0.1`.
