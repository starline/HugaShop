<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 */

namespace App\Controller\Admin\Content;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Models\Content\ContentPage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageListController extends BaseAdminController
{

    #[Route('/admin/pages', name: 'PageListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('page');

        // Обработка действий
        if (Request::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'disable': {
                            ContentPage::updatePage($ids, ['visible' => 0]);
                            break;
                        }
                    case 'enable': {
                            ContentPage::updatePage($ids, ['visible' => 1]);
                            break;
                        }
                    case 'delete': {
                            foreach ($ids as $id) {
                                ContentPage::deletePage($id);
                            }
                            break;
                        }
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                ContentPage::updatePage($id, ['position' => $position]);
            }

            ContentPage::clearCache(); # Cache clean
        }

        // Отображение
        $pages = ContentPage::getList(order: 'position');
        Design::assign('pages', $pages);

        return $this->fetchResponse('content/page_list.tpl');
    }
}
