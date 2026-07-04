<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckKasirRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->role === 'kasir') {
            return redirect()->route('orders.index')->with('error', 'Anda tidak memiliki hak akses ke halaman ini.');
        }

        return $next($request);
    }
}
