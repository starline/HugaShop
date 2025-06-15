<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.5
 *
 * Product Content
 *
 */

namespace HugaShop\Api\Product;

use HugaShop\Api\Image;
use HugaShop\Api\Helper;
use HugaShop\Api\BaseModel;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use HugaShop\Api\Content\ContentComment;
use HugaShop\Api\Warehouse\WarehousePurchase;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends BaseModel
{

    public static $table_fields = [
        'id' =>                 ['type' => 'int',           'extra' => 'AUTO_INCREMENT'],
        'url' =>                ['type' => 'varchar'],
        'name' =>               ['type' => 'varchar',       'translate' => true,    'req' => true],
        'meta_title' =>         ['type' => 'varchar',       'translate' => true],
        'meta_description' =>   ['type' => 'varchar',       'translate' => true],
        'annotation' =>         ['type' => 'varchar',       'translate' => true],
        'body' =>               ['type' => 'text',          'translate' => true],
        'brand_id' =>           ['type' => 'int'],
        'category_id' =>        ['type' => 'int'],
        'created' =>            ['type' => 'datetime',      'def' => 'CURRENT_TIMESTAMP'],
        'disable' =>            ['type' => 'tinyint',       'def' => 0],
        'featured' =>           ['type' => 'tinyint',       'def' => 0],
        'sale' =>               ['type' => 'tinyint',       'def' => 0],
        'visible' =>            ['type' => 'tinyint',       'def' => 0],
        'variant_name' =>       ['type' => 'varchar'],
        'sku' =>                ['type' => 'varchar',       'req' => true],
        'price' =>              ['type' => 'decimal',       'lenght' => 14.2,   'def' =>    0.00],
        'cost_price' =>         ['type' => 'decimal',       'lenght' => 14.2,   'def' =>    0.00],
        'old_price' =>          ['type' => 'decimal',       'lenght' => 14.2,   'def' =>    0.00],
        'stock' =>              ['type' => 'int',           'lenght' => 9],
        'weight' =>             ['type' => 'decimal',       'lenght' => 8.3,    'def' =>    0.000],
        'awaiting_date' =>      ['type' => 'date'],
        'awaiting' =>           ['type' => 'tinyint',       'def' => 0],
        'custom' =>             ['type' => 'tinyint',       'def' => 0],
        'position' =>           ['type' => 'int',           'def' => 0]
    ];

    public static $table__keys = [
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
            ->with('warehouse_move')
            ->whereHas('warehouse_move', function ($query) {
                $query->where('status', 1)
                    ->where('awaiting_date', '>', Carbon::now());
            })
            ->orderBy('warehouse_move.awaiting_date');
    }

    /**
     * Amount of future warehouse movements
     */
    public function getMovementsAmountAttribute()
    {
        return $this->movements->sum('amount');
    }


    public function image()
    {
        return $this->hasOne(Image::class, 'entity_id')
            ->where('entity_name', 'product')
            ->orderBy('position');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function related(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            ProductRelated::class,     // имя таблицы связей
            'product_id',            // внешний ключ для текущего товара
            'related_id'             // внешний ключ для связанного товара
        );
    }


    /**
     * Get Products
     * Товары сортируются в порядку убывания чтобы новые товары были в начале списка
     *
     * @param array $filter
     *  Возможные значения фильтра:
     *  id              - id товара или их массив
     *  category_id     - id категории или их массив
     *  brand_id        - id бренда или их массив
     *  page            - текущая страница, integer
     *  limit           - количество товаров на странице, integer
     *  sort            - порядок товаров, возможные значения: position(по умолчанию), name, price
     *  keyword         - ключевое слово для поиска
     *  features        - фильтр по свойствам товара, массив (id свойства => значение свойства)
     *
     * @param array $join = array('brand', 'category', 'images', 'variant')
     * @param boolean $count
     */
    public static function getProducts(array $filter = [], array $join = [], bool $count = false)
    {

        $query = self::query();

        // Filters
        if (isset($filter['id'])) {
            $query->whereIn('id', (array)$filter['id']);
        }

        if (isset($filter['category_id'])) {
            $query->whereIn('category_id', (array)$filter['category_id']);
        }

        if (isset($filter['brand_id'])) {
            $query->whereIn('brand_id', (array)$filter['brand_id']);
        }

        if (isset($filter['featured'])) {
            $query->where('featured', $filter['featured']);
        }

        if (isset($filter['sale'])) {
            $query->where('sale', $filter['sale']);
        }

        if (isset($filter['visible'])) {
            $query->where('visible', $filter['visible']);
        }

        if (Arr::has($filter, 'disable')) {
            $query->where('disable', $filter['disable']);
        }

        if (isset($filter['discounted'])) {
            $query->whereColumn('old_price', '>', 'price');
        }

        if (isset($filter['in_stock'])) {
            $query->where(function ($q) {
                $q->where('stock', '>', 0)->orWhereNull('stock');
            });
        }

        // Keyword search
        if (!empty($filter['keyword'])) {
            $keywords = explode(' ', trim($filter['keyword']));
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    $q->orWhere('name', 'like', "%$kw%")
                        ->orWhere('sku', 'like', "%$kw%")
                        ->orWhere('variant_name', 'like', "%$kw%");
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
        $with = [];
        if (in_array('brand', $join)) {
            $with[] = 'brand';
        }
        if (in_array('category', $join)) {
            $with[] = 'category';
        }
        if (in_array('images', $join)) {
            $with[] = 'images';
        }
        if (in_array('image', $join)) {
            $with[] = 'image';
        }
        if (in_array('variant', $join)) {
            $with[] = 'variants';
        }
        if (in_array('movements', $join)) {
            $with[] = 'movements';
        }
        if (!empty($with)) {
            $query->with($with);
        }

        // Sorting
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
            default:
                $query->orderByDesc('position');
                break;
        }

        // Pagination
        if (!empty($filter['limit']) && $filter['limit'] !== 'all') {
            $limit = max(1, (int)$filter['limit']);
            $page = max(1, (int)($filter['page'] ?? 1));
            $query->limit($limit)->offset(($page - 1) * $limit);
        }

        return $query->get();
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
     * @param array $join
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
     * Обновляем товар
     * @param int|array $id
     * @param object|array $product
     */
    public static function updateProduct(int|array $id, $product)
    {
        $product = Helper::makeUniqSlug(self::class, $product); # If the URL exists, change it
        return self::updateOne($id, $product);
    }


    /**
     * Create new products
     * @param object|array $product
     */
    public static function addProduct(object|array $product)
    {
        $product = Helper::makeUniqSlug(self::class, $product); # If the URL exists, change it
        return self::create($product);
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

        // Удаляем варианты
        $variants = ProductVariant::getVariants(array('product_id' => $id));
        foreach ($variants as $v) {
            ProductVariant::deleteVariant($v->id);
        }

        // Удаляем основные изображения
        $images = Image::getImages($id, 'product');
        foreach ($images as $i) {
            Image::deleteImage($i->id);
        }

        // Удаляем изображения контента
        $images = Image::getImages($id, 'product_content');
        foreach ($images as $i) {
            Image::deleteImage($i->id);
        }

        // Удаляем свойства
        $options = ProductOption::getOptions(array('product_id' => $id));
        foreach ($options as $o) {
            ProductOption::deleteOption($id, $o->feature_id);
        }

        // Удаляем связанные товары
        $related = ProductRelated::getRelatedProducts($id);
        foreach ($related as $rel) {
            ProductRelated::deleteRelatedProduct($id, $rel->related_id);
        }

        // Удаляем товар из связанных с другими
        ProductRelated::where('related_id')->delete();

        // Удаляем отзывы
        ContentComment::deleteEntityComments($id, Product::class);

        // Зачищаем из покупок
        //OrderPurchase::where('product_id', $id)->update(['product_id' => NULL]);

        // Зачищаем из поставок
        //WarehousePurchase::where('product_id', $id)->update(['product_id' => NULL]);

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
        $options = ProductOption::getOptions(['product_id' => $id]);
        foreach ($options as $o) {
            ProductOption::updateOption($new_id, $o->feature_id, $o->value);
        }

        // Дублируем связанные товары
        $related = ProductRelated::getRelatedProducts($id);
        foreach ($related as $rel) {
            ProductRelated::addRelatedProduct($new_id, $rel->related_id);
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
     * Метод получения связанных товаров с изображениями
     */
    public static function getRelatedProducts(int $product_id): Collection
    {
        return self::whereHas('related', function ($query) use ($product_id) {
            $query->where('product_id', $product_id);
        })
            ->with('image')
            ->get()
            ->keyBy('id');
    }


    /**
     * Live update product stock
     * @param int $variant_id
     * @param int $amount
     */
    public static function updateStock(int $product_id, int $amount)
    {
        return self::where('id', $product_id)->whereNotNull('stock')->increment('stock', $amount);
    }
}
