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

class LanguageController extends BaseAdminController
{

    #[Route('/admin//language', name: 'LanguageNewAdmin')]
    #[Route('/admin//language/{id}', requirements: ['id' => '\d+'], name: 'LanguageAdmin')]
    public function index(?int $id = null): Response
    {

        $this->checkAdminAccess('settings');

        #### Update
        ###########
        if (!empty($language = Request::getDataAcces(Language::getFields()))) {

            if (empty($language->id)) {
                $language = Design::setFlashMessage('add', Language::create($language));
            } else {
                Design::setFlashMessage('update', Language::updateOne($language->id, $language));
            }

            // Делаем редирект на страницу с ID
            return $this->redirectToRoute('LanguageAdmin', ['id' => $language->id]);
        }


        #### View
        #########
        if (!empty($id)) {
            $language = Language::getOne($id);

            if (empty($language->id)) {
                return $this->redirectToRoute('LanguageListAdmin');
            }
        }

        Design::assign('language', $language);

        return $this->fetchResponse('product/language.tpl');
    }
}
