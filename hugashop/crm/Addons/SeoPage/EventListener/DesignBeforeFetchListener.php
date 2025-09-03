<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.6
 *
 */

namespace HugaShop\Addons\SeoPage\EventListener;

use HugaShop\Services\Cache;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use App\Event\DesignBeforeFetchEvent;
use HugaShop\Models\Localization\Language;
use HugaShop\Addons\SeoPage\Models\SeoPage;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;

class DesignBeforeFetchListener
{

    /**
     * Symfony request stack
     */
    private RequestStack $request_stack;

    public function __construct(RequestStack $request_stack)
    {
        $this->request_stack = $request_stack;
    }

    /**
     * Order Add
     * @param DesignBeforeFetchEvent $event
     */
    #[AsEventListener]
    public function onDesignBeforeFetchEvent(DesignBeforeFetchEvent $event): void
    {
        if (Request::isAjax() || Design::getTheme() === 'admin') {
            return;
        }

        $request = $this->request_stack->getCurrentRequest();
        if ($request === null) {
            return;
        }

        // Request URI
        // Example: /info/pravila+кирилица
        // Example: /uk/info/pravila+кирилица
        $page_uri = urldecode((string) $request->server->get('REQUEST_URI'));
        $lang = Language::getCurrent()->code;

        $cache_item = Cache::cache(SeoPage::class)->getItem('item_' . Helper::makeToken($page_uri) . '_' . $lang);
        if (!$cache_item->isHit()) {

            // SEO page
            // Protect for infinity fake pages. Don't make cache
            if (empty($seo = SeoPage::getOneTranslate(['enabled' => 1, 'url' =>  $page_uri]))) {
                return;
            }

            Cache::cache(SeoPage::class)->save($cache_item->set($seo));
        } else {
            $seo = $cache_item->get();
        }

        Design::assign('seo', $seo);

        // H1
        if (!empty($seo->h1)) {
            Design::assign('h1', $seo->h1);
        }

        // Задаем meta-теги из SEO страницы
        if (!empty($seo->meta_title)) {
            Design::assign('meta_title', $seo->meta_title);
        }

        if (!empty($seo->meta_description)) {
            Design::assign('meta_description', $seo->meta_description);
        }
    }
}
