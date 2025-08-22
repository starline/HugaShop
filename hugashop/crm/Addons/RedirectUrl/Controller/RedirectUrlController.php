<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
 *
 */

namespace HugaShop\Addons\RedirectUrl\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\RedirectUrl\Models\RedirectUrl;

final class RedirectUrlController extends BaseAdminController
{
    use BaseAddonTrait;

    /**
     * Link edit
     */
    #[Route('/RedirectUrl/link', name: 'AddonRedirectUrlNew', priority: 20)]
    #[Route('/RedirectUrl/link/{id}', name: 'AddonRedirectUrl', priority: 20)]
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
            return $this->redirectToRoute('AddonRedirectUrl', ['id' => $link->id]);
        }

        #### View
        #########
        if (!empty($id)) {
            $link = RedirectUrl::getOne($id);
            if (empty($link->id)) {
                return $this->redirectToRoute('AddonRedirectUrlList');
            }
        }

        Design::assign('link',      $link);
        Design::assign('addon', $this->getAddon());

        return $this->fetchAddonResponse('link.tpl');
    }
}
