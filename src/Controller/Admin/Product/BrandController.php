<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.6
 *
 */

namespace App\Controller\Admin\Product;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use App\Services\ImageService;
use App\Services\LanguageService;
use App\Controller\BaseAdminController;
use HugaShop\Models\Product\ProductBrand;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BrandController extends BaseAdminController
{

    #[Route('/admin/product/brand', name: 'BrandNewAdmin')]
    #[Route('/admin/product/brand/{id}', requirements: ['id' => '\d+'], name: 'BrandAdmin')]
    public function index(?int $id = null): Response
    {

        $this->checkAdminAccess('product_brand');

        // Init content language
        LanguageService::languageCatch();

        #### Update
        ###########
        if (!empty($brand = Secure::getInputCheckEditAccess(ProductBrand::class, $id))) {

            if (empty($brand->id)) {
                $brand = Design::setFlashMessage('add', ProductBrand::createOne($brand));
            } else {
                Design::setFlashMessage('update', ProductBrand::updateOne($brand->id, $brand));
            }

            ImageService::catchImages($brand->id, ProductBrand::class);

            // Делаем редирект на страницу с ID
            return $this->redirectToRouteLang('BrandAdmin', ['id' => $brand->id]);
        }


        #### View
        #########
        if (!empty($id)) {
            $brand = ProductBrand::getOneEditTranslate($id, join: ['image']);
            if (empty($brand->id)) {
                return $this->redirectToRoute('BrandListAdmin');
            }
        }

        Design::assign('brand', $brand);
        return $this->fetchResponse('product/brand.tpl');
    }
}
