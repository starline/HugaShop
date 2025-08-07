<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
 *
 */

namespace App\Controller\Admin\Content;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use App\Services\LanguageService;
use App\Controller\BaseAdminController;
use HugaShop\Models\Content\ContentPage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends BaseAdminController
{
    #[Route('/admin/page', name: 'PageNewAdmin')]
    #[Route('/admin/page/{id}', requirements: ['id' => '\d+'], name: 'PageAdmin')]
    public function index(?int $id = null): Response
    {

        $this->checkAdminAccess('page');

        // Init content language
        LanguageService::languageCatch();


        #### Update
        ###########
        if (!empty($page = Secure::getInputCheckEditAccess(ContentPage::class, $id))) {
            if (empty($page->id)) {
                $page = Design::setFlashMessage('add', ContentPage::addPage($page));
            } else {
                Design::setFlashMessage('update', ContentPage::updatePage($page->id, $page));
            }

            return $this->redirectToRouteLang('PageAdmin', ['id' => $page->id]);
        }


        #### View
        #########
        if (!empty($id)) {
            $page = ContentPage::getOneEditTranslate($id);
            if (empty($page->id)) {
                return $this->redirectToRoute('PostListAdmin');
            }
        }


        Design::assign('page', $page);
        return $this->fetchResponse('content/page.tpl');
    }
}
