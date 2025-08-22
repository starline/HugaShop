<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
 * 
 * Products filling percent per language
 * 
 */

namespace HugaShop\Addons\ProductFilling\Models;

use HugaShop\Addons\BaseAddonModel;

final class ProductFilling extends BaseAddonModel
{
    public $timestamps = true;

    protected static $table_fields = [
        'id' =>             ['type' => 'int',      'extra' => 'AUTO_INCREMENT'],
        'product_id' =>     ['type' => 'int'],
        'language_code' =>  ['type' => 'varchar'],
        'percent' =>        ['type' => 'int',      'def' => 0],
        'updated_at' =>     ['type' => 'datetime'],
        'created_at' =>     ['type' => 'datetime']
    ];


    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
