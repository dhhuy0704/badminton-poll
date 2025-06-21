<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LanguageMiddleware
{
    /**
     * Check if the provided locale is valid
     * 
     * @param mixed $locale
     * @return bool
     */
    private function isValidLocale($locale): bool
    {
        // Only accept string values
        if (!is_string($locale)) {
            return false;
        }
        
        // Only accept our supported locales
        return in_array($locale, ['en', 'vi']);
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // First check if locale is stored in session
        if (Session::has('locale')) {
            $locale = Session::get('locale');
            // Validate locale
            if ($this->isValidLocale($locale)) {
                App::setLocale($locale);
            } else {
                // If invalid, remove it
                Session::forget('locale');
            }
        } 
        // Next check if locale is stored in cookie
        else if ($request->hasCookie('locale')) {
            $locale = $request->cookie('locale');
            // Validate locale
            if ($this->isValidLocale($locale)) {
                Session::put('locale', $locale);
                App::setLocale($locale);
            }
            // If invalid, we'll use the default locale
        }

        return $next($request);
    }
}
