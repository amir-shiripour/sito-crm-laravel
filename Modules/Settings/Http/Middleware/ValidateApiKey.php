<?php

namespace Modules\Settings\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Settings\Entities\ApiKey;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $module
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $module = null)
    {
        $token = $request->bearerToken() ?: $request->query('api_key') ?: $request->query('api');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'API token is missing.'
            ], 401);
        }

        // Find the API Key
        $apiKey = ApiKey::where('key', $token)->first();

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API token.'
            ], 401);
        }

        // Check if active and not expired
        if (!$apiKey->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'API token is inactive or expired.'
            ], 401);
        }

        // Check module restriction if specified in middleware parameters
        if ($module && strtolower($apiKey->module) !== strtolower($module)) {
            return response()->json([
                'success' => false,
                'message' => 'This API token is not authorized for the requested module.'
            ], 403);
        }

        // Check Rate Limiting
        if ($apiKey->rate_limit_per_hour !== null) {
            $cacheKey = 'api_rate_limit:' . $apiKey->id . ':' . date('Y-m-d-H');
            
            $currentUsage = Cache::get($cacheKey, 0);

            if ($currentUsage >= $apiKey->rate_limit_per_hour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rate limit exceeded. Maximum ' . $apiKey->rate_limit_per_hour . ' requests per hour allowed.'
                ], 429);
            }

            // Increment usage
            if (!Cache::has($cacheKey)) {
                Cache::put($cacheKey, 1, 3600);
            } else {
                Cache::increment($cacheKey);
            }
        }

        // Record usage in DB
        $apiKey->recordUsage();

        // Attach key to request for controller access
        $request->merge(['authenticated_api_key' => $apiKey]);

        return $next($request);
    }
}
