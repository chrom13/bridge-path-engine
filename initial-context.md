Role: Senior Laravel Architect & Product Engineer. Objective: Build a technical assessment for Torre using MySQL 8.0 and Laravel. The app identifies skill gaps between a user and a job, then recommends courses and mentors.

1. Infrastructure (Docker & MySQL)
Create a docker-compose.yml and Dockerfile:

App: PHP 8.3-FPM, Nginx.

DB: MySQL 8.0 (Include persistent volumes and environment variables for MYSQL_DATABASE, MYSQL_USER, etc.).

Cache/Queue: Redis (Essential for performance and background processing).

Configure a Laravel Queue Worker container to handle AI advice and mentor searches.

2. Core Business Logic (The Matchmaking Engine)
Implement a MatchmakingService with the following:

Weighted Skill Comparison: Compare user skills (Torre Genome) vs. job requirements (Torre Opportunity).

Gap Classification: Identify "Critical Gaps" (missing mandatory skills) vs. "Growth Gaps" (level mismatch).

Match Score: Calculate a percentage based on weighted importance of skills.

The "Pivot" Feature: Logic to suggest a "Bridge Role" if the current match is too low.

3. Monetization & Recommendations
For every identified gap, return a RecommendationPacket:

Tier 1 (Free/AI): Actionable advice via OpenAI/Gemini.

Tier 2 (Passive): Suggested courses (Course Model/DTO).

Tier 3 (High-Ticket): "Expert-on-Demand." Dynamically search for "Expert" users on Torre in that specific skill.

4. Architecture & Craftsmanship (Strict)
Separation of Concerns: TorreClient.php for API calls, DTOs for data handling, and Services for business logic.

Performance: Implement Tagged Caching (Redis) for Torre API responses.

Resiliency: Use Laravel\Http\Client with retries and specific exception handling for MySQL/API connection issues.

Testing: Generate MatchmakingTest.php mocking both the DB and the external APIs.

5. UI/UX (Tailwind & Blade)
Dashboard showing the "Confidence Score".

A "Career Roadmap" visualizer listing the gaps.

Interactive cards for Mentors and Courses.