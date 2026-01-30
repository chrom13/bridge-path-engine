<?php

namespace App\Http\Controllers;

use App\Services\TorreApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /**
     * Torre API Service
     */
    private TorreApiService $torreApi;

    public function __construct(TorreApiService $torreApi)
    {
        $this->torreApi = $torreApi;
    }

    /**
     * Show the login page
     */
    public function showLogin()
    {
        // If already logged in, redirect to home
        if (Session::has('user')) {
            return redirect()->route('home');
        }

        return view('auth.login');
    }

    /**
     * Handle login submission with Torre API authentication
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string|min:3',
        ]);

        $username = strtolower(trim($request->username));

        // Check if user genome is cached
        $isCached = $this->torreApi->hasCachedGenome($username);

        // Fetch user genome from Torre API (will use cache if available)
        $userGenome = $this->torreApi->getUserGenome($username);

        if (!$userGenome) {
            return back()
                ->withInput()
                ->withErrors(['username' => 'User not found on Torre. Please check the username and try again.']);
        }

        // Extract user information from genome
        $userData = [
            'username' => $userGenome['username'] ?? $username,
            'name' => $userGenome['name'] ?? ucfirst($username),
            'professional_headline' => $userGenome['professionalHeadline'] ?? null,
            'picture' => $userGenome['picture'] ?? null,
            'location' => $this->extractLocation($userGenome),
            'genome_data' => $userGenome, // Store full genome for later use
            'logged_in_at' => now(),
            'from_cache' => $isCached,
        ];

        // Store user data in session
        Session::put('user', $userData);

        $welcomeMessage = $isCached
            ? "Welcome back, {$userData['name']}! (Data from cache)"
            : "Welcome, {$userData['name']}! Your profile has been loaded from Torre.";

        return redirect()
            ->route('home')
            ->with('success', $welcomeMessage);
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        Session::forget('user');
        return redirect()
            ->route('login')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Extract location from genome data
     *
     * @param array $genome
     * @return string|null
     */
    private function extractLocation(array $genome): ?string
    {
        if (isset($genome['location'])) {
            $location = $genome['location'];

            if (is_string($location)) {
                return $location;
            }

            if (is_array($location)) {
                $parts = [];
                if (isset($location['name'])) {
                    $parts[] = $location['name'];
                }
                if (isset($location['country']) && $location['country'] !== ($location['name'] ?? null)) {
                    $parts[] = $location['country'];
                }
                return !empty($parts) ? implode(', ', $parts) : null;
            }
        }

        return null;
    }
}
