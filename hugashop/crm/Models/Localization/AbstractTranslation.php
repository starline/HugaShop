<?php

/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.9
 *
 */

namespace HugaShop\Models\Localization;

use HugaShop\Models\BaseModel;

class AbstractTranslation extends BaseModel
{

    protected static $base_table_fields = [
        'id' =>                 ['type' => 'int',      'extra' => 'AUTO_INCREMENT'],
        'entity_id' =>          ['type' => 'int'],
        'language_code' =>      ['type' => 'varchar']
        // ... Other from Model
    ];


    protected static $table_indexes = [
        'unique_translation' => ['column' => ['entity_id', 'language_code'], 'type' => 'index']
    ];


    /**
     * Fill out table fields
     */
    public static function setTableTranslation(string $base_model)
    {
        $model = $base_model::getModel();

        $translatable_fields = $base_model::getTranslatableFields();
        $base_fields = $base_model::getFields();

        // reboot table field
        static::$table_fields = self::$base_table_fields;

        // Добавляем поля для перевода
        foreach ($translatable_fields as $field) {
            static::$table_fields[$field] = $base_fields[$field];
        }

        $table_name = $model->getTable() . '_translation';

        $translate_nodel = new static;
        $translate_nodel->setTable($table_name);

        return $translate_nodel;
    }
}
