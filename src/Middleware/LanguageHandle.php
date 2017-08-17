<?php

namespace Bavix\Middleware;

use Illuminate\Http\Request;

class LanguageHandle
{

    public function handle(Request $request, \Closure $next)
    {
        $locales   = config('locales', ['en', 'ru']);
        $preferred = $request->getPreferredLanguage($locales);
        $locale    = bx_cookie('locale', $preferred);

        app()->setLocale($locale);

        return $next($request);
    }

}
