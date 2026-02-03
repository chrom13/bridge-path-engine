# Bridge Path Engine

A Laravel-based AI-powered skill gap analysis engine that identifies skill gaps between users and job opportunities, then recommends personalized mentorship packages through expert mentors.

## Overview

Bridge Path Engine integrates with the Torre API and AI services (Gemini/OpenAI) to provide:
- **Real-time job search** from Torre's global opportunities database
- **AI-powered skill gap analysis** comparing your Torre Genome against job requirements
- **Interactive radar chart visualization** showing skill match levels
- **Personalized mentor recommendations** with AI-suggested hour packages
- **Asynchronous processing** with real-time status updates via polling
- **Mentorship booking system** with tiered pricing (3/10/20 hours)

## Tech Stack

- **Backend**: Laravel 11 (PHP 8.3)
- **Database**: MySQL 8.0
- **Cache/Queue**: Redis 7
- **Web Server**: Nginx
- **Frontend**: Tailwind CSS (CDN) + Blade Templates
- **AI**: Google Gemini API (with OpenAI fallback support)
- **Containerization**: Docker + Docker Compose
- **Charts**: Chart.js for radar visualization

## Prerequisites

Before starting, ensure you have the following installed:

- [Docker Desktop](https://www.docker.com/products/docker-desktop) (v20.10+)
- [Docker Compose](https://docs.docker.com/compose/install/) (v2.0+)
- Git
- **Google Gemini API Key** or **OpenAI API Key**

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

The `.env` file requires configuration for Docker services and AI APIs:

```env
# Database Configuration
DB_HOST=db
DB_DATABASE=bridge_path
DB_USERNAME=bridge_user
DB_PASSWORD=secret

# Redis Configuration
REDIS_HOST=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis

# Torre API Configuration
TORRE_API_URL=https://torre.ai/api
TORRE_API_TIMEOUT=30
TORRE_CACHE_TTL=86400

# AI Provider (gemini or openai)
AI_PROVIDER=gemini

# OpenAI Configuration (optional)
OPENAI_API_KEY=your-openai-key-here
OPENAI_MODEL=gpt-3.5-turbo
OPENAI_TIMEOUT=60

# Gemini Configuration (recommended)
GEMINI_API_KEY=your-gemini-key-here
GEMINI_MODEL=gemini-pro
GEMINI_TIMEOUT=60
```

**Important:** You must provide either a Gemini API key or OpenAI API key for AI analysis to work.

### 3. Build and Start Docker Containers

Build and start all services in detached mode:

```bash
docker-compose up -d --build
```

This will start the following containers:
- **app**: PHP 8.3-FPM application server
- **nginx**: Web server (accessible on `http://localhost:8000`)
- **db**: MySQL 8.0 database
- **redis**: Redis cache and queue backend
- **queue**: Laravel queue worker for async AI analysis

### 4. Generate Application Key

```bash
docker-compose exec app php artisan key:generate
```

### 5. Run Database Migrations

```bash
docker-compose exec app php artisan migrate
```

This creates the following tables:
- `users` - User accounts (standard Laravel)
- `cache` - Cache entries
- `jobs` - Queue jobs
- `analysis_results` - AI analysis results and status

### 6. Verify Installation

Check that all containers are running:

```bash
docker-compose ps
```

Access the application:

```
http://localhost:8000
```

You should see the login page.

## Application Flow

### 1. Authentication with Torre

1. Navigate to `http://localhost:8000`
2. Enter a valid **Torre username** (e.g., `torrenegra`)
3. Application fetches your Torre Genome data
4. Profile data cached in Redis for 24 hours
5. Redirected to home page showing your top 5 strengths

### 2. Search for Opportunities

- Use the search bar to find opportunities (e.g., "Laravel", "React", "Remote")
- Browse paginated results with full details:
  - Job title, company, location
  - Required skills with proficiency levels
  - Salary range and benefits
  - Remote work availability

### 3. Analyze Skill Match

Click **"Analyze Match"** on any opportunity to:
1. Dispatch async AI analysis job to Redis queue
2. Show animated loading page with real-time progress
3. Poll analysis status every 2 seconds
4. Auto-redirect to results when complete (typically 10-30 seconds)

### 4. View Analysis Results

The AI-generated analysis includes:
- **Analysis Summary**: 2-3 sentence overview of your match
- **Radar Chart**: Interactive visualization comparing your skills vs job requirements
- **Priority Skill Gaps**: 3-5 gaps ranked by severity (Critical/Moderate/Minor)
- **Mentor Recommendations**: 1-3 expert mentors with AI-suggested hour packages

### 5. Book Mentorship Sessions

Click **"Book Session"** to see:
- **3 hours ($89)**: Perfect for minor skill gaps
- **10 hours ($279)**: Ideal for moderate gaps (most common recommendation)
- **20 hours ($529)**: Critical gap mastery and full transformation

The AI recommends the optimal package based on gap severity.

## AI Integration

### Supported AI Providers

The application supports two AI providers:

**Google Gemini (Recommended)**
- Model: `gemini-pro`
- Fast and reliable
- No quota issues with free tier
- Set `AI_PROVIDER=gemini` in `.env`

**OpenAI (Alternative)**
- Models: `gpt-3.5-turbo`, `gpt-4o`
- Requires paid plan for consistent availability
- Set `AI_PROVIDER=openai` in `.env`

### AI Analysis Components

The AI analyzes:
1. **Your Torre Genome**: Skills, proficiency levels, and weights
2. **Job Requirements**: Required skills and their proficiency expectations
3. **Gap Identification**: Compares and identifies top 3-5 gaps
4. **Severity Classification**: Critical (must-have), Moderate (important), Minor (nice-to-have)
5. **Mentor Matching**: Selects best mentors from predefined expert database
6. **Hour Recommendations**: Suggests 3, 10, or 20 hours based on gap complexity

### Mentor Database

Currently includes 4 expert mentors (hardcoded in service):
- **Marcus Laravel** (`m_laravel_pro`): Laravel Expert & Backend Architecture
- **Sarah Frontend** (`m_frontend_wiz`): Frontend Development & UI/UX
- **David DevOps** (`m_devops_guru`): DevOps, CI/CD & Cloud Infrastructure
- **Emma Leadership** (`m_soft_skills`): Soft Skills, Leadership & Communication

## Queue System

The application uses Redis queues for async AI analysis.

### Starting the Queue Worker

The queue worker runs automatically in the `queue` container. To monitor it:

```bash
# View queue worker logs
docker-compose logs -f queue

# Restart queue worker
docker-compose restart queue

# Manually process queue
docker-compose exec app php artisan queue:work --queue=default
```

### Queue Jobs

- **AnalyzeOpportunityJob**: Handles AI skill gap analysis
  - Timeout: 120 seconds
  - Retries: 1 (no automatic retry)
  - Updates status: `pending` → `processing` → `completed`/`failed`

### Monitoring Queue

```bash
# Check pending jobs
docker-compose exec redis redis-cli LLEN queues:default

# View all queue keys
docker-compose exec redis redis-cli KEYS "queues:*"

# Monitor Redis activity
docker-compose exec redis redis-cli monitor
```

## Project Structure

```
bridge-path-engine/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php          # Torre authentication
│   │   └── OpportunityController.php   # Search, analyze, booking
│   ├── Jobs/
│   │   └── AnalyzeOpportunityJob.php   # Async AI analysis job
│   ├── Models/
│   │   └── AnalysisResult.php          # Analysis results model
│   ├── Services/
│   │   ├── TorreApiService.php         # Torre API integration
│   │   └── CareerStrategistService.php # AI analysis service
│   └── DTOs/
│       ├── AnalysisResultDTO.php       # Main analysis result
│       ├── SkillGapDTO.php             # Individual skill gap
│       ├── RadarChartDataDTO.php       # Chart.js data
│       └── MentorRecommendationDTO.php # Mentor details
├── database/migrations/
│   └── 2026_02_03_000000_create_analysis_results_table.php
├── resources/views/
│   ├── auth/login.blade.php            # Torre login
│   ├── home.blade.php                  # Dashboard with top strengths
│   ├── results.blade.php               # Job search results
│   ├── analysis-loading.blade.php      # Animated loading with polling
│   ├── analysis.blade.php              # AI analysis results
│   └── layouts/app.blade.php           # Main layout
├── routes/web.php                      # Application routes
└── config/services.php                 # API configurations
```

## Common Commands

### Container Management

```bash
# Start all containers
docker-compose up -d

# Stop all containers
docker-compose down

# Restart a specific container
docker-compose restart app

# View logs
docker-compose logs -f [service-name]

# Rebuild containers
docker-compose up -d --build
```

### Laravel Commands

```bash
# Run migrations
docker-compose exec app php artisan migrate

# Clear all caches
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear

# List all routes
docker-compose exec app php artisan route:list

# Access Laravel Tinker
docker-compose exec app php artisan tinker
```

### Database Access

```bash
# Access MySQL CLI
docker-compose exec db mysql -u bridge_user -psecret bridge_path

# Export database
docker-compose exec db mysqldump -u bridge_user -psecret bridge_path > backup.sql

# Import database
docker-compose exec -T db mysql -u bridge_user -psecret bridge_path < backup.sql
```

## Testing the Application

### Valid Torre Usernames

- `torrenegra` - Alexander Torrenegra (Founder)
- `renanpeixotox` - Renan Peixoto (Head of Engineering)
- `joranboc` - Jorge Bocanegra (Tech Lead)
- Or use your own Torre username!

### Testing Torre API Integration

```bash
docker-compose exec app php artisan tinker
```

```php
use App\Services\TorreApiService;
$api = app(TorreApiService::class);

// Fetch user genome
$user = $api->getUserGenome('torrenegra');
echo "User: " . ($user['name'] ?? 'Not found') . PHP_EOL;

// Search opportunities
$results = $api->searchOpportunities('Laravel', 10, 0);
echo "Found: " . ($results['total'] ?? 0) . " opportunities" . PHP_EOL;
```

### Testing AI Analysis

1. Login with a Torre username
2. Search for "software engineer"
3. Click "Analyze Match" on any opportunity
4. Watch the animated loading page poll every 2 seconds
5. View results when complete

## Technical Trade-offs

This MVP was built with speed-to-market in mind. Below are the conscious trade-offs made:

| Decision | "Ideal" Quality Sacrificed | Speed Benefit | Tech Lead Verdict |
|----------|---------------------------|---------------|-------------------|
| **Mentors Hardcoded** | High. Requires a deploy to change text. Violates Single Responsibility. | Very High. Avoids creating migrations, Models, Seeders, and administrative CRUDs. | ✅ **Good Tactical Decision.** Do not spend time on a CRUD for a single admin user. Moving to a config file is a good middle ground. |
| **Arrays in Service Inputs** | Type Safety. We don't know what keys `$userGenome` has without reading external API docs. | Medium. Avoids creating 2-3 additional DTO classes and their mappers. | ⚠️ **Dangerous Technical Debt.** Common source of silent bugs. Recommended to pay this debt soon. |
| **Session based on Array** | Weak Structure. Accessing properties via string `$session['location']` is error-prone. | High. Laravel's Auth system handles arrays natively very easily. | ✅ **Acceptable.** Functional for a prototype, but will scale poorly if session data grows. |
| **Polling vs WebSockets** | Network Efficiency. Polling creates 1 request every 2s per user. | High. Setting up WebSockets requires extra infrastructure (Redis/Reverb) and config. | ✅ **Acceptable for MVP.** Polling is simple and reliable for low traffic. Switch to WebSockets if user base grows. |
| **Simple Job Implementation** | Robustness. If the job fails, there's basic error handling but no complex retry backoff for AI rate limits specifically handled in the job logic (generic retry only). | Medium. Quick to implement. | ✅ **Acceptable.** Sufficient for now. Detailed retry logic for specific API errors (429 vs 500) can be added later. |

### Recommended Improvements for Production

1. **Move mentors to database** - Create admin CRUD for managing mentors
2. **Implement DTOs for API responses** - Replace arrays with strongly-typed DTOs
3. **Switch to WebSockets** - Replace polling with Laravel Reverb/Pusher for real-time updates
4. **Advanced retry logic** - Add exponential backoff for specific AI API errors
5. **Add comprehensive tests** - Unit, feature, and browser tests
6. **Implement rate limiting** - Protect against abuse of AI analysis endpoint
7. **Add user dashboard** - Show analysis history and mentor bookings

## Caching Strategy

### Torre API Cache (24 hours)
- User genome data: `torre:api:genome:{username}`
- Opportunity details: `torre:api:opportunity:{id}`
- Search results: `torre:api:search:{hash}` (1 hour TTL)

### Monitor Cache

```bash
# View all Torre cache keys
docker-compose exec redis redis-cli KEYS "torre:*"

# Check TTL for user genome
docker-compose exec redis redis-cli TTL "torre:api:genome:torrenegra"

# Clear specific cache
docker-compose exec redis redis-cli DEL "torre:api:genome:torrenegra"

# Clear all cache
docker-compose exec redis redis-cli FLUSHALL
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

### AI Analysis Fails

1. Check AI provider configuration in `.env`
2. Verify API key is valid and has quota
3. Check queue worker logs: `docker-compose logs -f queue`
4. View Laravel logs: `tail -f storage/logs/laravel.log`

### Queue Not Processing

```bash
# Restart queue worker
docker-compose restart queue

# Check Redis connection
docker-compose exec app php artisan tinker
>>> Cache::store('redis')->ping();
```

### Database Connection Errors

- Ensure MySQL container is fully started: `docker-compose ps`
- Check credentials in `.env` match `docker-compose.yml`
- Wait 10-15 seconds after starting containers for MySQL to initialize

### Permission Issues

```bash
# Fix storage permissions
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

## Environment Variables Reference

### Required

```env
DB_HOST=db
DB_DATABASE=bridge_path
DB_USERNAME=bridge_user
DB_PASSWORD=secret

REDIS_HOST=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis

AI_PROVIDER=gemini  # or openai
GEMINI_API_KEY=your-key-here  # or OPENAI_API_KEY
```

### Optional

```env
TORRE_API_TIMEOUT=30
TORRE_CACHE_TTL=86400
GEMINI_MODEL=gemini-pro
GEMINI_TIMEOUT=60
```

## API Endpoints

### Web Routes

- `GET /` - Login page
- `POST /login` - Torre authentication
- `POST /logout` - Logout
- `GET /home` - Dashboard with top strengths
- `GET /search` - Search opportunities
- `GET /analyze/{id}` - Start AI analysis (dispatches job)
- `GET /analysis-loading/{analysisId}` - Loading page with polling
- `GET /analysis-status/{analysisId}` - AJAX status check
- `POST /apply/{id}` - Apply to opportunity

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

**Built with Laravel 11, Tailwind CSS, and AI-powered by Google Gemini**
