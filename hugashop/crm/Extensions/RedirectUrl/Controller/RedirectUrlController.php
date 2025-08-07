<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.9
 *
 */

namespace HugaShop\Extensions\RedirectUrl\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\RedirectUrl\Models\RedirectUrl;

final class RedirectUrlController extends BaseAdminController
{
    use BaseExtensionTrait;

    /**
     * Link edit
     */
    #[Route('/RedirectUrl/link', name: 'ExtRedirectUrlNew', priority: 20)]
    #[Route('/RedirectUrl/link/{id}', name: 'ExtRedirectUrl', priority: 20)]
    public function link(?int $id = null)
    {
        #### Update
        ###########
        if (!empty($link = Secure::getInputCheckEditAccess(RedirectUrl::class, $id))) {
            if (empty($link->id)) {
                $link = Design::setFlashMessage('add', RedirectUrl::createOne($link));
            } else {
                Design::setFlashMessage('update', RedirectUrl::updateOne($link->id, $link));
            }

            RedirectUrl::cacheClear();
            return $this->redirectToRoute('ExtRedirectUrl', ['id' => $link->id]);
        }

        #### View
        #########
        if (!empty($id)) {
            $link = RedirectUrl::getOne($id);
            if (empty($link->id)) {
                return $this->redirectToRoute('ExtRedirectUrlList');
            }
        }

        Design::assign('link',      $link);
        Design::assign('extension', $this->getExtension());

        return $this->fetchExtResponse('link.tpl');
    }
}
