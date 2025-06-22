<?php


/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.2
 *
 */

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

    public static $main_language_code;
    public static $current_language_code;

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
    public static function languageCatch()
    {
        $languages = Language::All();
        self::$main_language_code = $languages->firstWhere('main', 1)->code;
        self::$current_language_code = Request::get('lang', 'string');
        self::$current_language_code = $languages->firstWhere('code', self::$current_language_code)?->code ?: self::$main_language_code;

        Design::assign('languages', $languages);
        Design::assign('current_language', self::$current_language_code);
    }


    /**
     * Check if language is defined
     */
    public static function checkOrGetCode()
    {
        return (!empty(self::$current_language_code) and !empty(self::$main_language_code) and self::$current_language_code !== self::$main_language_code) ?? false;
    }
}
