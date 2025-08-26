<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.9
 *
 */

namespace App\Controller\Front;

use HugaShop\Services\Cache;
use HugaShop\Services\Config;
use HugaShop\Services\Design;
use HugaShop\Models\Product\Product;
use App\Controller\BaseFrontController;
use HugaShop\Models\Content\ContentPost;
use HugaShop\Models\Product\ProductCategory;
use HugaShop\Models\Localization\Language;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SitemapController extends BaseFrontController
{
    #[Route('/sitemap.xml', priority: 10)]
    public function index(): Response
    {

        // Cache
        $cache_item = Cache::cache(self::class, 60 * 60)->getItem('sitemap');

        if (!$cache_item->isHit()) {
            $root_url        = Config::get('root_url');
            $main_language = Language::getMain();
            $languages     = Language::getLanguages();
            $posts         = ContentPost::getPosts(['visible' => 1]);
            $categories    = ProductCategory::getCategories();
            $products      = Product::getProducts(['visible' => 1]);
            $today         = date('Y-m-d');

            $paths = [''];

            // Страницы
            /*foreach(ContentPage::getList() as $p) {
                if($p->visible && $p->menu_id == 1) {
                    $paths[] = '/' . $this->esc($p->url);
                }
            }*/

            // Блог
            foreach ($posts as $post) {
                $paths[] = '/blog/' . $this->esc($post->url);
            }

            // Категории
            foreach ($categories as $category) {
                if ($category->visible) {
                    $paths[] = '/' . $this->esc($category->url);
                }
            }

            // Товары
            foreach ($products as $product) {
                $paths[] = '/tovar-' . $this->esc($product->url);
            }

            // Бренды
            /*foreach(ProductBrand::getBrands() as $b) {
                $paths[] = '/brands/' . $this->esc($b->url);
            }*/

            Design::assign([
                'root_url'      => $root_url,
                'main_language' => $main_language,
                'languages'     => $languages,
                'paths'         => $paths,
                'today'         => $today
            ]);

            $result = Design::fetch('sitemap.tpl');

            Cache::cache(self::class)->save($cache_item->set($result));
        }

        $result = $cache_item->get();

        $response = new Response($result);
        $response->headers->set('Content-type', 'text/xml');

        return $response;
    }


    /**
     * Esc
     */
    private function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}
