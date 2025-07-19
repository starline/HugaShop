<?php
/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\ProductSearchKeyword\EventListener;

use App\Event\ProductSearchEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use HugaShop\Extensions\BaseExtensionTrait;
use HugaShop\Extensions\ProductSearchKeyword\Models\ProductSearchKeyword;

class ProductSearchEventListener
{
    use BaseExtensionTrait;

    #[AsEventListener]
    public function onProductSearchEvent(ProductSearchEvent $event): void
    {
        if (empty($this->getSettings()->enabled)) {
            return;
        }

        ProductSearchKeyword::addKeyword($event->getQuery());
    }
}
