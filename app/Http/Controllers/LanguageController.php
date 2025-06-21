<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Change the application language.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchLang(Request $request, $locale)
    {
        // Validate locale against available locales
        if (!in_array($locale, ['en', 'vi'])) {
            $locale = 'en';
        }
        
        // Store the locale in a cookie that lasts for a year
        $cookie = cookie('locale', $locale, 60*24*365);
        
        // Store the locale in the session
        Session::put('locale', $locale);
        
        // Set the application locale
        App::setLocale($locale);
        
        // Redirect back with the cookie
        return redirect()->back()->withCookie($cookie);
    }
}
