<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 */

namespace HugaShop\Extensions\InfoBlock\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Extensions\InfoBlock\Models\InfoBlock;

final class InfoBlockListController extends BaseAdminController
{

    use BaseExtensionTrait;

    /**
     * Список странниц
     */
    #[Route('/InfoBlock', name: 'ExtInfoBlockList', priority: 20)]
    public function block()
    {

        // Обработка действий
        if (Request::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'disable': {
                            InfoBlock::updateOne($ids, ['enabled' => 0]);
                            break;
                        }
                    case 'enable': {
                            InfoBlock::updateOne($ids, ['enabled' => 1]);
                            break;
                        }
                    case 'delete': {
                            foreach ($ids as $id) {
                                InfoBlock::deleteOne($id);
                            }
                            break;
                        }
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                InfoBlock::updateOne($id, ['position' => $position]);
            }

            InfoBlock::cacheClear();
        }

        $blocks = InfoBlock::getList(order: 'position');

        Design::assign('blocks', $blocks);

        return $this->fetchExtResponse('block_list.tpl');
    }
}
