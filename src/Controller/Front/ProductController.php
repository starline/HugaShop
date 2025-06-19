<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.0
 *
 * Этот класс использует шаблон product.tpl
 *
 */

namespace App\Controller\Front;

use HugaShop\Api\Image;
use HugaShop\Api\Design;
use HugaShop\Api\Request;
use HugaShop\Api\Settings;
use HugaShop\Api\Product\Product;
use App\Controller\BaseFrontController;
use HugaShop\Api\Product\ProductOption;
use HugaShop\Api\Content\ContentComment;
use HugaShop\Api\Product\ProductRelated;
use HugaShop\Api\Product\ProductCategory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends BaseFrontController
{

    #[Route('/p/{id}', requirements: ['id' => '\d+'], name: 'ProductShortId')]
    #[Route('/product/{id}', requirements: ['id' => '\d+'], name: 'ProductId')]
    public function product_id(int $id): Response
    {
        if (empty($product = Product::getProduct($id))) {
            throw $this->createNotFoundException('Product does not found'); # 404
        }

        return $this->redirectToRoute('Product', ['url' => $product->url], 301);
    }


    #[Route('/{url}/p{id}', requirements: ['id' => '\d+'], name: 'ProductUrl', priority: 1)]
    #[Route('/tovar-{url}', name: 'Product')]
    public function product_url(string $url, ?int $id = null): Response
    {

        // Выбираем товар из базы
        $product = Product::getProduct(id: $url, join: [
            'image',
            'brand',
            'variants',
            'variants.product',
            'variants.product.image'
        ]);

        if (empty($product)) {
            throw $this->createNotFoundException('Product does not found'); # 404
        }

        // Redirect to canonical page
        if ($product->url !== $url) {
            return $this->redirectToRoute('Product', ['url' => $product->url], 301);
        }

        // Категория товара
        $category = ProductCategory::getCategory(intval($product->category_id));
        Design::assign('category', $category);

        // Изображение товара
        $product->features = ProductOption::getProductOptions($product->id);

        // Comments
        ContentComment::handleComments($product->id, Product::class);

        // Связанные товары
        $related_products = ProductRelated::getRelatedProducts($product->id);
        Design::assign('related_products', $related_products);

        // Добавление в историю просмотренных товаров
        $max_visited_products = 50; # Максимальное число хранимых товаров в истории
        if (!empty($cookie_bp = Request::getCookie('BP'))) {
            $browsed_products = explode('.', $cookie_bp);

            // Удалим текущий товар, если он был
            if (($exists = array_search($product->id, $browsed_products)) !== false) {
                unset($browsed_products[$exists]);
            }
        }

        // Добавим текущий товар
        $browsed_products[] = $product->id;
        $cookie_data = join('.', array_slice($browsed_products, -$max_visited_products, $max_visited_products));

        Request::setCookie("BP", $cookie_data, 30); # Время жизни - 30 дней


        // SEO metateg
        if (empty($product->meta_title)) {
            $product->meta_title = $product->name;
        }

        if (empty($product->meta_description)) {
            $product->meta_description = $product->meta_title . ' ' . Settings::getParam('product_meta_description');
        }

        // И передаем его в шаблон
        Design::assign('canonical', $this->generateUrl('Product', ['url' => $product->url]));
        Design::assign('product', $product);
        Design::assign('meta_title', $product->meta_title);
        Design::assign('meta_description', $product->meta_description);

        return $this->fetchResponse('product.tpl');
    }
}
