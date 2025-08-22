<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
 *
 */

namespace HugaShop\Addons\SeoPage\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use App\Services\LanguageService;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\SeoPage\Models\SeoPage;


final class SeoPageController extends BaseAdminController
{

    use BaseAddonTrait;

    /**
     * SEO Page
     */
    #[Route('/SeoPage/page', name: 'AddonSeoPageNew', priority: 20)]
    #[Route('/SeoPage/page/{id}', requirements: ['id' => '\d+'], name: 'AddonSeoPage', priority: 20)]
    public function page(?int $id = null)
    {

        $this->checkAdminAccess('addon');

        // Init content language
        LanguageService::languageCatch();

        #### Update
        ###########
        if (!empty($page = Secure::getInputCheckEditAccess(SeoPage::class, $id))) {

            // Не допустить одинаковые URL разделов.
            if (($p = SeoPage::getOne(['url' => $page->url])) && $p->id != $page->id) {
                Design::setFlashMessage('error', 'url_exists');
            } else {
                if (empty($page->id)) {
                    $page = Design::setFlashMessage('add', SeoPage::createOne($page));
                } else {
                    Design::setFlashMessage('update', SeoPage::updateOne($page->id, $page));
                }
            }

            SeoPage::cacheClear();
            return $this->redirectToRouteLang('AddonSeoPage', ['id' => $page->id]);
        }


        #### View
        #########
        if (!empty($id)) {
            $page = SeoPage::getOneEditTranslate($id);
            if (empty($page->id)) {
                return $this->redirectToRouteLang('AddonSeoPageList');
            }
        }

        Design::assign('page', $page);
        Design::assign('addon', $this->getAddon());

        return $this->fetchAddonResponse('page.tpl');
    }
}
