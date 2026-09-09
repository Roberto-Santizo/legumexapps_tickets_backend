<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Errors\UnauthorizedError;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user->role !== UserRole::ADMIN) {
            throw new UnauthorizedError('No autorizado');
        }

        return $next($request);
    }
}
