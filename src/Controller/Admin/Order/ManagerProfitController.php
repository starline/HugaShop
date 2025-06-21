<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 */

namespace App\Controller\Admin\Order;

use HugaShop\Models\Design;
use HugaShop\Models\Request;
use HugaShop\Models\Settings;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ManagerProfitController extends BaseAdminController
{

    private $entity_params = [
        'create_order_rate' =>             ['type' => 'int'],
        'take_order_rate' =>               ['type' => 'int'],
        'referral_order_rate' =>           ['type' => 'int'],
    ];


    #[Route('/admin/order/manager_profit', name: 'ManagerProfitAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('user_manager');

        if (!empty($settings = Request::getDataAcces($this->entity_params))) {

            // Выбираем найстройки из POST
            foreach ($settings as $name => $val) {
                Settings::set($name, $val); # save settings
            }

            Design::append('service_messages_success', 'updated');
        }

        return $this->fetchResponse('order/manager_profit.tpl');
    }
}
