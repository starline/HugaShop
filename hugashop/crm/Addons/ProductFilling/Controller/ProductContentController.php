<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 *
 */

namespace HugaShop\Addons\ProductFilling\Controller;

use HugaShop\Services\Design;
use HugaShop\Services\Secure;
use App\Services\LanguageService;
use App\Controller\BaseAdminController;
use HugaShop\Addons\BaseAddonTrait;
use Symfony\Component\Routing\Attribute\Route;
use HugaShop\Addons\ProductFilling\Models\Product;
use Symfony\Component\HttpFoundation\Response;

final class ProductContentController extends BaseAdminController
{
    use BaseAddonTrait;

    #[Route('/ProductFilling/content/{id}', name: 'AddonProductFillingContent', requirements: ['id' => '\d+'], priority: 20)]
    public function index(int $id): Response
    {
        $this->checkAdminAccess('product_content');

        LanguageService::languageCatch();

        if (!empty($product = Secure::getInputCheckEditAccess(Product::class, $id))) {
            Design::setFlashMessage('update', Product::updateProduct($id, $product));
        }

        $product = Product::getOneEditTranslate($id);
        if (empty($product->id)) {
            return $this->redirectToRoute('AddonProductFilling');
        }

        Design::assign('product', $product);

        return $this->fetchAddonResponse('product_content.tpl');
    }
}
