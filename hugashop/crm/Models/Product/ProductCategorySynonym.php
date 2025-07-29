<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 2.2
 *
 */

namespace HugaShop\Models\Product;

use HugaShop\Models\BaseModel;
use Illuminate\Support\Collection;

class ProductCategorySynonym extends BaseModel
{

    protected static $table_fields = [
        'id' =>                 ['type' => 'int'],
        'category_id' =>        ['type' => 'int'],
        'name' =>               ['type' => 'varchar',           'req' => true],
        'position' =>           ['type' => 'int',               'def' => 0]
    ];


    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    
    /**
     * Функция возвращает cинонимы, удовлетворяющих фильтру
     * @param array $filter
     */
    public static function getSynonyms(array $filter = []): Collection
    {
        $query = self::with('category');
        if (!empty($filter['category_id'])) {
            $query->where('category_id', (int) $filter['category_id']);
        }
        return $query->orderBy('position')->get();
    }


    /**
     * Функция возвращает синоним по его id или name
     * (в зависимости от типа аргумента, int - id, string - name)
     * @param $id id или url поста
     */
    public static function getSynonym(int|string $id)
    {
        $query = self::with('category');
        if (is_int($id)) {
            $query->where('id', $id);
        } else {
            $query->where('name', $id);
        }
        return $query->first();
    }


    /**
     * Обновление синонимов категории
     * @param $category_id
     * @param $synonyms
     */
    public static function updateCategorySynonyms(int $category_id, array $synonyms)
    {
        // Удаляем старые синонимы
        self::deleteCategorySynonyms($category_id);

        // Готовим массив для массовой вставки
        $insertData = [];
        foreach ($synonyms as $index => $synonym) {
            if (!empty($synonym)) {
                $insertData[] = [
                    'category_id' => $category_id,
                    'name' => $synonym,
                    'position' => $index,
                ];
            }
        }

        // Вставляем новые записи, если есть
        if (!empty($insertData)) {
            return self::insert($insertData);
        }

        return true;
    }


    /**
     * Delete Category synonymms
     */
    public static function deleteCategorySynonyms(int $category_id): int
    {
        return self::where('category_id', $category_id)->delete();
    }
}
