<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.4
 *
 */

namespace App\Controller\Admin;

use HugaShop\Models\Settings;
use HugaShop\Services\Config;
use HugaShop\Services\Request;
use HugaShop\Models\Order\Order;
use App\Controller\BaseAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends BaseAdminController
{
    #[Route('/admin', name: 'MainAdmin')]
    public function index(): Response
    {
        return $this->redirectToRoute('OrderListAdmin');
    }


    #[Route('/admin/test', name: 'TestAdmin')]
    public function test(): Response
    {

        $user['name'] = 'User Name';
        $user['isLoggedIn'] = true;
        $user['id'] = 3;

        $filter['page'] = max(1, Request::getInt('page'));
        $filter['limit'] = Request::get('page', 'string') == 'all' ? 'all' : Settings::getParam('products_num_admin');
        $orders = Order::getOrders($filter, false, ['delivery_method', 'payment_method']); # Выбираем все заказы

        $seo['title'] = 'Заголовок';


        // Set new Template Namespace 
        // Use: '@admin/template.html.twig
        //$this->container->get('twig')->getLoader()->addPath(Config::get('templates_dir') . 'admin', 'admin');


        // the `renderBlockView()` method only returns the contents created by the
        // template block, so you can use those contents later in a `Response` object
        $contents = $this->renderBlockView('admin/main.html.twig', 'some_block', []);

        // the template path is the relative file path from `templates/`
        return $this->render('admin/main.html.twig', [
            'page_title' => 'Тестим Twig',
            'user' => $user,
            'orders' =>  $orders,
            'content' =>  $contents,
            'seo' => $seo
        ]);
    }
}
