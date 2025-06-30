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
use HugaShop\Services\Request;
use HugaShop\Models\Finance\FinancePurse;
use HugaShop\Models\Order\OrderPayment;
use HugaShop\Models\Order\OrderDelivery;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DeliveryController extends BaseAdminController
{
    #[Route('/admin/order/delivery', name: 'OrderDeliveryNewAdmin')]
    #[Route('/admin/order/delivery/{id}', requirements: ['id' => '\d+'], name: 'OrderDeliveryAdmin')]
    public function index(?int $id): Response
    {


        #### Update
        ###########
        if (!empty($delivery = Request::getDataAcces(OrderDelivery::getFields()))) {

            $delivery->settings = Request::post('delivery_settings', 'array');
            $delivery_payments = Request::post('delivery_payments');

            if (empty($delivery->id)) {
                $delivery = Design::setFlashMessage('add', OrderDelivery::createOne($delivery));
            } else {
                Design::setFlashMessage('update', OrderDelivery::updateOne($delivery->id, $delivery));
                OrderDelivery::updateDeliveryPayments($delivery->id, $delivery_payments);
            }

            return $this->redirectToRoute('OrderDeliveryAdmin', ['id' => $delivery->id]);
        }


        #### View
        #########
        if (!empty($id)) {
            $delivery = OrderDelivery::getOne($id, join: [
                'payments'
            ]);

            if (empty($delivery->id)) {
                return $this->redirectToRoute('DeliveryListAdmin');
            }

            Design::assign('delivery', $delivery);
        }

        Design::assign('payment_methods',   OrderPayment::getPaymentMethods());       # Связанные способы оплаты
        Design::assign('delivery_modules',  OrderDelivery::getDeliveryModules());
        Design::assign('finance_purses',    FinancePurse::getPurses());

        return $this->fetchResponse('order/delivery.tpl');
    }
}
