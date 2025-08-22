<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.7
 */

namespace HugaShop\Addons\ProductFilling\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use HugaShop\Services\Request;
use App\Services\PaginationService;
use App\Controller\BaseAdminController;
use HugaShop\Models\Localization\Language;
use HugaShop\Addons\BaseAddonTrait;
use HugaShop\Models\Product\ProductCategory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use HugaShop\Addons\ProductFilling\Models\Product;
use HugaShop\Addons\ProductFilling\Services\Calculate;

final class ProductFillingController extends BaseAdminController
{
    use BaseAddonTrait;

    #[Route('/ProductFilling', name: 'ExtProductFilling', priority: 20)]
    public function index()
    {
        $filter = PaginationService::initFilter();

        $category_id = Request::getInt('category_id');
        $filter['category_id'] = $category_id;
        if ($category_id && ($category = ProductCategory::getCategoryById($category_id))) {
            $filter['category_id'] = $category->children;
            Design::assign('category', $category);
        }

        if ($keyword = Request::get('keyword')) {
            $filter['keyword'] = $keyword;
            Design::assign('keyword', $keyword);
        }

        $filling = Request::getInt('filling');
        if (!is_null($filling)) {
            $filter['filling'] = max(0, min(100, $filling));
        }

        $products       = Product::getProducts($filter, join: ['image', 'fillings']);
        $products_count = Product::countProducts($filter);
        $categories     = ProductCategory::getCategoriesTree();

        Design::assign('categories', $categories);
        Design::assign('products', $products);
        Design::assign('products_count', $products_count);
        Design::assign('filling', $filter['filling'] ?? 100);
        Design::assign('languages', Language::getLanguages());
        Design::assign('pagination', PaginationService::getPagination($products_count, $filter));
        Design::assign('addon', $this->getAddon());

        return $this->fetchAddonResponse('product_list.tpl');
    }


    #[Route('/ProductFilling/ajax/calculate', name: 'ExtProductFillingCalculate', priority: 20)]
    public function calculate()
    {
        $batch = 20;
        $from = max(0, Request::getInt('from'));
        $filter = [
            'limit' => $batch,
            'page'  => intdiv($from, $batch) + 1,
        ];

        $products = Product::getProducts($filter);
        foreach ($products as $product) {
            Calculate::calculateProduct($product->id);
        }

        $total = Product::countProducts();
        $processed = $from + $products->count();

        return new JsonResponse([
            'from'  => $processed,
            'total' => $total,
            'end'   => $processed >= $total,
        ]);
    }


    #[Route('/ProductFilling/ajax/calculateProduct', name: 'ExtProductFillingCalculateProduct', priority: 20)]
    public function calculateProduct()
    {

        if (!Secure::checkCSRF()) {
            return new JsonResponse(['error' => 'csrf']);
        }

        $id = Request::postInt('id');
        if (empty($id)) {
            return new JsonResponse(['error' => 'id']);
        }

        Calculate::calculateProduct($id);

        $result = [];
        if ($product = Product::getProduct($id, ['fillings'])) {
            foreach ($product->fillings as $fill) {
                $result[$fill->language_code] = $fill->percent;
            }
        }

        return new JsonResponse($result);
    }
}
