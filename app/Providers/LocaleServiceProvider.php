<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

class LocaleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        try {
            // Set the application locale based on the session
            if (Session::has('locale')) {
                $locale = Session::get('locale');
                // Validate the locale
                if ($this->isValidLocale($locale)) {
                    App::setLocale($locale);
                } else {
                    // If invalid, remove it from session and use default
                    Session::forget('locale');
                }
            } 
            // As a fallback, try to check for a cookie
            else if (request()->hasCookie('locale')) {
                $locale = request()->cookie('locale');
                // Validate the locale
                if ($this->isValidLocale($locale)) {
                    Session::put('locale', $locale);
                    App::setLocale($locale);
                }
                // If invalid, we'll use the default locale
            }
        } catch (\Exception $e) {
            // Log the error but continue with the default locale
            // This ensures the application doesn't crash if there's a locale issue
            if (app()->hasDebugModeEnabled()) {
                Log::error('Error setting locale: ' . $e->getMessage());
            }
        }
    }
    
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
}
