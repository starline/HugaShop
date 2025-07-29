<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.5
 *
 */

namespace App\Controller\Admin\Product;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use App\Services\PaginationService;
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


        ## Обработка действий
        #####################
        if (Request::checkCSRF()) {

            // Действия с выбранными
            $ids = Request::post('check');
            if (is_array($ids)) {
                switch (Request::post('action')) {
                    case 'set_in_filter': {
                            ProductFeature::updateList($ids, ['in_filter' => 1]);
                            break;
                        }
                    case 'unset_in_filter': {
                            ProductFeature::updateList($ids, ['in_filter' => 0]);
                            break;
                        }
                    case 'delete': {
                            $current_cat = Request::getInt('category_id');
                            foreach ($ids as $id) {

                                // текущие категории
                                $category_ids = ProductCategoryFeature::getFeatureCategoryIds($id);

                                // В каких категориях оставлять
                                $diff = array_diff($category_ids, (array)$current_cat);
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


        $filter = PaginationService::initFilter();

        // Текущий фильтр
        if ($query_filter = Request::get('filter', 'string')) {
            if ($query_filter == 'in_filter') {
                $filter['in_filter'] = 1;
            }

            Design::assign('filter', $query_filter);
        }

        if ($category_id = Request::getInt('category_id')) {
            $category = ProductCategory::getCategoryById($category_id);
            Design::assign('category', $category);

            $filter['category_id'] = $category->id;
        }

        $features       = ProductFeature::getFeatures($filter);
        $features_count = ProductFeature::countFeatures($filter);

        Design::assign('pagination',    PaginationService::getPagination($features_count, $filter));
        Design::assign('categories',    ProductCategory::getCategoriesTree());
        Design::assign('features',      $features);
        Design::assign('features_count', $features_count);

        return $this->fetchResponse('product/feature_list.tpl');
    }
}
