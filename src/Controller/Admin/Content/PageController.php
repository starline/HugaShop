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
use HugaShop\Models\Request;
use App\Controller\BaseAdminController;
use HugaShop\Models\Content\ContentPage;
use HugaShop\Models\Localization\Language;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends BaseAdminController
{
    #[Route('/admin/page', name: 'PageNewAdmin')]
    #[Route('/admin/page/{id}', requirements: ['id' => '\d+'], name: 'PageAdmin')]
    public function index(?int $id): Response
    {

        $this->checkAdminAccess('page');

        // Init content language
        Language::languageCatch();

        #### Update
        ###########
        if (!empty($page = Request::getDataAcces(ContentPage::getFields()))) {

            // Не допустить одинаковые URL разделов.
            if (($p = ContentPage::getPage($page->url)) && $p->id != $page->id) {
                Design::setFlashMessage('error', 'url_exists');
            } else {
                if (empty($page->id)) {
                    $page = Design::setFlashMessage('add', ContentPage::addPage($page));
                } else {
                    Design::setFlashMessage('update', ContentPage::updatePage($page->id, $page));
                }
            }

            return $this->redirectToRouteLang('PageAdmin', ['id' => $page->id]);
        }


        #### View
        #########
        if (!empty($id)) {
            $page = ContentPage::getPage($id);
            if (empty($page->id)) {
                return $this->redirectToRoute('PostListAdmin');
            }
        }

        Design::assign('page', $page);

        return $this->fetchResponse('content/page.tpl');
    }
}
