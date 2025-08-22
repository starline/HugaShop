<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 */

namespace HugaShop\Addons\ProductPriceRequest\Models;

use HugaShop\Addons\BaseAddonModel;
use HugaShop\Models\Product\Product;

final class PriceRequest extends BaseAddonModel
{

    public $timestamps = true;
    protected static $table_fields = [
        'id'         => ['type' => 'int',       'extra' => 'AUTO_INCREMENT'],
        'product_id' => ['type' => 'int',       'req' => true],
        'name'       => ['type' => 'varchar',   'req' => true],
        'phone'      => ['type' => 'varchar'],
        'email'      => ['type' => 'varchar',   'req' => true],
        'comment'    => ['type' => 'varchar'],
        'user_agent' => ['type' => 'varchar',   'access' => false],
        'ip'         => ['type' => 'varchar',   'access' => false,  'length' => 20]
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
