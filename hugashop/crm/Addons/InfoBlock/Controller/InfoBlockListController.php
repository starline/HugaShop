<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
 *
 */

namespace HugaShop\Addons\InfoBlock\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\InfoBlock\Models\InfoBlock;

final class InfoBlockListController extends BaseAdminController
{

    use BaseAddonTrait;

    /**
     * Список страниц
     */
    #[Route('/InfoBlock', name: 'AddonInfoBlockList', priority: 20)]
    public function block()
    {

        // Обработка действий
        if (Secure::checkCSRF()) {

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

        Design::assign('addon',     $this->getAddon());
        Design::assign('blocks',    InfoBlock::getList(order: 'position'));

        return $this->fetchAddonResponse('block_list.tpl');
    }
}
