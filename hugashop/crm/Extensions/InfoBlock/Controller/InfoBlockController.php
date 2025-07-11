<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
 *
 */

namespace HugaShop\Extensions\InfoBlock\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
use App\Services\LanguageService;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\InfoBlock\Models\InfoBlock;

final class InfoBlockController extends BaseAdminController
{

    use BaseExtensionTrait;

    /**
     * Список странниц
     */
    #[Route('/InfoBlock/block', name: 'ExtInfoBlockNew', priority: 20)]
    #[Route('/InfoBlock/block/{id}', name: 'ExtInfoBlock', priority: 20)]
    public function block(?int $id = null)
    {

        // Init content language
        LanguageService::languageCatch();

        #### Update
        ###########
        if (!empty($block = Request::getDataAcces(InfoBlock::getFields()))) {

            if (empty($block->id)) {
                $block = Design::setFlashMessage('add', InfoBlock::createOne($block));
            } else {
                Design::setFlashMessage('update', InfoBlock::updateOne($block->id, $block));
                InfoBlock::cacheClear();
            }

            return $this->redirectToRouteLang('ExtInfoBlock', ['id' => $block->id]);
        }


        #### View
        #########
        if (!empty($id)) {
            $block = InfoBlock::getOneEditTranslate($id);
            if (empty($block->id)) {
                return $this->redirectToRoute('ExtInfoBlockList');
            }
        }

        Design::assign('block', $block);

        return $this->fetchExtResponse('block.tpl');
    }
}
