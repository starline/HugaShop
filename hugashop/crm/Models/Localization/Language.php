<?php


/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.3
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
        $language = self::query()->where('id', $language_id)->first();
        if (empty($language)) {
            return false;
        }

        // If language is set as main, do not delete
        if (!empty($language->main)) {
            return false;
        }

        return self::deleteOne($language_id);
    }

    /**
     * Create language. If it's marked as main, remove main flag from others
     */
    public static function createOne(array|object $values)
    {
        $vals = is_object($values) ? (array) $values : $values;
        if (!empty($vals['main'])) {

            // Reset main flag for other languages
            self::query()->where('main', 1)->update(['main' => 0]);
        }

        return parent::create($values);
    }

    /**
     * Update language. If it's marked as main, remove main flag from others
     */
    public static function updateOne(int|array $ids, array|object $values)
    {
        $vals = is_object($values) ? (array) $values : $values;
        if (!empty($vals['main'])) {
            $ids_array = is_array($ids) ? (array) ($ids['id'] ?? $ids) : [$ids];
            self::query()
                ->where('main', 1)
                ->whereNotIn('id', $ids_array)
                ->update(['main' => 0]);
        }

        return parent::updateOne($ids, $values);
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
        if (
            !empty(self::$current_language_code) &&
            !empty(self::$main_language_code) &&
            self::$current_language_code !== self::$main_language_code
        ) {
            return self::$current_language_code;
        }

        return false;
    }
}
