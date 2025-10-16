<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokenNotExpired
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();
        if ($token && $token->expires_at) {
            $now = now();
            // If expired, revoke and reject
            if ($now->greaterThanOrEqualTo($token->expires_at)) {
                try { $token->delete(); } catch (\Throwable) {}
                return response()->json(['message' => 'Token expired'], 401);
            }

            // Sliding refresh: if remaining lifetime is <= renew_before, extend to now + TTL
            $renewBefore = (int) config('token-auth.token_renew_before_minutes', 15);
            $ttl = (int) config('token-auth.token_ttl_minutes', 30);
            if ($renewBefore > 0 && $ttl > 0) {
                if ($token->expires_at->lessThanOrEqualTo($now->copy()->addMinutes($renewBefore))) {
                    // Only touch DB when within the sliding window
                    try {
                        $token->forceFill(['expires_at' => $now->copy()->addMinutes($ttl)])->save();
                    } catch (\Throwable) {
                        // best-effort; if it fails, continue without extending
                    }
                }
            }
        }

        return $next($request);
    }
}
