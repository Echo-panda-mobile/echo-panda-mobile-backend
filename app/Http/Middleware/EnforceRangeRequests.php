<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceRangeRequests
{
    /**
     * Require Range header for GET stream requests to discourage full-file download requests.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && ! $request->headers->has('Range')) {
            return response()->json([
                'message' => 'Range header is required for streaming.',
            ], 416);
        }

        return $next($request);
    }
}
