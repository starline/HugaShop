<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
 *
 */

namespace App\Controller\Admin\Order;

use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\Finance\FinancePurse;
use HugaShop\Api\Order\OrderPayment;
use HugaShop\Api\Order\OrderDelivery;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DeliveryController extends BaseAdminController
{
    #[Route('/admin/order/delivery', name: 'OrderDeliveryNewAdmin')]
    #[Route('/admin/order/delivery/{id}', requirements: ['id' => '\d+'], name: 'OrderDeliveryAdmin')]
    public function index(?int $id): Response
    {

        $delivery_payments = [];
        $delivery_settings = [];


        #### Update
        ###########
        if (!empty($delivery = Request::getDataAcces(OrderDelivery::getFields()))) {

            $delivery->settings = Request::post('delivery_settings');

            if (empty($delivery->id)) {
                $delivery->id = Design::setFlashMessage('add', OrderDelivery::add($delivery));
            } else {
                Design::setFlashMessage('update', OrderDelivery::update($delivery->id, $delivery));
                $delivery_payments = Request::post('delivery_payments');
                OrderDelivery::updateDeliveryPayments($delivery->id, $delivery_payments);
            }

            return $this->redirectToRoute('OrderDeliveryAdmin', ['id' => $delivery->id]);
        }


        #### View
        #########
        if (!empty($id)) {
            $delivery = OrderDelivery::getOne($id);

            if (empty($delivery->id)) {
                return $this->redirectToRoute('DeliveryListAdmin');
            }

            $delivery_payments = OrderDelivery::getDeliveryPayments($id);
            $delivery_settings = $delivery->settings;
        }

        $payment_methods =  OrderPayment::getPaymentMethods(); # Связанные способы оплаты
        $delivery_modules = OrderDelivery::getDeliveryModules();
        $finance_purses =   FinancePurse::getPurses();

        Design::assign('delivery', $delivery);
        Design::assign('delivery_settings', $delivery_settings);
        Design::assign('delivery_payments', $delivery_payments);
        Design::assign('payment_methods', $payment_methods);
        Design::assign('delivery_modules', $delivery_modules);
        Design::assign('finance_purses', $finance_purses);

        return $this->fetchResponse('order/delivery.tpl');
    }
}
