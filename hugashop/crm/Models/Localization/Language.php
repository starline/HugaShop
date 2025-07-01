<?php


/**
 * HugaShop - Sell anything
 *
 * @author Andri Huga
 * @version 1.7
 *
 */

namespace HugaShop\Models\Localization;

use HugaShop\Models\BaseModel;

class Language extends BaseModel
{

    protected static $table_fields = [
        'id' =>             ['type' => 'int',      'extra' => 'AUTO_INCREMENT'],
        'code' =>           ['type' => 'varchar'],
        'name' =>           ['type' => 'varchar'],
        'main' =>           ['type' => 'tinyint',  'def' => 0]
    ];

    public static $languages;
    public static $main_language;
    public static $current_language;


    /**
     * Get All lenguages. Use cache 
     */
    public static function getLanguages()
    {
        if (empty(self::$languages)) {
            self::$languages = self::query()->orderBy('id')->get();
        }
        return self::$languages;
    }


    /**
     * Get Main Language. Use Cache
     */
    public static function getMain()
    {
        if (empty(self::$main_language)) {
            self::$main_language = self::getLanguages()->firstWhere('main', 1);
        }
        return self::$main_language;
    }


    /**
     * Get currentt language
     */
    public static function getCurrent(?string $code = null)
    {
        if (is_null($code)) {
            return self::getMain();
        }
        if (empty(self::$current_language)) {
            self::$current_language = self::getLanguages()->firstWhere('code', $code);
        }
        return self::$current_language;
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
    public static function createOne(array|object $values): object
    {

        $values = is_object($values) ? (array) $values : $values;

        // Reset main flag for other languages
        if (!empty($values->main)) {
            self::query()->where('main', 1)->update(['main' => 0]);
        }

        return parent::createOne($values);
    }


    /**
     * Update language. If it's marked as main, remove main flag from others
     */
    public static function updateOne(int|array $ids, array|object $values)
    {
        $values = is_object($values) ? (array) $values : $values;

        if (!empty($values->main)) {
            $ids_array = is_array($ids) ? (array) ($ids['id'] ?? $ids) : [$ids];
            self::query()
                ->where('main', 1)
                ->whereNotIn('id', $ids_array)
                ->update(['main' => 0]);
        }

        return parent::updateOne($ids, $values);
    }


    /**
     * Check if language is defined
     */
    public static function checkOrGetCode()
    {
        if (
            !empty(self::$current_language) &&
            !empty(self::$main_language) &&
            self::$current_language->code !== self::$main_language->code
        ) {
            return self::$current_language->code;
        }

        return false;
    }
}
