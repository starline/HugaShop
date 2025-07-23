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
     * Список страниц
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
                            InfoBlock::updateList($ids, ['enabled' => 0]);
                            break;
                        }
                    case 'enable': {
                            InfoBlock::updateList($ids, ['enabled' => 1]);
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

        Design::assign('blocks',  InfoBlock::getList(order: 'position'));

        return $this->fetchExtResponse('block_list.tpl');
    }
}
