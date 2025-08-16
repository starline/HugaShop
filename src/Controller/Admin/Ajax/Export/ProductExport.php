<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 *
 */

namespace App\Controller\Admin\Ajax\Export;

use HugaShop\Services\Config;
use HugaShop\Models\Product\Product;
use HugaShop\Services\Request;
use HugaShop\Models\Product\ProductCategory;
use App\Controller\BaseAdminController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductExport extends BaseAdminController
{
    private $columns_names = array(
        'sku'           => 'Арт.',
        'name'          => 'Название товара (вариант)',
        'price'         => 'Цена',
        'cost_price'    => 'Оптовая цена',
        'stock'         => 'Наличие',
        'url'           => 'Ссылка на сайт',
        'brand'         => 'Бранд',
        //'body' =>             	'Описание html',
        //'images' =>           	'Изображения',
        //'category'=>				'Категория',
    );

    private $column_delimiter       = ";";
    private $subcategory_delimiter  = '/';
    private $products_count         = 10; # кол-во товаров обработаных за раз
    private $filename               = 'products.csv';


    #[Route('/admin/ajax/products/export', name: 'ProductExport')]
    public function index()
    {

        $this->checkAdminAccess('export', checkCSRF: true);

        $export_file_path = Config::get('export_files_dir') . $this->filename;

        // Страница, которую экспортируем
        $page = Request::get('page');
        if (empty($page) || $page == 1) {
            $page = 1;

            // Если начали сначала - удалим старый файл экспорта
            if (is_writable($export_file_path)) {
                unlink($export_file_path);
            }
        }

        // Открываем файл экспорта на добавление
        $f = fopen($export_file_path, 'ab');

        // Выбираем названия характеристик товаров
        // $features = ProductFeature::getFeatures();
        // foreach ($features as $feature)
        // $this->columns_names[$feature->name] = $feature->name;

        // Если начали сначала - добавим в первую строку названия колонок
        if ($page == 1) {
            fputcsv($f, $this->columns_names, $this->column_delimiter);
        }


        // Отфильтровать
        $filter = [];
        $filter['page'] = $page;
        $filter['limit'] = $this->products_count;

        // Выбираем подкатегории
        $category_id = Request::getInt('category_id');
        $filter['category_id'] = $category_id;
        if ($category_id && $category = ProductCategory::getCategoryById($category_id)) {
            $filter['category_id'] = $category->children;
        }


        // Выбираем товаары с базы
        $products = Product::getProducts($filter, join: ['images', 'brand']);
        if (empty($products)) {
            return new JsonResponse(['end' => true]);
        }


        // Добаавляем характеристики к товару
        // $options = ProductOption::getProductOptions($p->id);
        // foreach ($options as $option) {
        // 	if (!isset($products[$option->product_id][$option->name]))
        // 		$products[$option->product_id][$option->name] = $option->value;
        // }


        // foreach ($products as $p_id => &$product) {
        // 	$categories = array();
        // 	$cats = ProductCategory::getProductCategories($p_id);

        // 	foreach ($cats as $category) {
        // 		$path = array();
        // 		$cat = ProductCategory::getCategoryById((int)$category->category_id);
        // 		if (!empty($cat)) {

        // 			// Формируем дерево категории
        // 			foreach ($cat->path as $p)
        // 				$path[] = str_replace($this->subcategory_delimiter, '\\' . $this->subcategory_delimiter, $p->name);

        // 			$categories[] = join('/', $path);
        // 		}
        // 	}
        // 	$product['category'] = join(', ', $categories);
        // }


        foreach ($products as $product) {
            foreach ($this->columns_names as $column_var => $column_name) {
                if (!empty($product->$column_var)) {
                    switch ($column_var) {
                        case 'brand': {
                                if (!empty($product->brand->name)) {
                                    $res[$column_var] = $product->brand->name;
                                }
                                break;
                            }
                        case 'url': {

                                // сформируем сссылку на товар
                                $res[$column_var] = Config::get('root_url') . '/' . Config::PRODUCT_PREFIX . $product->url;
                                break;
                            }
                        case 'images': {
                                foreach ($product->images as $image) {
                                    $filenames[] = $image->filename;
                                }
                                if (!empty($filenames)) {
                                    $res[$column_var] = implode(',', $filenames);
                                }
                                break;
                            }
                        default: {
                                $res[$column_var] = $product->$column_var;;
                            }
                    }
                }
            }
            fputcsv($f, $res, $this->column_delimiter);
        }

        fclose($f);
        $total_products = Product::countProducts($filter);

        $response = ['end' => true, 'page' => $page, 'totalpages' => ceil($total_products / $this->products_count)];
        if ($this->products_count * $page < $total_products) {
            $response['end'] = false;
        }

        return new JsonResponse($response);
    }
}
