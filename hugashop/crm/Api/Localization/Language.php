<?php

namespace HugaShop\Api\Localization;

use HugaShop\Api\BaseModel;

class Language extends BaseModel
{
    protected $table = 'languages';

    public static $table_fields = [
        'id' =>             ['type' => 'int',      'extra' => 'AUTO_INCREMENT'],
        'code' =>           ['type' => 'varchar'],
        'name' =>           ['type' => 'varchar'],
        'is_default' =>     ['type' => 'tinyint',  'def' => 0],
    ];

    public static function getLanguages()
    {
        return self::query()->orderBy('id')->get();
    }

    public static function deleteLenguage(int $language_id)
    {
        // TODO если язык стоик как основйно, отменить удаление
        return self::deleteOne($language_id);
    }
}
