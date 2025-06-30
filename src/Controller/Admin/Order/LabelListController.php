<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace App\Controller\Admin\Order;

use HugaShop\Services\Design;
use HugaShop\Models\Helper;
use HugaShop\Models\Request;
use HugaShop\Models\Order\OrderLabel;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LabelListController extends BaseAdminController
{
    #[Route('/admin/order/labels', name: 'LabelListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('order_label');

        // Обработка действий
        if (Request::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'delete': {
                            foreach ($ids as $id) {

                                // TODO: Проверить, связана ли метка с заказами

                                OrderLabel::deleteLabel(intval($id));
                            }
                        }
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                OrderLabel::updateOne($id, ['position' => $position]);
            }
        }

        $labels = OrderLabel::getLabels();
        Design::assign('labels', $labels);

        // Отображение
        return $this->fetchResponse('order/label_list.tpl');
    }
}
