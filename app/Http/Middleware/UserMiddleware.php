<?php

namespace App\Http\Middleware;

use App\Http\Controllers\BaseController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware extends BaseController
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth('user')->check()) {
            return $next($request);
        } else {
            return $this->sendResponse(false, [], "You are not user role", null, 400, $request);
        }
    }
}
