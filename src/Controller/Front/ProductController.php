<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.7
 *
 */

namespace App\Controller\Front;

use HugaShop\Models\Settings;
use HugaShop\Services\Design;
use App\Event\ProductViewEvent;
use HugaShop\Models\Product\Product;
use App\Controller\BaseFrontController;
use HugaShop\Models\Content\ContentComment;
use HugaShop\Models\Product\ProductVariant;
use HugaShop\Models\Product\ProductCategory;
use HugaShop\Models\Product\ProductOption;
use HugaShop\Services\Config;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends BaseFrontController
{

    #[Route('/p/{id}', requirements: ['id' => '\d+'], name: 'ProductShortId', priority: 10)]
    #[Route('/product/{id}', requirements: ['id' => '\d+'], name: 'ProductId', priority: 10)]
    #[Route('/{url}/pd{id}', requirements: ['id' => '\d+'], name: 'ProductUrl', priority: 10)]
    public function product_id(int $id, ?string $url = null): Response
    {
        if (empty($product = Product::getProduct($id))) {
            throw $this->createNotFoundException('Product does not found'); # 404
        }

        return $this->redirectToRoute('Product', ['url' => $product->url], 301);
    }



    #[Route(Config::PRODUCT_PREFIX . '{url}', name: 'Product', priority: 10)]
    public function product_url(string $url, ?int $id = null): Response
    {

        // Выбираем товар из базы
        $product = Product::getOneTranslate(['url' => $url], join: [
            'image',
            'images',
            'brand',
            'related',
            'related.image'
        ]);


        if (empty($product) || (!is_null($id) and $id !== $product->id)) {
            throw $this->createNotFoundException('Product does not found'); # 404
        }

        // Redirect to canonical page
        if ($product->url !== $url) {
            return $this->redirectToRoute('Product', ['url' => $product->url], 301);
        }

        $product->features = ProductOption::getProductOptions($product->id);

        // Variants
        $product_variants = ProductVariant::getVariants($product->id, ['product', 'product.image']);
        Design::assign('product_variants', $product_variants);

        // Категория товара
        $category = ProductCategory::getCategory(intval($product->category_id));
        Design::assign('category', $category);

        // Comments
        ContentComment::handleComments($product->id, Product::class);

        // SEO metateg
        if (empty($product->meta_title)) {
            $product->meta_title = $product->name;
        }

        if (empty($product->meta_description)) {
            $product->meta_description = $product->meta_title . ' ' . Settings::getParam('product_meta_description');
        }

        // И передаем его в шаблон
        Design::assign('canonical', $this->generateUrlWithLocale('Product', ['url' => $product->url]));
        Design::assign('product', $product);
        Design::assign('meta_title', $product->meta_title);
        Design::assign('meta_description', $product->meta_description);

        // Event product view
        $this->setEvent(new ProductViewEvent($product));

        return $this->fetchResponse('product.tpl');
    }
}
