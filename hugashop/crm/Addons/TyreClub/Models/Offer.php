<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.0
 *
 */

namespace HugaShop\Addons\TyreClub\Models;

use HugaShop\Addons\BaseAddonModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Offer extends BaseAddonModel
{
    protected $table = 'addon_tyre_club_offers';
    public $timestamps = true;

    protected static $table_fields = [
        'id'                    => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'product_id'            => ['type' => 'int',     'req'   => true],
        'product_external_id'   => ['type' => 'int'],
        'source_price_wholesale'=> ['type' => 'decimal', 'length' => 14.2, 'def' => 0.00],
        'source_price_retail'   => ['type' => 'decimal', 'length' => 14.2, 'def' => 0.00],
        'user_wholesale_price'  => ['type' => 'decimal', 'length' => 14.2, 'def' => 0.00],
        'user_price_retail'     => ['type' => 'decimal', 'length' => 14.2, 'def' => 0.00],
        'provider_id'           => ['type' => 'int'],
        'in_stock'              => ['type' => 'int'],
        'country'               => ['type' => 'int'],
        'year'                  => ['type' => 'int'],
        'date'                  => ['type' => 'int']
    ];

    protected static $table_indexes = [
        'product_provider_date' => ['column' => ['product_id', 'provider_id', 'date'], 'type' => 'index']
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
