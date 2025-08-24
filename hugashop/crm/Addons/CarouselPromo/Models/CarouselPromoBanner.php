<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.1
 *
 */

namespace HugaShop\Addons\CarouselPromo\Models;

use HugaShop\Models\Image;
use HugaShop\Addons\CarouselPromo\CarouselPromo;
use HugaShop\Addons\BaseAddonModel;

final class CarouselPromoBanner extends BaseAddonModel
{
    protected static $table_fields = [
        'id'       => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'name'     => ['type' => 'varchar', 'required' => 'true'],
        'url'      => ['type' => 'varchar'],
        'position' => ['type' => 'int',     'def' => 0],
        'enabled'  => ['type' => 'tinyint', 'def' => 0],
    ];

    public function image()
    {
        return $this->hasOne(Image::class, 'entity_id')
            ->where('entity_name', CarouselPromo::class)
            ->where('visible', 1)
            ->orderBy('position');
    }

    public function images()
    {
        return $this->hasMany(Image::class, 'entity_id')
            ->where('entity_name', CarouselPromo::class)
            ->orderBy('position');
    }
}
