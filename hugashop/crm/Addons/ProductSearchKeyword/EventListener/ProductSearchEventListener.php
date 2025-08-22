<?php
/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 */

namespace HugaShop\Addons\ProductSearchKeyword\EventListener;

use App\Event\ProductSearchEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use HugaShop\Addons\BaseAddonTrait;
use HugaShop\Addons\ProductSearchKeyword\Models\ProductSearchKeyword;

class ProductSearchEventListener
{
    use BaseAddonTrait;

    #[AsEventListener]
    public function onProductSearchEvent(ProductSearchEvent $event): void
    {
        if (empty($this->getSettings()->enabled)) {
            return;
        }

        ProductSearchKeyword::addKeyword($event->getQuery());
    }
}
