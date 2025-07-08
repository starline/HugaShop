<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace HugaShop\Extensions\SeoPage\Controller;

use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use App\Services\LanguageService;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\SeoPage\Models\SeoPage;
use HugaShop\Extensions\BaseExtensionController;

final class SeoPageController extends BaseExtensionController
{

    /**
     * Список странниц
     */
    #[Route('/SeoPage', name: 'ExtSeoPageList', priority: 20)]
    public function index()
    {

        $this->checkAdminAccess('extension');

        // Обработка действий
        if (Request::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'disable': {
                            SeoPage::updateOne($ids, ['enabled' => 0]);
                            break;
                        }
                    case 'enable': {
                            SeoPage::updateOne($ids, ['enabled' => 1]);
                            break;
                        }
                    case 'delete': {
                            foreach ($ids as $id) {
                                SeoPage::deleteOne($id);
                            }
                            break;
                        }
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                SeoPage::updateOne($id, ['position' => $position]);
            }

            SeoPage::cacheClear();
        }

        $pages = SeoPage::getList(order: 'position');
        Design::assign('pages', $pages);

        return $this->fetchResponse(Config::get('extension_dir') . 'SeoPage/templates/page_list.tpl');
    }


    /**
     * SEO Page
     * @param ?int $page_id
     */
    #[Route('/SeoPage/page', name: 'ExtSeoPageNew', priority: 20)]
    #[Route('/SeoPage/page/{id}', requirements: ['id' => '\d+'], name: 'ExtSeoPage', priority: 20)]
    public function page(?int $id = null)
    {

        $this->checkAdminAccess('extension');

        // Init content language
        LanguageService::languageCatch();

        #### Update
        ###########
        if (!empty($page = Request::getDataAcces(SeoPage::getFields()))) {

            // Не допустить одинаковые URL разделов.
            if (($p = SeoPage::getOne(['url' => $page->url])) && $p->id != $page->id) {
                Design::setFlashMessage('error', 'url_exists');
            } else {
                if (empty($page->id)) {
                    $page = Design::setFlashMessage('add', SeoPage::createOne($page));
                } else {
                    Design::setFlashMessage('update', SeoPage::updateOne($page->id, $page));
                    SeoPage::cacheClear();
                }
            }

            return $this->redirectToRouteLang('ExtSeoPage', ['id' => $page->id]);
        }


        #### View
        #########
        if (!empty($id)) {
            $page = SeoPage::getOneEditTranslate($id);
            if (empty($page->id)) {
                return $this->redirectToRouteLang('ExtSeoPageList');
            }
        }

        Design::assign('page', $page);

        return $this->fetchResponse(Config::get('extension_dir') . 'SeoPage/templates/page.tpl');
    }
}
