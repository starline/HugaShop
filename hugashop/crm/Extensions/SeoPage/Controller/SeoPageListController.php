<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 */

namespace HugaShop\Extensions\SeoPage\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\SeoPage\Models\SeoPage;


final class SeoPageListController extends BaseAdminController
{

    use BaseExtensionTrait;

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

        return $this->fetchExtResponse('page_list.tpl');
    }
}
