<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 3.8
 *
 */

namespace HugaShop\Models\Finance;

use HugaShop\Models\BaseModel;

class FinanceCategory extends BaseModel
{
    protected static $table_fields = [
        'id' =>                     ['type' => 'int',       'extra' => 'AUTO_INCREMENT'],
        'name' =>                   ['type' => 'varchar',   'req' => true],
        'type' =>                   ['type' => 'int'],      # Don't set tinyint, if empty set 0
        'comment' =>                ['type' => 'varchar'],
        'position' =>               ['type' => 'int',       'def' => 0, 'access' => false]
    ];


    /**
     * Выбираем категории платежей
     * @param int|string $type - 1|plus|income  0|minus|expense
     */
    public static function getCategories(int|string|null $type = null)
    {
        $filter = [];

        if (!is_null($type)) {
            if (in_array($type, ['plus', 'income', 1])) {
                $filter['type'] = 1;
            } elseif (in_array($type, ['minus', 'expense', 0])) {
                $filter['type'] = 0;
            }
        }

        return self::getList($filter, order: 'position');
    }


    /**
     * Удаляем финансовую категорию
     * @param int|array $id - id или array(id, id, ...)
     */
    public static function deleteCategory(int|array $ids)
    {
        self::deleteOne($ids);

        // Очищаем взаимосвязанные таблицы
        //FinancePayment->update('finance_category_id=NULL')->where('finance_category_id in(?@)', $id_arr)->get();
        //return ContentPage::delete()->whereId($id_arr)->get();
    }
}
