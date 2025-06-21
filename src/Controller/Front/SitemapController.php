<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.5
 *
 */

namespace App\Controller\Front;

use HugaShop\Models\Config;
use HugaShop\Models\Helper;
use HugaShop\Models\Product\Product;
use HugaShop\Models\Content\ContentPost;
use App\Controller\BaseFrontController;
use HugaShop\Models\Product\ProductCategory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SitemapController extends BaseFrontController
{
    #[Route('/sitemap.xml', priority: 5)]
    public function index(): Response
    {

        // Cache
        $cache_item = Helper::cache(self::class, 60 * 60)->getItem('sitemap');

        if (!$cache_item->isHit()) {
            $result = '<?xml version="1.0" encoding="UTF-8"?>';
            $result .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';


            // Главная страница
            $url = Config::get('root_url');

            $result .= "\t<url>";
            $result .= "\t\t<loc>$url</loc>";
            $result .= "\t\t<lastmod>" . date('Y-m-d') . "</lastmod>";
            $result .= "\t</url>";


            // Страницы
            /*foreach(ContentPage::getList() as $p) {
            if($p->visible && $p->menu_id == 1) {
                $url = Config::get('root_url').'/'.$this->esc($p->url);
                $result .= "\t<url>"."\n";
                $result .= "\t\t<loc>$url</loc>"."\n";
                $result .= "\t</url>"."\n";
            }
            }*/


            // Блог
            foreach (ContentPost::getPosts(['visible' => 1]) as $p) {
                $url = Config::get('root_url') . '/blog/' . $this->esc($p->url);
                $result .= "\t<url>";
                $result .= "\t\t<loc>$url</loc>";
                $result .= "\t</url>";
            }


            // Категории
            foreach (ProductCategory::getCategories() as $c) {
                if ($c->visible) {
                    $url = Config::get('root_url') . '/' . $this->esc($c->url);
                    $result .= "\t<url>";
                    $result .= "\t\t<loc>$url</loc>";
                    $result .= "\t</url>";
                }
            }


            // Бренды
            /*foreach(ProductBrand::getBrands() as $b) {
            $url = Config::get('root_url').'/brands/'.$this->esc($b->url);
            $result .= "\t<url>"."\n";
            $result .= "\t\t<loc>$url</loc>"."\n";
            $result .= "\t</url>"."\n";
            }*/


            // Товары
            foreach (Product::getProducts(["visible" => 1]) as $product) {
                $product_url = Config::get('root_url') . '/tovar-' . $this->esc($product->url);
                $result .= "\t<url>";
                $result .= "\t\t<loc>$product_url</loc>";
                $result .= "\t</url>";
            }

            $result .=  '</urlset>';

            Helper::cache(self::class)->save($cache_item->set($result));
        }

        $result = $cache_item->get();

        $response = new Response($result);
        $response->headers->set('Content-type', 'text/xml');
        return $response;
    }


    /**
     * Esc
     * @param string $s
     */
    public function esc(string $s)
    {
        return (htmlspecialchars($s, ENT_QUOTES, 'UTF-8'));
    }
}
