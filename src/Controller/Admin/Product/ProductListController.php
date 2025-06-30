<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
 *
 */

namespace App\Controller\Admin\Product;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use HugaShop\Models\Product\Product;
use HugaShop\Models\Product\ProductBrand;
use App\Controller\BaseAdminController;
use App\Services\PaginationService;
use HugaShop\Models\Product\ProductCategory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Illuminate\Database\Capsule\Manager as Capsule;

class ProductListController extends BaseAdminController
{
    #[Route('/admin/products', name: 'ProductListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess(['product_view', 'product_content']);

        $filter = PaginationService::initFilter();

        // Текущая категория
        $category_id = Request::getInt('category_id');
        $filter['category_id'] = $category_id;

        // Если категория существует Выбираем всех деток категории
        if ($category_id && $category = ProductCategory::getCategoryById($category_id)) {
            $filter['category_id'] = $category->children;
        }

        // Текущий бренд
        $brand_id = Request::getInt('brand_id');
        if ($brand_id && $brand = ProductBrand::getBrand($brand_id)) {
            $filter['brand_id'] = $brand->id;
        }

        // Текущий фильтр
        if ($fltr = Request::get('filter', 'string')) {
            if ($fltr == 'featured') {
                $filter['featured'] = 1;
            } elseif ($fltr == 'sale') {
                $filter['sale'] = 1;
            } elseif ($fltr == 'discounted') {
                $filter['discounted'] = 1;
            } elseif ($fltr == 'visible') {
                $filter['visible'] = 1;
            } elseif ($fltr == 'hidden') {
                $filter['visible'] = 0;
            } elseif ($fltr == 'outofstock') {
                $filter['in_stock'] = 0;
            } elseif ($fltr == 'instock') {
                $filter['in_stock'] = 1;
            } elseif ($fltr == 'stagnation') {
                $filter['stagnation'] = 1;
            } elseif ($fltr == 'purchase') {
                $filter['purchase'] = 1;
            } elseif ($fltr == 'top') {
                $filter['top'] = 1;
            }

            Design::assign('filter', $fltr);
        }

        $filter['date_from'] = Request::get('date_from');
        Design::assign('date_from', $filter['date_from']);

        // Поиск (type 'string' - сжирает запятые)
        $keyword = Request::get('keyword');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            Design::assign('keyword', $keyword);
        }

        // Обработка действий
        if (Request::checkCSRF()) {

            foreach (Helper::getPositions('DESC') as $id => $position) {
                Product::updateProduct($id, ['position' => $position]);
            }

            // Действия с выбранными
            $ids = Request::post('check');
            if (!empty($ids)) {
                switch (Request::post('action')) {
                    case 'disable': {
                            Product::updateProduct($ids, ['visible' => 0]);
                            break;
                        }
                    case 'enable': {
                            Product::updateProduct($ids, ['visible' => 1]);
                            break;
                        }
                    case 'set_featured': {
                            Product::updateProduct($ids, ['featured' => 1]);
                            break;
                        }
                    case 'unset_featured': {
                            Product::updateProduct($ids, ['featured' => 0]);
                            break;
                        }
                    case 'set_sale': {
                            Product::updateProduct($ids, ['sale' => 1]);
                            break;
                        }
                    case 'unset_sale': {
                            Product::updateProduct($ids, ['sale' => 0]);
                            break;
                        }
                    case 'delete': {
                            foreach ($ids as $id) {
                                Product::deleteProduct($id);
                            }
                            break;
                        }
                    case 'duplicate': {
                            foreach ($ids as $id) {
                                Product::duplicateProduct(intval($id));
                            }
                            break;
                        }
                    case 'move_to_page': {

                            $target_page = Request::postInt('target_page');

                            // Сразу потом откроем эту страницу
                            $filter['page'] = $target_page;

                            // До какого товара перемещать
                            $limit = $filter['limit'] * ($target_page - 1);
                            if ($target_page > Request::getInt('page')) {
                                $limit += count($ids) - 1;
                            } else {
                                $ids = array_reverse($ids, true);
                            }

                            $temp_filter = $filter;
                            $temp_filter['page'] = $limit + 1;
                            $temp_filter['limit'] = 1;
                            $target_product = Product::getProducts($temp_filter)->last();
                            $target_position = $target_product->position;

                            // Если вылезли за последний товар - берем позицию последнего товара в качестве цели перемещения
                            if ($target_page > Request::getInt('page') && !$target_position) {
                                $target_position = Product::getLastProductPosition();
                            }

                            foreach ($ids as $id) {
                                $sort_product = Product::getOne($id);
                                $initial_position = $sort_product->position;

                                if ($target_position > $initial_position) {
                                    Product::where('position', '>', $initial_position)
                                        ->where('position', '<=', $target_position)
                                        ->update([
                                            'position' => Capsule::raw('position - 1')
                                        ]);
                                } else {
                                    Product::where('position', '<', $initial_position)
                                        ->where('position', '>=', $target_position)
                                        ->update([
                                            'position' => Capsule::raw('position + 1')
                                        ]);
                                }

                                Product::where('id', $id)->update(['position' => $target_position]);
                            }
                            break;
                        }
                    case 'move_to_brand': {
                            $brand_id = Request::postInt('target_brand');
                            $brand = ProductBrand::getBrand($brand_id);
                            $filter['page'] = 1;
                            $filter['brand_id'] = $brand_id;
                            Product::updateProduct($ids, ["brand_id" => $brand_id]);

                            // Заново выберем бренды категории
                            $brands = ProductBrand::getBrands(['category_id' => $category_id]);
                            Design::assign('brands', $brands);

                            break;
                        }
                }
            }
        }

        // Отображение
        if (isset($brand)) {
            Design::assign('brand', $brand);
        }
        if (isset($category)) {
            Design::assign('category', $category);
        }

        // TODO: Фильтр по характеристике

        $products = Product::getProducts($filter, join: ['image', 'movements']);
        $products_count = Product::countProducts($filter);

        if ($products->isNotEmpty()) {
            foreach ($products as $product) {
                $product->profit_price = $product->price - $product->cost_price;
            }
        }

        // Все Бренды
        $all_brands = ProductBrand::getBrands();

        // Бренды категории
        $brands = ProductBrand::getBrands(['category_id' => $filter['category_id']]);

        // Категории
        $categories = ProductCategory::getCategoriesTree();

        Design::assign('pagination',        PaginationService::getPagination($products_count, $filter));
        Design::assign('products',          $products);
        Design::assign('products_count',    $products_count);
        Design::assign('categories',        $categories);
        Design::assign('all_brands',        $all_brands);
        Design::assign('brands',            $brands);

        return $this->fetchResponse('product/product_list.tpl');
    }
}
