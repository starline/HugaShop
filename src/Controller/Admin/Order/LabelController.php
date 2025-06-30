<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.0
 *
 */

namespace App\Controller\Admin\Order;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
use HugaShop\Models\Order\OrderLabel;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LabelController extends BaseAdminController
{
    #[Route('/admin/order/label', name: 'LabelNewAdmin')]
    #[Route('/admin/order/label/{id}', requirements: ['id' => '\d+'], name: 'LabelAdmin')]
    public function index(?int $id = null): Response
    {

        $this->checkAdminAccess('order_label');

        #### Update
        ###########
        if (!empty($label = Request::getDataAcces(OrderLabel::getFields()))) {

            if (!empty($label->color)) {
                $label->color = str_replace('#', '', $label->color);
            }

            if (empty($label->id)) {
                $label = Design::setFlashMessage('add', OrderLabel::createOne($label));
            } else {
                Design::setFlashMessage('update', OrderLabel::updateOne($label->id, $label));
            }

            return $this->redirectToRoute('LabelAdmin', ['id' => $label->id]);
        }

        #### View
        #########
        if (!empty($id)) {
            $label = OrderLabel::getOne($id);
            if (empty($label->id)) {
                return $this->redirectToRoute('LabelListAdmin');
            }
        } else {
            $label = new \stdClass();
            $label->color = 'ffffff';
        }

        Design::assign('label', $label);

        return $this->fetchResponse('order/label.tpl');
    }
}
