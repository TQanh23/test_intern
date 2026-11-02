<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStudent
{
    /**
     * Chặn admin khỏi phần làm bài, để điểm của admin không lọt vào thống kê. Luôn đặt sau middleware `auth`.
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()->isStudent(), 403);

        return $next($request);
    }
}
