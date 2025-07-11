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
use HugaShop\Services\Request;
use App\Services\LanguageService;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\SeoPage\Models\SeoPage;


final class SeoPageController extends BaseAdminController
{

    use BaseExtensionTrait;

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
        Design::assign('extension', $this->getExtension());

        return $this->fetchExtResponse('page.tpl');
    }
}
