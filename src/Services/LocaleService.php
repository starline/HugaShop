<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 *
 * Service for detecting locale from request path
 */

namespace App\Services;

use HugaShop\Services\Request;
use HugaShop\Models\Localization\Language;
use Symfony\Component\HttpFoundation\Request as HttpRequest;

class LocaleService
{


    /**
     * Prepare request path and set locale
     */
    public static function prepare(HttpRequest $request): void
    {
        // Получаем исходный путь БЕЗ использования getPathInfo()
        $raw_path = parse_url($request->server->get('REQUEST_URI') ?? '/', PHP_URL_PATH);
        $segments = array_values(array_filter(explode('/', trim($raw_path, '/'))));

        $main    = Language::getMain();                 # Главная локаль (например: 'en')
        $prefix  = $segments[0] ?? null;                # Первый сегмент пути

        // Если путь начинается с валидной локали
        if ($prefix && Language::isLanguage($prefix)) {

            // Если это дефолтная локаль — редиректим на путь без неё
            if ($prefix === $main->code) {
                array_shift($segments); # удаляем локаль
                $redirect = '/' . implode('/', $segments);
                $redirect = $redirect === '' ? '/' : '/' . rtrim($redirect, '/');

                $url = $request->getSchemeAndHttpHost() . $redirect;
                Request::makeRedirect($url);
            }

            // Устанавливаем текущую локаль
            Language::getCurrent($prefix);

            // Убираем локаль из пути
            array_shift($segments);
            $new_path = implode('/', $segments);
            $new_path = $new_path === '' ? '/' : '/' . rtrim($new_path, '/');

            // Меняем PATH_INFO и REQUEST_URI
            $request->server->set('PATH_INFO', $new_path);
            $request->server->set('REQUEST_URI', $new_path);

            return;
        }
    }


    /**
     * Return detected locale code
     */
    public static function detect(): string
    {
        return Language::checkOrGetCode() ?: Language::getMain()->code;
    }
}
