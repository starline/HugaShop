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

    public static function deleteOne(array|int $ids)
    {
        $ids_array = is_array($ids) ? $ids : [$ids];

        foreach ($ids_array as $id) {
            Image::where('entity_name', CarouselPromo::class)
                ->where('entity_id', $id)
                ->get()
                ->each(fn($image) => Image::deleteImage($image->id));
        }

        return parent::deleteOne($ids);
    }
}
