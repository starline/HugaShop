<?php

/**
 *
 * @author Andi Huga
 * @version 1.9
 *
 */

namespace App\Controller\Front\Exchange;

use HugaShop\Models\Order\OrderPayment;
use App\Controller\BaseFrontController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PaymentController extends BaseFrontController
{
    #[Route('/exchange/payment/{id}', requirements: ['id' => '\d+'], priority: 4, name: 'PaymentExchange')]
    public function index(int $id)
    {

        if (empty($payment = OrderPayment::getOne($id))) {
            throw $this->createNotFoundException('Access denied'); # 404
        }

        $module_name = $payment->module;
        $ClassName = "HugaShop\\Modules\\Payment\\{$module_name}\\{$module_name}";

        if (empty($module_name) || !class_exists($ClassName)) {
            throw $this->createNotFoundException('Access denied'); # 404
        }

        $Module = new $ClassName();
        return new Response($Module->callback());
    }
}
