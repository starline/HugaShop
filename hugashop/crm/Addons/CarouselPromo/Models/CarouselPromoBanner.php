<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 *
 */

namespace HugaShop\Addons\CarouselPromo\Models;

use HugaShop\Models\Image;
use HugaShop\Addons\CarouselPromo\CarouselPromo;
use HugaShop\Addons\BaseAddonModel;

final class CarouselPromoBanner extends BaseAddonModel
{

    public $timestamps = true;
    protected static $table_fields = [
        'id'       => ['type' => 'int',     'extra' => 'AUTO_INCREMENT'],
        'name'     => ['type' => 'varchar', 'required' => 'true'],
        'link'     => ['type' => 'varchar'],
        'position' => ['type' => 'int',     'def' => 0],
        'enabled'  => ['type' => 'tinyint', 'def' => 0],
    ];

    public function image()
    {
        return $this->hasOne(Image::class, 'entity_id')
            ->where('entity_name', CarouselPromo::class);
    }


    /**
     * Delete banners and their images
     */
    public static function deleteOne(array|int $ids)
    {
        $ids_array = is_array($ids) ? $ids : [$ids];
        Image::deleteEntityImages($ids_array, CarouselPromo::class);
        return parent::deleteOne($ids);
    }
}
