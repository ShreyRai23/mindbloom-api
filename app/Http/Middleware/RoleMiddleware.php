<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): mixed
    {
        $user = auth('api')->user();
        if (!$user || $user->role !== $role) {
            return response()->json([
                'message' => "Access denied. This section is for {$role} accounts only. 🚫"
            ], 403);
        }
        return $next($request);
    }
}
