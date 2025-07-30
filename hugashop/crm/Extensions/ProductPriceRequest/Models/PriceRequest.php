<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 */

namespace HugaShop\Extensions\ProductPriceRequest\Models;

use HugaShop\Extensions\BaseExtensionModel;
use HugaShop\Models\Product\Product;

final class PriceRequest extends BaseExtensionModel
{
    public $timestamps = true;

    protected static $table_fields = [
        'id'         => ['type' => 'int',       'extra' => 'AUTO_INCREMENT'],
        'product_id' => ['type' => 'int',       'req' => true],
        'name'       => ['type' => 'varchar',   'req' => true],
        'phone'      => ['type' => 'varchar'],
        'email'      => ['type' => 'varchar'],
        'link'       => ['type' => 'varchar'],
        'ip'         => ['type' => 'varchar',   'length' => 20]
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
