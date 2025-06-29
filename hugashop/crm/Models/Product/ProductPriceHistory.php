<?php

namespace HugaShop\Models\Product;

use HugaShop\Models\BaseModel;

class ProductPriceHistory extends BaseModel
{
    protected $table = 'product_price_history';

    protected static $table_fields = [
        'id' =>             ['type' => 'int',      'extra' => 'AUTO_INCREMENT'],
        'product_id' =>     ['type' => 'int',      'req' => true],
        'price' =>          ['type' => 'decimal',  'lenght' => 14.2, 'def' => 0.00],
        'cost_price' =>     ['type' => 'decimal',  'lenght' => 14.2, 'def' => 0.00],
        'created_at' =>     ['type' => 'datetime', 'def' => 'CURRENT_TIMESTAMP'],
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public static function addRecord(int $product_id, float $price, float $cost_price)
    {
        $record = self::createOne([
            'product_id' => $product_id,
            'price' => $price,
            'cost_price' => $cost_price,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return $record->id ?? 0;
    }
}
