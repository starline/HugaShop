<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
 *
 */

namespace HugaShop\Extensions\SeoPage\EventListener;

use HugaShop\Services\Cache;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use App\Event\DesignBeforeFetchEvent;
use HugaShop\Extensions\SeoPage\Models\SeoPage;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class DesignBeforeFetchListener
{
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

        // $_SERVER['REQUEST_URI'];
        // Example: /info/pravila+кирилица
        $page_uri = urldecode($_SERVER['REQUEST_URI']);

        $cache_item = Cache::cache(self::class)->getItem('item_' . Helper::makeToken($page_uri));
        if (!$cache_item->isHit()) {

            // SEO page
            // Protect for infinity fake pages. Don't make cache
            if (empty($seo = SeoPage::getOne(['enabled' => 1, 'url' =>  $page_uri]))) {
                return;
            }

            Cache::cache(self::class)->save($cache_item->set($seo));
        }

        $seo = $cache_item->get();
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
