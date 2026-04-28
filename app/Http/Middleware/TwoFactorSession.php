<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('2fa_user_id')) {
            return redirect()->route('login')
                ->with('error', 'Sesi tidak valid. Silakan login ulang.');
        }

        return $next($request);
    }
}