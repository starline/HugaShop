<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 *
 */

namespace App\Controller\Admin\Product;

use HugaShop\Api\Design;
use HugaShop\Api\Request;
use App\Controller\BaseAdminController;
use HugaShop\Api\Localization\Language;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LanguageListController extends BaseAdminController
{
    #[Route('/admin/languages', name: 'LanguageListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('settings');

        // Обработка действий
        if (Request::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');

            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'delete': {
                            foreach ($ids as $id) {
                                Language::deleteLenguage($id);
                            }
                            break;
                        }
                }
            }
        }

        $languages = Language::getList();
        Design::assign('languages', $languages);

        return $this->fetchResponse('settings/language_list.tpl');
    }
}
