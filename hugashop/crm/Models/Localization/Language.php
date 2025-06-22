<?php

namespace HugaShop\Models\Localization;

use HugaShop\Models\Design;
use HugaShop\Models\Request;
use HugaShop\Models\BaseModel;

class Language extends BaseModel
{

    protected static $table_fields = [
        'id' =>             ['type' => 'int',      'extra' => 'AUTO_INCREMENT'],
        'code' =>           ['type' => 'varchar'],
        'name' =>           ['type' => 'varchar'],
        'main' =>           ['type' => 'tinyint',  'def' => 0]
    ];

    public static $main_language;
    public static $current_lang;

    public function main()
    {
        return $this->firstWhere('main', true);
    }

    public static function getLanguages()
    {
        return self::query()->orderBy('id')->get();
    }

    public static function deleteLenguage(int $language_id)
    {
        // TODO если язык стоик как основйно, отменить удаление
        return self::deleteOne($language_id);
    }

    /**
     * Init content language
     */
    public function languageCatch()
    {
        $languages = Language::getList();
        self::$main_language = $languages->firstWhere('main', 1)->code;
        self::$current_lang = Request::get('lang', 'string') ?: self::$main_language;
        Design::assign('languages', $languages);
        Design::assign('current_language', self::$current_lang);
    }
}
