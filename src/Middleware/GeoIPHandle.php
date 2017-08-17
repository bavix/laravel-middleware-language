<?php

namespace Bavix\Middleware;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class GeoIPHandle extends LanguageHandle
{

    /**
     * @param string $ip
     *
     * @return array|null
     */
    protected function freeGeoIP($ip)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://freegeoip.net/json/' . $ip);
        curl_setopt($ch, CURLOPT_ACCEPT_ENCODING, 'UTF-8');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return $code > 199 && $code < 300 ?
            json_decode($response, true) :
            null;
    }

    /**
     * @param $ip
     *
     * @return string
     */
    protected function detection($ip)
    {
        $locales   = config('locales', ['en', 'ru']);
        $detection = $this->freeGeoIP($ip);

        if (!empty($detection['country_code']))
        {
            $code = strtolower($detection['country_code']);

            if (in_array($code, $locales, true))
            {
                return $code;
            }
        }

        return $this->request()
            ->getPreferredLanguage($locales);
    }

    /**
     * @return string
     */
    protected function getLanguage()
    {
        $ip = $this->request()->ip();
        $key = static::class . '\\locale' . $ip;

        if (!Cache::has($key))
        {
            $locale = $this->detection($ip);

            Cache::put(
                $key,
                $locale,
                Carbon::now()->addMonth()
            );

            return $locale;
        }

        return Cache::get($key);
    }

}
