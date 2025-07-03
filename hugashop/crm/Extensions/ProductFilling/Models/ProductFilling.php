<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 * 
 * Products filling percent per language
 * 
 */

namespace HugaShop\Extensions\ProductFilling\Models;

use HugaShop\Extensions\BaseExtensionModel;

final class ProductFilling extends BaseExtensionModel
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
