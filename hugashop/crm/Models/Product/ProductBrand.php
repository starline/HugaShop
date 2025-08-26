<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.4
 *
 */

namespace HugaShop\Models\Product;

use HugaShop\Models\Image;
use HugaShop\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

class ProductBrand extends BaseModel
{

    public $timestamps = true;
    protected static $table_fields = [
        'id' =>                 ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'url' =>                ['type' => 'varchar',       'slug' => true],
        'name' =>               ['type' => 'varchar',       'trans' => true, 'req' => true],
        'meta_title' =>         ['type' => 'varchar',       'trans' => true],
        'meta_description' =>   ['type' => 'varchar',       'trans' => true],
        'description' =>        ['type' => 'text',          'trans' => true],
        'featured' =>           ['type' => 'tinyint',       'def' => 0]
    ];

    protected static $table_indexes = [
        'name'      => ['column' => ['name'],                'type' => 'index'],
        'url'       => ['column' => ['url'],                 'type' => 'index'],
        'featured'  => ['column' => ['featured'],            'type' => 'index']
    ];

    public function image()
    {
        return $this->hasOne(Image::class, 'entity_id')
            ->where('entity_name', ProductBrand::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id');
    }


    /**
     * Функция возвращает массив брендов, удовлетворяющих фильтру
     * @param array $filter
     */
    public static function getBrands(array $filter = [], array $join = [])
    {
        $query = self::query();

        // With relations
        if (!empty($join)) {
            $query->with($join);
        }

        // Фильтр по featured
        if (isset($filter['featured'])) {
            $query->where('featured', (int) $filter['featured']);
        }

        // Фильтр по category_id
        if (!empty($filter['category_id'])) {
            $categoryIds = (array) $filter['category_id'];

            $query->whereHas('products', function (Builder $q) use ($categoryIds, $filter) {
                $q->whereIn('category_id', $categoryIds);

                if (isset($filter['visible'])) {
                    $q->where('visible', (int) $filter['visible']);
                }
            });
        }

        return $query->orderBy('name')->get()->keyBy('id');
    }


    /**
     * Удаление бренда
     */
    public static function deleteBrand(int|array $ids)
    {
        $ids_array = is_array($ids) ? $ids : [$ids];
        Image::deleteEntityImages($ids_array, ProductBrand::class);
        return parent::deleteOne($ids);
    }
}
