<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.1
 *
 */

namespace App\Controller\Admin\Product;

use HugaShop\Models\Config;
use HugaShop\Services\Design;
use HugaShop\Models\Request;
use App\Controller\BaseAdminController;
use HugaShop\Models\Product\ProductBrand;
use HugaShop\Models\Localization\Language;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BrandController extends BaseAdminController
{
    private $allowed_image_extentions = ['png', 'gif', 'jpg', 'jpeg', 'ico'];

    #[Route('/admin/product/brand', name: 'BrandNewAdmin')]
    #[Route('/admin/product/brand/{id}', requirements: ['id' => '\d+'], name: 'BrandAdmin')]
    public function index(?int $id = null): Response
    {

        $this->checkAdminAccess('product_brand');

        // Init content language
        Language::languageCatch();

        #### Update
        ###########
        if (!empty($brand = Request::getDataAcces(ProductBrand::getFields()))) {

            if (empty($brand->id)) {
                $brand = Design::setFlashMessage('add', ProductBrand::addBrand($brand));
            } else {
                Design::setFlashMessage('update', ProductBrand::updateBrand($brand->id, $brand));
            }

            // Удаление изображения
            if (Request::post('delete_image')) {
                ProductBrand::deleteImage($brand->id);
            }

            // Загрузка изображения
            $image = Request::files('image');
            if (!empty($image['name']) && in_array(strtolower(pathinfo($image['name'], PATHINFO_EXTENSION)), $this->allowed_image_extentions)) {
                ProductBrand::deleteImage($brand->id);
                move_uploaded_file($image['tmp_name'], Config::get('images_brands_dir') . $image['name']);
                ProductBrand::updateBrand($brand->id, ['image' => $image['name']]);
            }

            // Делаем редирект на страницу с ID
            return $this->redirectToRouteLang('BrandAdmin', ['id' => $brand->id]);
        }


        #### View
        #########
        if (!empty($id)) {
            $brand = ProductBrand::getBrand($id);

            if (empty($brand->id)) {
                return $this->redirectToRoute('BrandListAdmin');
            }
        }

        Design::assign('brand', $brand);

        return $this->fetchResponse('product/brand.tpl');
    }
}
