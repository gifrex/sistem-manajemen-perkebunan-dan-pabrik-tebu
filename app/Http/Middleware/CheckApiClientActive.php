<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiClientActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !($user instanceof \App\Models\ApiClient) || !$user->is_active) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akses ditolak. Client tidak aktif atau token tidak valid.'
            ], 403);
        }

        return $next($request);
    }
}