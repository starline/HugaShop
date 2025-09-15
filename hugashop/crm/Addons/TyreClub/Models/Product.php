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
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Product extends BaseAddonModel
{
    protected $table = 'addon_tyre_club_products';
    public $timestamps = true;

    protected static $table_fields = [
        'id'                => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'external_id'       => ['type' => 'int',     'req'   => true],
        'model_id'          => ['type' => 'int'],
        'brand_id'          => ['type' => 'int'],
        'full_name'         => ['type' => 'varchar'],
        'reinforce_id'      => ['type' => 'int'],
        'ply_rating'        => ['type' => 'int'],
        'studded'           => ['type' => 'tinyint', 'def'   => 0],
        'seal'              => ['type' => 'tinyint', 'def'   => 0],
        'silent'            => ['type' => 'tinyint', 'def'   => 0],
        'width'             => ['type' => 'int'],
        'height'            => ['type' => 'int'],
        'diameter'          => ['type' => 'int'],
        'load_index'        => ['type' => 'int'],
        'speed_index'       => ['type' => 'varchar', 'length' => 10],
        'vehicle_type_id'   => ['type' => 'int'],
        'photo_url'         => ['type' => 'varchar', 'length' => 512]
    ];

    protected static $table_indexes = [
        'external_id_unique' => ['column' => ['external_id'], 'type' => 'unique'],
        'brand_index'        => ['column' => ['brand_id'],    'type' => 'index'],
        'model_index'        => ['column' => ['model_id'],    'type' => 'index'],
    ];

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class, 'product_id');
    }
}
