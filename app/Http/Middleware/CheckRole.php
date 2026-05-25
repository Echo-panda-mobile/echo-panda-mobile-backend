<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $roles = null)
    {
        $user = $request->user();
        if (! $roles) {
            return $next($request);
        }

        $allowed = array_map('trim', explode(',', $roles));

        if (! $user) {
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return response()->json(['message' => 'Authentication required.'], 401);
            }

            abort(401, 'Authentication required.');
        }

        if (! in_array($user->role, $allowed, true)) {
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return response()->json(['message' => 'Insufficient role'], 403);
            }

            abort(403, 'Insufficient role');
        }

        return $next($request);
    }
}
