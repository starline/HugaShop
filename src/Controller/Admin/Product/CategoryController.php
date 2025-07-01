<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace App\Controller\Admin\Product;

use HugaShop\Models\Image;
use HugaShop\Services\Design;
use HugaShop\Services\Request;
use HugaShop\Models\SeoFaqs;
use HugaShop\Models\SeoKeywords;
use App\Controller\BaseAdminController;
use HugaShop\Models\Localization\Language;
use HugaShop\Models\Product\ProductCategory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Models\Product\ProductCategorySynonym;

class CategoryController extends BaseAdminController
{

    #[Route('/admin/product/category', name: 'CategoryNewAdmin')]
    #[Route('/admin/product/category/{id}', requirements: ['id' => '\d+'], name: 'CategoryAdmin')]
    public function index(?int $id = null): Response
    {

        $this->checkAdminAccess('product_category');

        // Init content language
        Language::languageCatch();

        #### Update
        ###########
        if (!empty($category = Request::getDataAcces(ProductCategory::getFields()))) {

            if (empty($category->id)) {
                $category = Design::setFlashMessage('add', ProductCategory::addCategory($category));
            } else {
                Design::setFlashMessage('update', ProductCategory::updateCategory($category->id, $category));
            }

            // SOE keywords
            SeoKeywords::catchKeywords($category->id, 'category');
            SeoFaqs::catchKeywords($category->id, 'category');

            Image::catchImages($category->id, 'category', 'images');

            // Обновляем синонимы
            $synonyms = Request::post('synonyms', 'array');
            ProductCategorySynonym::updateCategorySynonyms($category->id, $synonyms);

            // Делаем редирект на страницу с ID
            return $this->redirectToRouteLang('CategoryAdmin', ['id' => $category->id]);
        }


        #### View
        #########
        if (!empty($id)) {

            $category = ProductCategory::getCategoryById($id);

            if (empty($category->id)) {
                return $this->redirectToRoute('CategoryListAdmin');
            }

            // Изображения товара
            $images = Image::getImages($category->id, 'category');
            $images_content = Image::getImages($category->id, 'category_content');


            $seo_keywords_arr = SeoKeywords::getKeywords($category->id, 'category');
            $seo_keywords = join("\n", $seo_keywords_arr);

            $seo_faqs_arr = SeoFaqs::getFAQs($category->id, 'category');
            $seo_faqs = join("\n", $seo_faqs_arr);

            // Выбираем синонимы категории
            $synonyms = ProductCategorySynonym::getSynonyms(['category_id' => $category->id]);

            Design::assign('images', $images);
            Design::assign('images_content', $images_content);
            Design::assign('seo_keywords', $seo_keywords);
            Design::assign('seo_faqs', $seo_faqs);
            Design::assign('synonyms', $synonyms);
            Design::assign('category', $category);
        }


        Design::assign('categories', ProductCategory::getCategoriesTree());

        return $this->fetchResponse('product/category.tpl');
    }
}
