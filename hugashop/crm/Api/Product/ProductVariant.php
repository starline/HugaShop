<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.2
 *
 * Работа с вариантами товаров
 *
 */

namespace HugaShop\Api\Product;

use HugaShop\Api\Helper;
use HugaShop\Api\BaseModel;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Builder;
use HugaShop\Api\Product\Product;
use HugaShop\Api\Product\ProductProvider;

class ProductVariant extends BaseModel
{
    public static $table_fields = [
        'id' =>                ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'product_id' =>        ['type' => 'int'],
        'provider_id' =>       ['type' => 'int'],
        'name' =>              ['type' => 'varchar'],
        'sku' =>               ['type' => 'varchar',       'req' => true],
        'price' =>             ['type' => 'decimal',       'lenght' => 14.2, 'def' => 0.00],
        'cost_price' =>        ['type' => 'decimal',       'lenght' => 14.2, 'def' => 0.00],
        'old_price' =>         ['type' => 'decimal',       'lenght' => 14.2, 'def' => 0.00],
        'stock' =>             ['type' => 'int',           'lenght' => 9],
        'weight' =>            ['type' => 'decimal',       'lenght' => 8.3, 'def' => 0.000],
        'date' =>              ['type' => 'datetime',      'def' => 'CURRENT_TIMESTAMP'],
        'awaiting_date' =>     ['type' => 'date'],
        'awaiting' =>          ['type' => 'tinyint',       'def' => 0],
        'custom' =>            ['type' => 'tinyint',       'def' => 0],
        'position' =>          ['type' => 'int',           'def' => 0]
    ];

    public static $table_keys = [
        'no_restore_price' =>   ['no_restore_price'],
        'product_id' =>         ['product_id'],
        'sku' =>                ['sku'],
        'price' =>              ['price'],
        'stock' =>              ['stock'],
        'provider_id' =>        ['provider_id'],
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function provider()
    {
        return $this->belongsTo(ProductProvider::class, 'provider_id');
    }


    /**
     * Функция возвращает варианты товара
     * @param array $filter
     * @param array $join
     */
    public static function getVariants(array $filter = [], array|string $order = 'position', array $join = []): array
    {
        $query = self::query()->select('product_variant.*');

        if (in_array('provider', $join)) {
            $query->leftJoin('product_provider as pr', 'product_variant.provider_id', '=', 'pr.id')
                ->addSelect('pr.name as provider_name');
        }

        if (in_array('Product', $join) || !empty($filter['category_id']) || !empty($filter['visible'])) {
            $query->leftJoin('product as p', 'product_variant.product_id', '=', 'p.id')
                ->addSelect([
                    'product_variant.id as variant_id',
                    'product_variant.name as variant_name',
                    'product_variant.position as variant_position',
                    'p.id as product_id',
                    'p.name as product_name',
                    'p.url',
                    'p.annotation',
                    'p.body',
                    'p.category_id',
                    'p.brand_id',
                    'p.visible',
                    'p.disable',
                    'p.featured',
                    'p.sale',
                ]);
        }

        if (in_array('ProductCategory', $join)) {
            $query->leftJoin('product_category as pc', 'pc.id', '=', 'p.category_id')
                ->addSelect(['pc.name as category_name', 'pc.url as category_url']);
        }

        if (in_array('Image', $join)) {
            $query->leftJoin('content_image as i', function ($join) {
                $join->on('i.entity_id', '=', 'p.id')
                    ->where('i.entity_name', 'product')
                    ->whereRaw('(i.position = (SELECT MIN(position) FROM content_image WHERE entity_id=p.id AND entity_name="product"))');
            })->addSelect('i.filename as image');
        }

        if (in_array('ProductBrand', $join)) {
            $query->leftJoin('product_brand as brand', 'brand.id', '=', 'p.brand_id')
                ->addSelect('brand.name as brand_name');
        }

        if (!empty($filter['product_id'])) {
            $query->whereIn('product_variant.product_id', (array) $filter['product_id']);
        }

        if (!empty($filter['id'])) {
            $query->whereIn('product_variant.id', (array) $filter['id']);
        }

        if (!empty($filter['in_stock'])) {
            $query->where(function (Builder $q) {
                $q->where('product_variant.stock', '>', 0)
                    ->orWhereNull('product_variant.stock');
            });
        }

        if (!empty($filter['low_price'])) {
            $query->whereRaw('product_variant.price = (SELECT MIN(pv.price) FROM product_variant pv WHERE pv.product_id = product_variant.product_id)');
        }

        if (!empty($filter['category_id'])) {
            $query->whereIn('p.category_id', (array) $filter['category_id']);
        }

        if (!empty($filter['visible'])) {
            $query->where('p.visible', $filter['visible']);
        }

        if (is_string($order)) {
            $query->orderBy($order);
        } elseif (is_array($order)) {
            foreach ($order as $field => $direction) {
                if (is_int($field)) {
                    $query->orderBy($direction);
                } else {
                    $query->orderBy($field, $direction ?: 'asc');
                }
            }
        }

        return $query->get()->keyBy('id')->all();
    }


    /**
     * Выбираем информацию о Варианте товара
     * @param int|string $id - может быть как и цифрой(id) так и строкой(sku)
     */
    public static function getVariant(int|string $id, array $join = []): ?object
    {
        if (is_numeric($id)) {
            $filter['id'] = intval($id);
        } else {
            $filter['sku'] = $id;
        }
        return self::getOne($filter, $join);
    }


    /**
     * Update protuct variant data
     * @param int $id
     * @param object|array $variant
     */
    public static function updateVariant(int $id, object|array $variant)
    {
        foreach (["price", "old_price", "cost_price", "weight"] as $key) {
            if (isset($variant->$key)) {
                $variant->$key = Helper::clearPrice($variant->$key);
            }
        }

        if (isset($variant->stock) && ($variant->stock === '' || $variant->stock === '∞')) {
            $variant->stock = null;
        }

        return self::updateOne($id, $variant);
    }


    /**
     * Добавить вариант
     * @param $variant
     */
    public static function addVariant($variant)
    {
        foreach (["price", "old_price", "cost_price", "weight"] as $key) {
            if (isset($variant->$key)) {
                $variant->$key = Helper::clearPrice($variant->$key);
            }
        }

        if (isset($variant->stock) && ($variant->stock === '' || $variant->stock === '∞')) {
            $variant->stock = null;
        }

        return self::create($variant);
    }


    /**
     * Удаление варианта
     * @param int $id
     */
    public static function deleteVariant(int $id)
    {
        if (self::deleteOne($id)) {
            Capsule::table('order_purchase')->where('variant_id', $id)->update(['variant_id' => null]);
            Capsule::table('wh_purchase')->where('variant_id', $id)->update(['variant_id' => null]);
            return true;
        }

        return false;
    }


    /**
     * Live update variant stock
     * @param int $variant_id
     * @param int $amount
     */
    public static function updateStock(int $variant_id, int $amount)
    {
        return self::where('id', $variant_id)->whereNotNull('stock')->increment('stock', $amount);
    }


    /**
     * Обнуление наличия
     * @param $variant
     * @param array $filter
     */
    public static function restoreStock($variant, array $filter = [])
    {
        $query = self::query();

        if (!empty($filter['provider_ids'])) {
            $query->whereIn('provider_id', (array) $filter['provider_ids']);
        }

        return $query->update((array) $variant);
    }
}
