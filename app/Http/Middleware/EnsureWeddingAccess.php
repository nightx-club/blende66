<?php

namespace App\Http\Middleware;

use App\Models\Wedding;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWeddingAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $wedding = $request->route('wedding');
        if (! $wedding instanceof Wedding || ! $wedding->is_active) {
            abort(404);
        }
        return $next($request);
    }
}
