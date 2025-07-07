<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.9
 *
 */

namespace HugaShop\Extensions\SeoPage;

use HugaShop\Services\Cache;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use App\Event\DesignBeforeFetchEvent;
use HugaShop\Extensions\BaseExtension;
use HugaShop\Extensions\SeoPage\Models\SeoPage as SeoPageModel;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class SeoPage extends BaseExtension
{

    /**
     * Список странниц
     */
    public function index()
    {

        // Обработка действий
        if (Request::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'disable': {
                            SeoPageModel::updateOne($ids, ['enabled' => 0]);
                            break;
                        }
                    case 'enable': {
                            SeoPageModel::updateOne($ids, ['enabled' => 1]);
                            break;
                        }
                    case 'delete': {
                            foreach ($ids as $id) {
                                SeoPageModel::deleteOne($id);
                            }
                            break;
                        }
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                SeoPageModel::updateOne($id, ['position' => $position]);
            }

            SeoPageModel::clearCache();
        }

        $pages = SeoPageModel::getList(order: 'position');
        Design::assign('pages', $pages);

        return $this->getTemplatePath('templates/page_list.tpl');
    }


    /**
     * SEO Page
     * @param ?int $page_id
     */
    public function page(?int $id = null)
    {

        #### Update
        ###########
        if (!empty($page = Request::getDataAcces(SeoPageModel::getFields()))) {

            // Не допустить одинаковые URL разделов.
            if (($p = SeoPageModel::getOne(['url' => $page->url])) && $p->id != $page->id) {
                Design::setFlashMessage('error', 'url_exists');
            } else {
                if (empty($page->id)) {
                    $page = Design::setFlashMessage('add', SeoPageModel::createOne($page));
                } else {
                    Design::setFlashMessage('update', SeoPageModel::updateOne($page->id, $page));
                    SeoPageModel::clearCache();
                }
            }

            Request::makeRedirect("/admin/extension/SeoPage/page/$page->id");
        }


        #### View
        #########
        if (!empty($id)) {
            $page = SeoPageModel::getOne($id);
            if (empty($page->id)) {
                Request::makeRedirect("/admin/extension/SeoPage");
            }
        }

        Design::assign('page', $page);

        return $this->getTemplatePath('templates/page.tpl');
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

        // $_SERVER['REQUEST_URI'];
        // Example: /info/pravila+кирилица
        $page_uri = urldecode($_SERVER['REQUEST_URI']);

        $cache_item = Cache::cache(self::class)->getItem('item_' . Helper::makeToken($page_uri));
        if (!$cache_item->isHit()) {

            // SEO page
            // Protect for infinity fake pages. Don't make cache
            if (empty($seo = SeoPageModel::getOne(['enabled' => 1, 'url' =>  $page_uri]))) {
                return;
            }

            Cache::cache(self::class)->save($cache_item->set($seo));
        }

        $seo = $cache_item->get();
        Design::assign('seo', $seo);

        // Задаем meta-теги из SEO страницы
        if (!empty($seo->meta_title)) {
            Design::assign('meta_title', $seo->meta_title);
        }

        if (!empty($seo->meta_description)) {
            Design::assign('meta_description', $seo->meta_description);
        }
    }
}
