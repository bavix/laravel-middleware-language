<?php

namespace Bavix\Middleware;

use Illuminate\Http\Request;

class LanguageHandle
{

    /**
     * @var bool
     */
    protected $response = false;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @return Request
     */
    protected function request()
    {
        return $this->request ?: \request();
    }

    /**
     * @return string
     */
    protected function getLanguage()
    {
        $locales   = config('locales', ['en', 'ru']);
        $preferred = $this->request()
            ->getPreferredLanguage($locales);

        return bx_cookie('locale', $preferred);
    }

    /**
     * @param \Closure $next
     *
     * @return mixed
     */
    protected function response(\Closure $next)
    {
        if ($this->response)
        {
            $response = $next($this->request());
            $cookies  = cookie()->forever('locale', app()->getLocale());

            return $response->withCookie($cookies);
        }

        return $next($this->request());
    }

    /**
     * @param Request  $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $this->request = $request;
        $locale        = bx_cookie('locale');

        if (null === $locale)
        {
            $this->response = true;
            $locale         = $this->getLanguage();
        }

        app()->setLocale($locale);

        return $this->response($next);
    }

}
