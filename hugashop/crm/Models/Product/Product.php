<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.9
 *
 * Product Content
 *
 */

namespace HugaShop\Models\Product;

use HugaShop\Models\Image;
use Illuminate\Support\Arr;
use HugaShop\Services\Helper;
use HugaShop\Models\BaseModel;
use HugaShop\Models\Order\OrderPurchase;
use HugaShop\Models\Localization\Language;
use HugaShop\Models\Product\ProductOption;
use HugaShop\Models\Content\ContentComment;
use HugaShop\Models\Product\ProductRelated;
use HugaShop\Models\Product\ProductVariant;
use HugaShop\Models\Warehouse\WarehousePurchase;
use HugaShop\Models\Localization\AbstractTranslation;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends BaseModel
{

    public $timestamps = true;

    protected static $table_fields = [
        'id' =>                 ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'url' =>                ['type' => 'varchar'],
        'name' =>               ['type' => 'varchar',       'trans' => true,    'req' => true],
        'meta_title' =>         ['type' => 'varchar',       'trans' => true],
        'meta_description' =>   ['type' => 'varchar',       'trans' => true],
        'annotation' =>         ['type' => 'varchar',       'trans' => true],
        'body' =>               ['type' => 'text',          'trans' => true],
        'variant_name' =>       ['type' => 'varchar',       'trans' => true],
        'brand_id' =>           ['type' => 'int'],
        'category_id' =>        ['type' => 'int'],
        'disable' =>            ['type' => 'tinyint',       'def' => 0],
        'featured' =>           ['type' => 'tinyint',       'def' => 0],
        'sale' =>               ['type' => 'tinyint',       'def' => 0],
        'visible' =>            ['type' => 'tinyint',       'def' => 0],
        'sku' =>                ['type' => 'varchar',       'req' => true],
        'price' =>              ['type' => 'decimal',       'length' => 14.2,   'def' =>    0.00],
        'cost_price' =>         ['type' => 'decimal',       'length' => 14.2,   'def' =>    0.00],
        'old_price' =>          ['type' => 'decimal',       'length' => 14.2,   'def' =>    0.00],
        'stock' =>              ['type' => 'int',           'length' => 9],
        'weight' =>             ['type' => 'decimal',       'length' => 8.3,    'def' =>    0.000],
        'awaiting_date' =>      ['type' => 'date'],
        'awaiting' =>           ['type' => 'tinyint',       'def' => 0],
        'custom' =>             ['type' => 'tinyint',       'def' => 0],
        'position' =>           ['type' => 'int',           'def' => 0],
    ];

    public static $table_keys = [
        'url' => ['url'],
        'brand_id' => ['brand_id'],
        'visible' => ['visible'],
        'featured' => ['featured'],
        'sale' => ['sale'],
        'disable' => ['disable'],
        'category_id' => ['category_id', 'visible']

    ];

    public function brand()
    {
        return $this->belongsTo(ProductBrand::class, 'brand_id');
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function image()
    {
        return $this->hasOne(Image::class, 'entity_id')
            ->where('entity_name', 'product')
            ->where('visible', 1)
            ->orderBy('position');
    }

    public function images()
    {
        return $this->hasMany(Image::class, 'entity_id')
            ->where('entity_name', 'product')
            ->orderBy('position');
    }


    /**
     * Future warehouse movements for the product
     */
    public function movements()
    {
        return $this->hasMany(WarehousePurchase::class, 'product_id', 'id')
            ->with(['warehouse_move' => function ($q) {
                $q->where('status', 1)
                    ->orderBy('awaiting_date');
            }])
            ->whereHas('warehouse_move', function ($q) {
                $q->where('status', 1);
            });
    }


    /**
     * Amount of future warehouse movements
     */
    public function getMovementsAmountAttribute()
    {
        return $this->movements->sum('amount');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function related(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            ProductRelated::class,   # имя таблицы связей
            'product_id',            # внешний ключ для текущего товара
            'related_id'             # внешний ключ для связанного товара
        )->withPivot('position',)
            ->orderBy('pivot_position');
    }

    /**
     * Features
     */
    public function options()
    {
        return $this->hasMany(ProductOption::class, 'product_id');
    }

    /**
     * Use: product->features_value
     */
    public function getFeaturesValueAttribute()
    {
        return $this->options->keyBy('feature_id');
    }

    /**
     * Product features with name and value
     */
    public function features()
    {
        return $this->belongsToMany(
            ProductFeature::class,
            ProductOption::class,
            'product_id',
            'feature_id'
        )->orderBy('position');
    }


    /**
     * Get Products
     * Товары сортируются в порядку убывания чтобы новые товары были в начале списка
     *
     * @param array $filter
     * Возможные значения фильтра:
     * id              - id товара или их массив
     * category_id     - id категории или их массив
     * brand_id        - id бренда или их массив
     * page            - текущая страница, integer
     * limit           - количество товаров на странице, integer
     * sort            - порядок товаров, возможные значения: position(по умолчанию), name, price
     * keyword         - ключевое слово для поиска
     * features        - фильтр по свойствам товара, массив (id свойства => значение свойства)
     *
     * @param array $join = array('brand', 'category', 'images', 'variant')
     * @param boolean $count
     */
    public static function getProducts(array $filter = [], array $join = [], bool $count = false)
    {

        $model = static::getModel();
        $query = $model->newQuery();

        $baseTable          = $model->getTable();
        $prefix             = $model->getConnection()->getTablePrefix();
        $base_table_prefix  = $prefix . $baseTable;

        // Join translation table for current locale if needed
        $transTable = null;
        if ($language_code = Language::checkOrGetCode()) {
            $transModel  = AbstractTranslation::setTableTranslation(static::class);
            $transTable  = $transModel->getTable();

            $select = ["{$base_table_prefix}.*"];
            foreach (static::getTranslatableFields() as $field) {
                $select[] = "COALESCE({$prefix}{$transTable}.{$field}, {$base_table_prefix}.{$field}) as {$field}";
            }

            $query->leftJoin($transTable, function ($join) use ($transTable, $baseTable, $language_code) {
                $join->on("{$transTable}.entity_id", '=', "{$baseTable}.id")
                    ->where("{$transTable}.language_code", '=', $language_code);
            })->selectRaw(implode(', ', $select));
        }


        // Filters
        if (Arr::has($filter, 'id')) {
            $query->whereIn($baseTable . '.id', (array)$filter['id']);
        }

        if (!empty($filter['category_id'])) {
            $query->whereIn('category_id', (array)$filter['category_id']);
        }

        if (Arr::has($filter, 'brand_id')) {
            $query->whereIn('brand_id', (array)$filter['brand_id']);
        }

        if (Arr::has($filter, 'featured')) {
            $query->where('featured', $filter['featured']);
        }

        if (Arr::has($filter, 'sale')) {
            $query->where('sale', $filter['sale']);
        }

        if (Arr::has($filter, 'visible')) {
            $query->where('visible', $filter['visible']);
        }

        if (Arr::has($filter, 'disable')) {
            $query->where('disable', $filter['disable']);
        }

        if (Arr::has($filter, 'discounted')) {
            $query->whereColumn('old_price', '>', 'price');
        }

        if (Arr::has($filter, 'in_stock')) {
            $query->where(function ($q) {
                $q->where('stock', '>', 0)->orWhereNull('stock');
            });
        }

        // Keyword search including translations
        if (!empty($filter['keyword'])) {
            $keywords = explode(' ', trim($filter['keyword']));
            $query->where(function ($q) use ($keywords, $language_code, $transTable) {
                foreach ($keywords as $kw) {
                    $q->orWhere('name', 'like', "%$kw%")
                        ->orWhere('sku', 'like', "%$kw%")
                        ->orWhere('variant_name', 'like', "%$kw%");
                    if ($language_code) {
                        $q->orWhere("{$transTable}.name", 'like', "%$kw%")
                            ->orWhere("{$transTable}.variant_name", 'like', "%$kw%");
                    }
                }
            });
        }

        // Feature filter
        if (!empty($filter['features']) && is_array($filter['features'])) {
            foreach ($filter['features'] as $feature_id => $value) {
                $query->whereHas('options', function ($q) use ($feature_id, $value) {
                    $q->where('feature_id', $feature_id)->whereIn('value', (array)$value);
                });
            }
        }

        // COUNT
        if ($count) {
            return $query->count();
        }

        // With relations
        if (!empty($join)) {
            $query->with($join);
        }


        // Sorting
        if (Arr::has($filter, 'sort_disable')) {
            $query->orderByRaw(
                "CASE WHEN {$base_table_prefix}.disable = 1 THEN 2 " .
                    "WHEN {$base_table_prefix}.stock IS NULL OR {$base_table_prefix}.stock > 0 THEN 0 ELSE 1 END"
            );
        }

        if (Arr::has($filter, 'sort_in_stock')) {
            $query->orderByRaw(
                "CASE WHEN {$base_table_prefix}.stock IS NULL OR " .
                    "{$base_table_prefix}.stock > 0 THEN 0 ELSE 1 END"
            );
        }

        $sort = $filter['sort'] ?? 'position';
        switch ($sort) {
            case 'name':
                $query->orderBy('name');
                break;
            case 'created':
                $query->orderByDesc('created');
                break;
            case 'price':
                $query->orderBy('price');
                break;
            case 'position':
                $query->orderByDesc('position');
                break;
        }


        // Pagination
        if (!empty($filter['limit']) && $filter['limit'] !== 'all') {
            $limit = max(1, (int)$filter['limit']);
            $page = max(1, (int)($filter['page'] ?? 1));
            $query->limit($limit)->offset(($page - 1) * $limit);
        }

        return $model->runWithInitTable(function () use ($query) {
            return $query->get()->keyBy('id');
        });
    }


    /**
     * Функция возвращает количество товаров
     * @param array $filter
     * @param array $join
     */
    public static function countProducts(array $filter = [], array $join = [])
    {
        return self::getProducts($filter, $join, count: true);
    }


    /**
     * Функция возвращает товар
     * @param int|string $id - id или name
     * @param array $join [ 'variant', 'related', 'relared.image', 'image', 'images' ]
     * @return object
     */
    public static function getProduct(int|string $id, array $join = [])
    {
        if (is_numeric($id)) {
            $filter['id'] = intval($id);
        } else {
            $filter['url'] = $id;
        }

        return self::getOne($filter, $join);
    }


    /**
     * Обновляем товар. Save price history
     * @param int|array $id
     * @param object|array $product
     */
    public static function updateProduct(int|array $id, array|object $product)
    {
        $product = Helper::makeUniqSlug(self::class, $product); # If the URL exists, change it

        // Save price and cost price history
        $ids = is_array($id) ? $id : [$id];
        $new_price = null;
        $new_cost_price = null;

        if (is_object($product)) {
            if (isset($product->price)) {
                $new_price = $product->price;
            }
            if (isset($product->cost_price)) {
                $new_cost_price = $product->cost_price;
            }
        } elseif (is_array($product)) {
            if (array_key_exists('price', $product)) {
                $new_price = $product['price'];
            }
            if (array_key_exists('cost_price', $product)) {
                $new_cost_price = $product['cost_price'];
            }
        }

        if ($new_price !== null || $new_cost_price !== null) {
            foreach ($ids as $pid) {
                $old_product = self::getOne($pid);
                if ($old_product) {
                    $final_price    = $new_price !== null ? $new_price : $old_product->price;
                    $final_cost     = $new_cost_price !== null ? $new_cost_price : $old_product->cost_price;
                    if (
                        floatval($old_product->price) != floatval($final_price) ||
                        floatval($old_product->cost_price) != floatval($final_cost)
                    ) {
                        ProductPriceHistory::addRecord(
                            intval($pid),
                            floatval($final_price),
                            floatval($final_cost)
                        );
                    }
                }
            }
        }

        return self::updateOne($id, $product);
    }


    /**
     * Create new products
     * @param object|array $product
     */
    public static function addProduct(object|array $product)
    {
        $product = Helper::makeUniqSlug(self::class, $product); # If the URL exists, change it
        $product = self::createOne($product);

        // Make position same as id
        $product = self::updateOne($product->id, ['position' => $product->id]);

        return $product;
    }


    /**
     * Удалить товар
     * @param int $id
     */
    public static function deleteProduct(int $id)
    {
        if (empty($id)) {
            return false;
        }

        // Удаляем из вариантов
        ProductVariant::deleteVariant($id);

        // Удаляем основные изображения
        $images = Image::getImages($id, 'product');
        foreach ($images as $i) {
            Image::deleteImage($i->id);
        }

        // Удаляем свойства
        ProductOption::deleteProductOption($id);

        // Удаляем связанные товары
        ProductRelated::deleteAllRelatedProducts($id);

        // Удаляем товар из связанных с другими
        ProductRelated::where('related_id')->delete();

        // Удаляем отзывы
        ContentComment::deleteEntityComments($id, Product::class);

        // Зачищаем из покупок
        OrderPurchase::where('product_id', $id)->update(['product_id' => NULL]);

        // Зачищаем из поставок
        WarehousePurchase::where('product_id', $id)->update(['product_id' => NULL]);

        // Удаляем товар
        return self::deleteOne($id);
    }


    /**
     * Создаем дубликат товара
     * @param int $id
     */
    public static function duplicateProduct(int $id)
    {
        $product = self::findOrFail($id)->replicate();
        $product = Helper::makeUniqSlug(self::class, $product); # If the URL exists, change it

        unset($product->created);

        $product->visible = 0;
        $product->featured = 0;
        $product->meta_title = '';
        $product->sale = 0;
        $product->name .= " - копия";

        // Сдвигаем товары вперед и вставляем копию на соседнюю позицию
        self::where('position', '>', $product->position)->increment('position');

        // Вставляем дубликат на следующую позицию
        $original_position = $product->position;
        $product->position = $original_position + 1;
        $product->save();

        $new_id = $product->id;

        // Дублируем изображения
        $images = Image::getImages($id, 'product');
        foreach ($images as $image) {
            Image::addImage($new_id, 'product', $image->filename);
        }

        // Дублируем свойства
        $options = ProductOption::getProductOptions($id);
        foreach ($options as $o) {
            ProductOption::createOne([
                'product_id' => $new_id,
                'feature_id' => $o->feature_id,
                'option_id' => $o->option_id
            ]);
        }

        // Дублируем связанные товары
        $related = ProductRelated::getRelatedProducts($id);
        foreach ($related as $rel) {
            ProductRelated::addRelatedProduct($new_id, $rel->related_id);
        }

        // If product had variants, add duplicate to variants list
        $has_variants = ProductVariant::query()
            ->where('product_id', $id)
            ->orWhere('parent_id', $id)
            ->exists();

        if ($has_variants) {
            $variants = ProductVariant::getVariants($id)->pluck('product_id')->toArray();
            $variants[] = $new_id;
            ProductVariant::updateVariants($id, $variants);
        }

        return $new_id;
    }


    /**
     * Get last product
     */
    public static function getLastProductPosition()
    {
        return self::orderBy('position', 'desc')->skip(1)->value('position');
    }


    /**
     * Live update product stock
     * Кол-во нужно добавлять/вычетать в SQL запросе. Чтобы не произошла коллизия при одновременных запросах
     */
    public static function changeAmount(int $product_id, int $amount)
    {
        return self::where('id', $product_id)->whereNotNull('stock')->increment('stock', $amount);
    }
}
