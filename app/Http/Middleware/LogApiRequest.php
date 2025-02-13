<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequest
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle($request, Closure $next)
  {
    try {
      // Log request details
      Log::info('API Request', [
        'timestamp' => now()->toDateTimeString(), // Current time
        'endpoint' => $request->path(), // URL being accessed
        'method' => $request->method(), // HTTP method (GET, POST, etc.)
        'user_id' => optional($request->user())->id, // User ID (if logged in)
        'ip' => $request->ip(), // User's IP address
        'headers' => $request->headers->all(), // Request headers
        'body' => $request->except(['password', 'password_confirmation']), // Request body (excluding sensitive info)
      ]);
    } catch (\Exception $e) {
      Log::error('Failed to log API request', ['error' => $e->getMessage()]);
    }

    // Process the request and get the response
    $response = $next($request);

    try {
      // Log response details
      Log::channel('api')->info('API Response', [
        'timestamp' => now()->toDateTimeString(), // Current time
        'status' => $response->getStatusCode(), // HTTP status code (200, 404, etc.)
        'user_id' => optional($request->user())->id, // User ID (if logged in)
        'response' => json_decode($response->getContent(), true), // Response content
      ]);
    } catch (\Exception $e) {
      Log::error('Failed to log API response', ['error' => $e->getMessage()]);
    }

    return $response;
  }
}
