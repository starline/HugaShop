<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.6
 *
 */

namespace HugaShop\Models\Localization;

use Illuminate\Support\Str;
use HugaShop\Models\BaseModel;

class AbstractTranslation extends BaseModel
{

    protected static $table_fields = [
        'id' =>                 ['type' => 'int',      'extra' => 'AUTO_INCREMENT'],
        'entity_id' =>          ['type' => 'int'],
        'language_code' =>      ['type' => 'varchar']
        // ... Other from Model
    ];


    public static $table_keys = [
        'unique_translation' => ['entity_id', 'language_code']
    ];


    /**
     * Fill out table fields
     */
    public static function setTableTranslation(string $base_model)
    {

        if (class_exists($base_model)) {
            $translatable_fields = $base_model::getTranslatableFields();
            $base_fields = $base_model::getFields();

            // Добавляем их в таблицу полей
            foreach ($translatable_fields as $field) {
                static::$table_fields[$field] = $base_fields[$field];
            }
        }

        $table_name = Str::snake(class_basename($base_model) . 'Translation');

        $query = new static;
        $query->setTable($table_name);

        return $query;
    }
}
