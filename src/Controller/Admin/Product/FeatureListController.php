<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.0
 *
 */

namespace App\Controller\Admin\Product;

use HugaShop\Models\Design;
use HugaShop\Models\Helper;
use HugaShop\Models\Request;
use App\Controller\BaseAdminController;
use HugaShop\Models\Product\ProductFeature;
use HugaShop\Models\Product\ProductCategory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Models\Product\ProductCategoryFeature;

class FeatureListController extends BaseAdminController
{
    #[Route('/admin/product/features', name: 'FeatureListAdmin')]
    public function index(): Response
    {

        $this->checkAdminAccess('product_feature');

        // Обработка действий
        if (Request::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'set_in_filter': {
                            ProductFeature::updateOne($ids, ['in_filter' => 1]);
                            break;
                        }
                    case 'unset_in_filter': {
                            ProductFeature::updateOne($ids, ['in_filter' => 0]);
                            break;
                        }
                    case 'delete': {
                            $current_cat = Request::getInt('category_id');
                            foreach ($ids as $id) {
                                // текущие категории
                                $cats = ProductCategoryFeature::getFeatureCategories($id);

                                // В каких категориях оставлять
                                $diff = array_diff($cats, (array)$current_cat);
                                if (!empty($current_cat) && !empty($diff)) {
                                    ProductCategoryFeature::updateFeatureCategories($id, $diff);
                                } else {
                                    ProductFeature::deleteFeature($id);
                                }
                            }
                            break;
                        }
                }
            }

            foreach (Helper::getPositions() as $id => $position) {
                ProductFeature::updateOne($id, ['position' => $position]);
            }
        }

        $categories = ProductCategory::getCategoriesTree();
        $category = null;

        $filter = [];
        $category_id = Request::getInt('category_id');
        if ($category_id) {
            $category = ProductCategory::getCategoryById($category_id);
            $filter['category_id'] = $category->id;
        }

        $features = ProductFeature::getFeatures($filter);

        Design::assign('categories', $categories);
        Design::assign('category', $category);
        Design::assign('features', $features);

        return $this->fetchResponse('product/feature_list.tpl');
    }
}
