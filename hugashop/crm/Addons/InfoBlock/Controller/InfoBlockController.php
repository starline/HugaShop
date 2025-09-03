<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.7
 *
 */

namespace HugaShop\Addons\InfoBlock\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use App\Services\LanguageService;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\InfoBlock\Models\InfoBlock;

final class InfoBlockController extends BaseAdminController
{

    use BaseAddonTrait;

    /**
     * Список страниц
     */
    #[Route('/InfoBlock/block', name: 'AddonInfoBlockNew', priority: 20)]
    #[Route('/InfoBlock/block/{id}', name: 'AddonInfoBlock', priority: 20)]
    public function block(?int $id = null)
    {

        // Init content language
        LanguageService::languageCatch();

        #### Update
        ###########
        if (!empty($block = Secure::getInputCheckEditAccess(InfoBlock::class, $id))) {
            if (empty($block->id)) {
                $block = Design::setFlashMessage('add', InfoBlock::createOne($block));
            } else {
                Design::setFlashMessage('update', InfoBlock::updateOne($block->id, $block));
            }

            InfoBlock::cacheClear();
            return $this->redirectToRouteLang('AddonInfoBlock', ['id' => $block->id]);
        }


        #### View
        #########
        if (!empty($id)) {
            $block = InfoBlock::getOneEditTranslate($id);
            if (empty($block->id)) {
                return $this->redirectToRoute('AddonInfoBlockList');
            }
        }

        Design::assign('addon', $this->getAddon());
        Design::assign('block', $block);

        return $this->fetchAddonResponse('block.tpl');
    }
}
