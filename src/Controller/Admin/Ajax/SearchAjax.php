<?php

namespace App\Controller\Admin\Ajax;

use HugaShop\Models\Image;
use HugaShop\Services\Request;
use HugaShop\Models\User\User;
use HugaShop\Models\Order\Order;
use HugaShop\Models\Product\Product;
use App\Controller\BaseAdminController;
use HugaShop\Models\Warehouse\WarehouseMove;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class SearchAjax extends BaseAdminController
{

    private $page_limit = 100;


    #[Route('/admin/ajax/search/movement', name: 'SearchAjaxAdmin')]
    public function mivement()
    {

        $this->checkAdminAccess(['finance', 'warehouse_edit', 'warehouse_add']);

        $filter['limit'] = $this->page_limit;

        // Поиск (без 'string' - сжирает запятые)
        $keyword = Request::get('query');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
        }

        $movements = WarehouseMove::getMovements($filter);

        $suggestions = [];
        foreach ($movements as $movement) {
            $suggestion =           new \stdClass();
            $suggestion->value =    'Перемещение №' . $movement->id;
            $suggestion->data =     $movement;
            $suggestions[] =        $suggestion;
        }

        $res = new \stdClass();
        $res->query = $keyword;
        $res->suggestions = $suggestions;

        return new JsonResponse($res);
    }


    #[Route('/admin/ajax/search/user')]
    public function user()
    {

        $this->checkAdminAccess(['finance', 'warehouse_edit', 'warehouse_add', 'user_edit', 'order_edit']);

        $filter['limit'] = $this->page_limit;

        // Поиск ('string' - сжирает запятые? не исполььзуем)
        $keyword = Request::get('query');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
        }

        // Сортировка
        $sort = Request::get('sort', 'string');
        if (!empty($sort)) {
            $filter['sort'] = $sort;
        }

        $users = User::getUsers($filter);

        $suggestions = [];
        foreach ($users as $user) {
            $suggestion = new \stdClass();
            $suggestion->value = $user->name;
            if (!empty($user->phone)) {
                $suggestion->value .= " ($user->phone)";
            }
            $suggestion->data = $user;
            $suggestions[] = $suggestion;
        }

        $res = new \stdClass();
        $res->query = $keyword;
        $res->suggestions = $suggestions;

        return new JsonResponse($res);
    }


    /**
     * Search product
     */
    #[Route('/admin/ajax/search/product')]
    public function product()
    {

        $this->checkAdminAccess(['order', 'product_price', 'warehouse_add', 'warehouse_edit']);

        // Поиск (без 'string' - сжирает запятые)
        $keyword = Request::get('query');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
        }

        $filter['limit'] = $this->page_limit;

        $products = Product::getProducts($filter, ['image', 'movements']);

        $suggestions = [];
        foreach ($products as $product) {

            if (!empty($product->image->filename)) {
                $product->image->url = Image::getImageURL($product->image->filename, 60, 60, 'c');
            }

            $suggestion = new \stdClass();
            $suggestion->value = $product->name;
            $suggestion->data = $product;
            $suggestions[] = $suggestion;
        }

        $res = new \stdClass();
        $res->query = $keyword;
        $res->suggestions = $suggestions;

        return new JsonResponse($res);
    }


    /**
     * Search order
     */
    #[Route('/admin/ajax/search/order')]
    public function order()
    {

        $this->checkAdminAccess(['finance', 'warehouse_edit', 'warehouse_add', 'user_edit', 'order_edit']);

        $filter['limit'] = $this->page_limit;

        // Поиск (без 'string' - сжирает запятые)
        $keyword = Request::get('query');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
        }

        $orders = Order::getOrders($filter);

        $suggestions = [];
        foreach ($orders as $order) {
            $suggestion = new \stdClass();
            $suggestion->value = 'Заказ №' . $order->id;
            $suggestion->data = $order;
            $suggestions[] = $suggestion;
        }

        $res = new \stdClass();
        $res->query = $keyword;
        $res->suggestions = $suggestions;

        return new JsonResponse($res);
    }
}
