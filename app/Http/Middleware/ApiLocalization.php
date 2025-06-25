<?php

namespace App\Http\Middleware;

use App\Http\Controllers\BaseController;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiLocalization extends BaseController
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('Accept-Language') ?? null;

        if (!$locale) {
            $locale = config('app.locale');
        } else {
            // Parse the Accept-Language header to get the primary locale
            $locales = explode(',', $locale);
            $locale = trim(explode(';', $locales[0])[0]);
        }

        // Validate if the locale is supported
        if (!array_key_exists($locale, config('app.supported_languages')?? ['ar', 'en'])) {
            $locale = config('app.locale');
        }

        app()->setLocale($locale);

        $response = $next($request);

        $response->headers->set('Accept-Language', $locale);

        return $response;
    }
}
