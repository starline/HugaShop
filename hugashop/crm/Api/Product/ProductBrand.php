<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.6
 *
 */

namespace HugaShop\Api\Product;

use HugaShop\Api\Config;
use HugaShop\Api\Helper;
use HugaShop\Api\BaseModel;
use Illuminate\Database\Eloquent\Builder;

class ProductBrand extends BaseModel
{
    public static $table_fields = [
        'id' =>                 ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'name' =>               ['type' => 'varchar',       'translate' => true, 'req' => true],
        'url' =>                ['type' => 'varchar'],
        'meta_title' =>         ['type' => 'varchar',       'translate' => true],
        'meta_description' =>   ['type' => 'varchar',       'translate' => true],
        'description' =>        ['type' => 'text',          'translate' => true],
        'image' =>              ['type' => 'varchar'],
        'featured' =>           ['type' => 'tinyint',       'def' => 0]
    ];

    public static $table_keys = [
        'name' => ['name'],
        'url' => ['url'],
        'featured' => ['featured']
    ];


    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id');
    }

    /**
     * Функция возвращает массив брендов, удовлетворяющих фильтру
     * @param array $filter
     */
    public static function getBrands(array $filter = [])
    {
        $query = self::query();

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
     * Функция возвращает бренд по его id или url
     * (в зависимости от типа аргумента, int - id, string - url)
     * @param int|string $id id или url поста
     *
     */
    public static function getBrand(int|string $id)
    {
        if (is_numeric($id)) {
            $filter['id'] = intval($id);
        } else {
            $filter['url'] = $id;
        }

        return self::getOne($filter);
    }


    /**
     * Добавление бренда
     * @param $brand
     */
    public static function addBrand($brand)
    {
        $brand = Helper::makeUniqSlug(self::class, $brand);
        return self::create($brand);
    }


    /**
     * Обновление бренда(ов)
     * @param int $id
     * @param $brand
     */
    public static function updateBrand(int $id, $brand)
    {
        $brand = Helper::makeUniqSlug(self::class, $brand);
        return self::updateOne($id, $brand);
    }


    /**
     * Удаление бренда
     * @param int $id
     */
    public static function deleteBrand(int $id)
    {

        // Удаляем изображение
        self::deleteImage($id);

        if (ProductBrand::deleteOne($id)) {
            Product::where('brand_id', $id)->update(['brand_id' => null]);
        } else {
            return false;
        }
    }


    /**
     * Удаление изображения бренда
     * @param int $brand_id
     */
    public static function deleteImage(int $brand_id)
    {

        $brand = ProductBrand::find($brand_id);

        if (!$brand || empty($brand->image)) {
            return;
        }

        $filename = $brand->image;

        // Обновляем бренд, убирая ссылку на изображение
        $brand->image = null;
        $brand->save();

        // Проверяем, используется ли это изображение другими брендами
        $count = ProductBrand::where('image', $filename)->count();

        if ($count === 0) {
            @unlink(Config::get('images_brands_dir') . $filename);
        }
    }
}
