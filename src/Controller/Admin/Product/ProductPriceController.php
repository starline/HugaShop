<?php

/**
 * HugaShop - Selling anything
 *
 * @author Andri Huga
 * @version 3.0
 * 
 * ProductPriceAdmin
 *
 */

namespace App\Controller\Admin\Product;

use HugaShop\Services\Design;
use HugaShop\Services\Helper;
use HugaShop\Services\Request;
use App\Services\PaginationService;
use HugaShop\Models\Order\Order;
use HugaShop\Models\Product\Product;
use HugaShop\Models\User\UserPermission;
use App\Controller\BaseAdminController;
use HugaShop\Models\Product\ProductRelated;
use HugaShop\Models\Product\ProductVariant;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductPriceController extends BaseAdminController
{

    private $fillable = [
        'id' =>                 ['type' => 'int'],
        'visible' =>            ['type' => 'tinyint'],
        'disable' =>            ['type' => 'tinyint'],
        'featured' =>           ['type' => 'tinyint'],
        'sale' =>               ['type' => 'tinyint'],
        'custom' =>             ['type' => 'tinyint'],
        'awaiting' =>           ['type' => 'tinyint'],
        'variant_name' =>       ['type' => 'varchar'],
        'sku' =>                ['type' => 'varchar'],
        'price' =>              ['type' => 'decimal'],
        'cost_price' =>         ['type' => 'decimal'],
        'old_price' =>          ['type' => 'decimal'],
        'stock' =>              ['type' => 'int'],
        'weight' =>             ['type' => 'decimal'],
        'awaiting_date' =>      ['type' => 'date'],
        'product_id' =>         ['type' => 'int']
    ];


    #[Route('/admin/product/{id}/price', requirements: ['id' => '\d+'], name: 'ProductPriceAdmin')]
    public function index(int $id): Response
    {

        if (!UserPermission::checkAccess('product_price')) { # Check acces
            return $this->redirectToRoute('ProductAdmin', ['id' => $id]);
        }


        #### Update
        ###########
        if (!empty($product = Request::getDataAcces($this->fillable))) {

            // Преобразовываем дату datapiker для mysql
            if (!empty($product->awaiting_date)) {
                $product->awaiting_date =  Helper::dateConvert($product->awaiting_date . ' 12:00', 'Y-m-d');
            }

            Design::setFlashMessage('update', Product::updateProduct($product->id, $product));


            // Связанные товары
            ProductRelated::deleteAllRelatedProducts($product->id); # Удаляем все связанные товары
            if (!empty($rel_products_ids = Request::post('related_products', 'array'))) {
                $pos = 0;
                foreach ($rel_products_ids as $rel_id) {
                    ProductRelated::addRelatedProduct($product->id, $rel_id, $pos++);
                }
            }

            // Варианты товара
            $variants = Request::post('product_variants', 'array');
            ProductVariant::updateVariants($product->id, $variants);

            return $this->redirectToRoute('ProductPriceAdmin', ['id' => $product->id]);
        }



        #### View
        #########
        if (!empty($id)) {

            $product = Product::getProduct(intval($id), join: [
                'related',
                'related.image'
            ]);

            if (empty($product->id)) {
                return $this->redirectToRoute('ProductListAdmin');
            }

            Design::assign('product', $product);

            $product_variants = ProductVariant::getVariants($product->id, ['product', 'product.image']);
            Design::assign('product_variants', $product_variants);
        }

        return $this->fetchResponse('product/product_price.tpl');
    }


}
