<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.3
 *
 */

namespace HugaShop\Extensions\ProductBrowsed\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Request;
use HugaShop\Models\Product\Product;
use App\Controller\BaseFrontController;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\Routing\Attribute\Route;

final class ProductBrowsedController extends BaseFrontController
{

    use BaseExtensionTrait;

    #[Route('/ProductBrowsed/ajax', name: 'ExtProductBrowsedAjax', priority: 20)]
    public function browsed()
    {

        $cookie_bp = Request::getCookie('BP');
        $limit =  $this->getSettings()->limit;

        if (!empty($cookie_bp)) {
            $browsed_products_ids = array_reverse(explode('.', $cookie_bp));
            $browsed_products_ids = array_slice($browsed_products_ids, 0, $limit);

            $browsed_products = Product::getProducts([
                'id' => $browsed_products_ids,
                'visible' => 1
            ], join: ['image']);

            // Сохраняем порядок из $browsed_products_ids
            $browsed_products_sort = [];
            foreach ($browsed_products_ids as $id) {
                if (isset($browsed_products[$id])) {
                    $browsed_products_sort[] = $browsed_products[$id];
                }
            }

            Design::assign('browsed_products', $browsed_products_sort);
        }

        return $this->fetchExtResponse('product_browsed.tpl', 'product_browsed');
    }
}
