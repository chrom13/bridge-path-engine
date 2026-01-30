<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TorreApiService
{
    /**
     * Base URL for Torre API
     */
    private string $baseUrl;

    /**
     * API timeout in seconds
     */
    private int $timeout;

    /**
     * Number of retry attempts
     */
    private int $retryTimes;

    /**
     * Sleep time between retries in milliseconds
     */
    private int $retrySleep;

    /**
     * Cache TTL in seconds (24 hours)
     */
    private int $cacheTtl;

    public function __construct()
    {
        $this->baseUrl = config('services.torre.api_url', 'https://torre.ai/api');
        $this->timeout = config('services.torre.timeout', 30);
        $this->retryTimes = config('services.torre.retry_times', 3);
        $this->retrySleep = config('services.torre.retry_sleep', 1000);
        $this->cacheTtl = config('services.torre.cache_ttl', 86400); // 24 hours
    }

    /**
     * Get user genome/bio by username
     * Implements caching with 24-hour TTL
     *
     * @param string $username
     * @param bool $forceRefresh Force refresh from API, bypass cache
     * @return array|null
     */
    public function getUserGenome(string $username, bool $forceRefresh = false): ?array
    {
        $cacheKey = $this->getCacheKey('genome', $username);

        // Check cache first unless force refresh is requested
        if (!$forceRefresh && Cache::has($cacheKey)) {
            Log::info("Torre API: Retrieved user genome from cache", ['username' => $username]);
            return Cache::get($cacheKey);
        }

        // Fetch from API
        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->retryTimes, $this->retrySleep)
                ->get("{$this->baseUrl}/genome/bios/{$username}");

            if ($response->successful()) {
                $data = $response->json();

                // Cache the response for 24 hours
                Cache::put($cacheKey, $data, $this->cacheTtl);

                Log::info("Torre API: Fetched and cached user genome", [
                    'username' => $username,
                    'ttl' => $this->cacheTtl
                ]);

                return $data;
            }

            if ($response->status() === 404) {
                Log::warning("Torre API: User not found", ['username' => $username]);
                return null;
            }

            Log::error("Torre API: Failed to fetch user genome", [
                'username' => $username,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error("Torre API: Exception while fetching user genome", [
                'username' => $username,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Search for opportunities using Torre search API
     *
     * @param string|null $searchTerm Search term/keyword
     * @param int $limit Number of results to return
     * @param int $offset Offset for pagination
     * @return array|null
     */
    public function searchOpportunities(?string $searchTerm = null, int $limit = 20, int $offset = 0): ?array
    {
        // Build cache key based on search parameters
        $cacheKey = $this->getCacheKey(
            'search',
            md5(($searchTerm ?? 'all') . "-{$limit}-{$offset}")
        );

        // Check cache first (shorter TTL for search results - 1 hour)
        if (Cache::has($cacheKey)) {
            Log::info("Torre API: Retrieved search results from cache", [
                'term' => $searchTerm,
                'limit' => $limit,
                'offset' => $offset
            ]);
            return Cache::get($cacheKey);
        }

        try {
            // Build search payload according to Torre API spec
            $payload = $this->buildSearchPayload($searchTerm, $limit, $offset);

            // Use the correct search endpoint
            $searchUrl = 'https://search.torre.co/opportunities/_search';

            $response = Http::timeout($this->timeout)
                ->retry($this->retryTimes, $this->retrySleep)
                ->post($searchUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();

                // Cache search results for 1 hour
                Cache::put($cacheKey, $data, 3600);

                Log::info("Torre API: Search completed successfully", [
                    'term' => $searchTerm,
                    'results' => count($data['results'] ?? []),
                    'limit' => $limit,
                    'offset' => $offset
                ]);

                return $data;
            }

            Log::error("Torre API: Failed to search opportunities", [
                'status' => $response->status(),
                'term' => $searchTerm,
                'body' => $response->body()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error("Torre API: Exception while searching opportunities", [
                'error' => $e->getMessage(),
                'term' => $searchTerm
            ]);

            return null;
        }
    }

    /**
     * Build search payload for Torre API
     *
     * @param string|null $searchTerm
     * @param int $limit
     * @param int $offset
     * @return array
     */
    private function buildSearchPayload(?string $searchTerm, int $limit, int $offset): array
    {
        $payload = [
            'size' => $limit,
            'offset' => $offset,
        ];

        // Add search criteria if term provided
        if (!empty($searchTerm)) {
            $payload['and'] = [
                [
                    'keywords' => [
                        'term' => $searchTerm,
                        'locale' => 'en'
                    ]
                ]
            ];
        }

        return $payload;
    }

    /**
     * Get opportunity details by ID
     *
     * @param string $opportunityId
     * @return array|null
     */
    public function getOpportunity(string $opportunityId): ?array
    {
        $cacheKey = $this->getCacheKey('opportunity', $opportunityId);

        // Check cache first
        if (Cache::has($cacheKey)) {
            Log::info("Torre API: Retrieved opportunity from cache", ['id' => $opportunityId]);
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->retryTimes, $this->retrySleep)
                ->get("{$this->baseUrl}/opportunities/{$opportunityId}");

            if ($response->successful()) {
                $data = $response->json();

                // Cache for 1 hour
                Cache::put($cacheKey, $data, 3600);

                return $data;
            }

            Log::error("Torre API: Failed to fetch opportunity", [
                'id' => $opportunityId,
                'status' => $response->status()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error("Torre API: Exception while fetching opportunity", [
                'id' => $opportunityId,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Clear cached data for a user
     *
     * @param string $username
     * @return void
     */
    public function clearUserCache(string $username): void
    {
        $cacheKey = $this->getCacheKey('genome', $username);
        Cache::forget($cacheKey);
        Log::info("Torre API: Cleared user cache", ['username' => $username]);
    }

    /**
     * Generate cache key
     *
     * @param string $type
     * @param string $identifier
     * @return string
     */
    private function getCacheKey(string $type, string $identifier): string
    {
        return "torre:api:{$type}:{$identifier}";
    }

    /**
     * Check if cache exists for user
     *
     * @param string $username
     * @return bool
     */
    public function hasCachedGenome(string $username): bool
    {
        return Cache::has($this->getCacheKey('genome', $username));
    }

    /**
     * Get cache TTL remaining for user
     *
     * @param string $username
     * @return int|null Seconds remaining, or null if not cached
     */
    public function getCacheTtlRemaining(string $username): ?int
    {
        $cacheKey = $this->getCacheKey('genome', $username);

        if (!Cache::has($cacheKey)) {
            return null;
        }

        // Note: Laravel doesn't provide a direct way to get TTL remaining
        // This is a limitation, we just return that it exists
        return $this->cacheTtl;
    }
}
