# Bridge Path Engine

A Laravel-based skill gap matchmaking engine that identifies skill gaps between users and job opportunities, then recommends personalized learning paths through courses and expert mentors.

## Overview

Bridge Path Engine integrates with the Torre API to:
- Compare user skills (Torre Genome) against job requirements (Torre Opportunity)
- Calculate weighted match scores and confidence levels
- Identify critical skill gaps and growth opportunities
- Recommend AI-powered learning advice, courses, and expert mentors
- Suggest alternative "bridge roles" when direct matches are low

## Tech Stack

- **Backend**: Laravel 11 (PHP 8.3)
- **Database**: MySQL 8.0
- **Cache/Queue**: Redis 7
- **Web Server**: Nginx
- **Frontend**: Tailwind CSS + Blade
- **Containerization**: Docker + Docker Compose

## Prerequisites

Before starting, ensure you have the following installed:

- [Docker Desktop](https://www.docker.com/products/docker-desktop) (v20.10+)
- [Docker Compose](https://docs.docker.com/compose/install/) (v2.0+)
- Git

## Getting Started

### 1. Clone the Repository

```bash
git clone <repository-url> bridge-path-engine
cd bridge-path-engine
```

### 2. Environment Configuration

Copy the example environment file:

```bash
cp .env.example .env
```

The `.env` file is pre-configured with Docker service names. Key configurations:

```env
DB_HOST=db
DB_DATABASE=bridge_path
DB_USERNAME=bridge_user
DB_PASSWORD=secret

REDIS_HOST=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis

TORRE_API_URL=https://torre.ai/api
```

### 3. Build and Start Docker Containers

Build and start all services in detached mode:

```bash
docker-compose up -d --build
```

This will start the following containers:
- **app**: PHP 8.3-FPM application server
- **nginx**: Web server (accessible on port 8000)
- **db**: MySQL 8.0 database
- **redis**: Redis cache and queue backend
- **queue**: Laravel queue worker

### 4. Generate Application Key

```bash
docker-compose exec app php artisan key:generate
```

### 5. Verify Installation

Check that all containers are running:

```bash
docker-compose ps
```

Test the database connection:

```bash
docker-compose exec app php artisan db:show
```

Test Redis connectivity:

```bash
docker-compose exec app php artisan tinker --execute="use Illuminate\Support\Facades\Cache; Cache::store('redis')->put('test', 'working', 60); echo Cache::store('redis')->get('test');"
```

Access the application from inside the container:

```bash
docker-compose exec nginx curl http://localhost
```

You should see the Laravel welcome page.

## Common Commands

### Container Management

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# Rebuild containers
docker-compose up -d --build

# View container logs
docker-compose logs -f [service-name]

# View all logs
docker-compose logs -f
```

### Laravel Artisan Commands

```bash
# Run migrations
docker-compose exec app php artisan migrate

# Run seeders
docker-compose exec app php artisan db:seed

# Clear cache
docker-compose exec app php artisan cache:clear

# List routes
docker-compose exec app php artisan route:list

# Run tests
docker-compose exec app php artisan test
```

### Database Access

```bash
# Access MySQL CLI
docker-compose exec db mysql -u bridge_user -psecret bridge_path

# Run database migrations
docker-compose exec app php artisan migrate

# Rollback migrations
docker-compose exec app php artisan migrate:rollback
```

### Redis CLI

```bash
# Access Redis CLI
docker-compose exec redis redis-cli

# Monitor Redis commands
docker-compose exec redis redis-cli monitor
```

### Composer

```bash
# Install dependencies
docker-compose exec app composer install

# Add a package
docker-compose exec app composer require [package-name]

# Update dependencies
docker-compose exec app composer update
```

## Project Structure

```
bridge-path-engine/
├── app/
│   ├── Http/Controllers/     # API and web controllers
│   ├── Models/               # Eloquent models
│   ├── Services/             # Business logic services
│   ├── DTOs/                 # Data Transfer Objects
│   └── Clients/              # External API clients (Torre)
├── database/
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── routes/
│   ├── web.php              # Web routes
│   └── api.php              # API routes
├── resources/
│   └── views/               # Blade templates
├── tests/                   # PHPUnit tests
├── docker/                  # Docker configuration files
│   ├── nginx/
│   ├── php/
│   └── mysql/
├── docker-compose.yml       # Docker services configuration
└── Dockerfile              # PHP application container
```

## Core Features (In Development)

### Matchmaking Engine
- Weighted skill comparison algorithm
- Gap classification (Critical vs Growth)
- Match score calculation
- Bridge role recommendations

### Recommendation System
- **Tier 1**: AI-powered advice (OpenAI/Gemini)
- **Tier 2**: Course recommendations
- **Tier 3**: Expert mentor matching

### API Integrations
- Torre Genome API (user skills)
- Torre Opportunity API (job requirements)
- OpenAI/Gemini for AI recommendations

## Development Workflow

_To be updated as the project evolves_

## Testing

```bash
# Run all tests
docker-compose exec app php artisan test

# Run specific test file
docker-compose exec app php artisan test tests/Feature/MatchmakingTest.php

# Run with coverage
docker-compose exec app php artisan test --coverage
```

## Troubleshooting

### Containers won't start
```bash
# Check logs for errors
docker-compose logs

# Rebuild from scratch
docker-compose down -v
docker-compose up -d --build
```

### Database connection errors
- Ensure MySQL container is fully started: `docker-compose ps`
- Check credentials in `.env` match `docker-compose.yml`
- Wait a few seconds after starting containers for MySQL to initialize

### Permission issues
```bash
# Fix storage permissions
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R laravel:laravel storage bootstrap/cache
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
