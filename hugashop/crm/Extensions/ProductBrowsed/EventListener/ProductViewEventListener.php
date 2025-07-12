<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.4
 *
 */

namespace HugaShop\Extensions\ProductBrowsed\EventListener;

use HugaShop\Services\Request;
use App\Event\ProductViewEvent;
use HugaShop\Extensions\BaseExtensionTrait;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class ProductViewEventListener
{

    use BaseExtensionTrait;

    /**
     * setBrowsedProducts
     */
    #[AsEventListener]
    public function onProductViewEvent(ProductViewEvent $event): void
    {
        $product  = $event->getProduct();
        $settings = $this->getSettings();

        if (empty($settings->enabled)) {
            return;
        }

        // Добавление в историю просмотренных товаров
        $max_visited_products = $settings->limit ?? 30; # Максимальное число хранимых товаров в истории
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
    }
}
